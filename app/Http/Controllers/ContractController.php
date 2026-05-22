<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Contract;
use App\Services\ContractService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService,
        protected OtpService $otpService
    ) {
    }

    /**
     * Show a contract detail page.
     */
    public function show(Contract $contract)
    {
        $this->authorizeContractAccess($contract);
        $contract->load(['booking.property', 'tenant', 'owner']);

        return view('contracts.show', compact('contract'));
    }

    /**
     * Send OTP and show signing form.
     */
    public function requestSign(Contract $contract)
    {
        $this->authorizeContractAccess($contract);
        $user = Auth::user();

        $this->otpService->generate($user->phone, 'contract_sign', $contract);

        return back()->with('success', 'Un code de signature a été envoyé au ' . $user->phone);
    }

    /**
     * Sign the contract with OTP code.
     */
    public function sign(Request $request, Contract $contract)
    {
        $this->authorizeContractAccess($contract);

        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ], [
            'otp_code.required' => 'Le code de signature est obligatoire.',
            'otp_code.size'     => 'Le code doit contenir 6 chiffres.',
        ]);

        $user = Auth::user();
        $signed = false;

        if ($user->id === $contract->tenant_id) {
            $signed = $this->contractService->signByTenant($contract, $request->otp_code);
        } elseif ($user->id === $contract->owner_id) {
            $signed = $this->contractService->signByOwner($contract, $request->otp_code);
        }

        if (!$signed) {
            return back()->withErrors(['otp_code' => 'Code incorrect ou expiré. Veuillez réessayer.']);
        }

        $contract->refresh();

        if ($contract->isSigned()) {
            return redirect()->route('contracts.show', $contract)
                ->with('success', 'Contrat signé par les deux parties ! Le contrat est maintenant valide.');
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Votre signature a bien été enregistrée. En attente de la signature de l\'autre partie.');
    }

    /**
     * Download the contract PDF.
     */
    public function download(Contract $contract)
    {
        $this->authorizeContractAccess($contract);

        if (!$contract->pdf_path) {
            // Regenerate the PDF
            $this->contractService->generatePdf($contract);
            $contract->refresh();
        }

        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            return back()->with('error', 'Le PDF du contrat n\'est pas disponible. Veuillez réessayer.');
        }

        return Storage::disk('public')->download(
            $contract->pdf_path,
            'Contrat-' . $contract->reference . '.pdf'
        );
    }

    /**
     * Show the inspection form (entry or exit).
     */
    public function inspectionForm(Contract $contract, string $type)
    {
        $this->authorizeContractAccess($contract);

        if (!in_array($type, ['entry', 'exit'])) {
            abort(404);
        }

        return view('contracts.inspection', compact('contract', 'type'));
    }

    /**
     * Save inspection report.
     */
    public function saveInspection(Request $request, Contract $contract, string $type)
    {
        $this->authorizeContractAccess($contract);

        if (!in_array($type, ['entry', 'exit'])) {
            abort(404);
        }

        $request->validate([
            'inspection_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'inspection_file.required' => 'Veuillez fournir le document d\'état des lieux.',
            'inspection_file.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $path = $request->file('inspection_file')
            ->store('contracts/inspections', 'public');

        if ($type === 'entry') {
            $contract->update([
                'entry_inspection_path'     => $path,
                'entry_inspection_signed_at' => now(),
            ]);
        } else {
            $contract->update([
                'exit_inspection_path'     => $path,
                'exit_inspection_signed_at' => now(),
            ]);
        }

        $label = $type === 'entry' ? 'entrée' : 'sortie';
        return back()->with('success', "État des lieux de {$label} enregistré.");
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function authorizeContractAccess(Contract $contract): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->id !== $contract->tenant_id && $user->id !== $contract->owner_id) {
            abort(403, 'Accès non autorisé à ce contrat.');
        }
    }
}
