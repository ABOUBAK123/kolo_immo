<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Contract;
use App\Models\OtpCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractService
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Generate a rental contract for a booking.
     */
    public function generateContract(Booking $booking): Contract
    {
        // Check if a contract already exists
        if ($booking->contract) {
            return $booking->contract;
        }

        $reference = 'CTR-' . strtoupper(Str::random(10));

        $contract = Contract::create([
            'reference'  => $reference,
            'booking_id' => $booking->id,
            'tenant_id'  => $booking->tenant_id,
            'owner_id'   => $booking->owner_id,
            'status'     => 'pending_signature',
        ]);

        // Generate PDF
        $pdfPath = $this->generatePdf($contract);
        $contract->update(['pdf_path' => $pdfPath]);

        return $contract->fresh();
    }

    /**
     * Generate the PDF for a contract and return the storage path.
     */
    public function generatePdf(Contract $contract): string
    {
        $contract->load(['booking.property', 'tenant', 'owner']);

        $booking  = $contract->booking;
        $property = $booking->property;
        $tenant   = $contract->tenant;
        $owner    = $contract->owner;

        $html = $this->buildContractHtml($contract, $booking, $property, $tenant, $owner);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $path     = 'contracts/' . $contract->reference . '.pdf';
        $pdfContent = $pdf->output();

        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    /**
     * Sign the contract as tenant using OTP verification.
     */
    public function signByTenant(Contract $contract, string $otpCode): bool
    {
        if ($contract->isSignedByTenant()) {
            return true;
        }

        $tenant  = $contract->tenant;
        $phone   = $tenant->phone;

        $valid = $this->otpService->verify($phone, $otpCode, 'contract_sign');

        if (!$valid) {
            return false;
        }

        $contract->update([
            'tenant_signed_at'    => now(),
            'tenant_signature_otp' => $otpCode,
        ]);

        // If both parties signed, update status
        if ($contract->isSignedByOwner()) {
            $contract->update(['status' => 'signed']);
        }

        return true;
    }

    /**
     * Sign the contract as owner using OTP verification.
     */
    public function signByOwner(Contract $contract, string $otpCode): bool
    {
        if ($contract->isSignedByOwner()) {
            return true;
        }

        $owner = $contract->owner;
        $phone = $owner->phone;

        $valid = $this->otpService->verify($phone, $otpCode, 'contract_sign');

        if (!$valid) {
            return false;
        }

        $contract->update([
            'owner_signed_at'    => now(),
            'owner_signature_otp' => $otpCode,
        ]);

        // If both parties signed, update status
        if ($contract->isSignedByTenant()) {
            $contract->update(['status' => 'signed']);
        }

        return true;
    }

    /**
     * Build the HTML for the contract PDF.
     */
    protected function buildContractHtml(
        Contract $contract,
        Booking $booking,
        $property,
        $tenant,
        $owner
    ): string {
        $today        = now()->format('d/m/Y');
        $checkIn      = $booking->check_in->format('d/m/Y');
        $checkOut     = $booking->check_out->format('d/m/Y');
        $nights       = $booking->nights;
        $totalAmount  = number_format($booking->total_amount, 0, ',', ' ');
        $deposit      = number_format($booking->deposit_amount, 0, ',', ' ');
        $pricePerNight = number_format($booking->price_per_night, 0, ',', ' ');
        $allowPets     = $property->allow_pets ? 'autorises' : 'non autorises';
        $allowSmoking  = $property->allow_smoking ? 'autorise' : 'strictement interdit';
        $allowParties  = $property->allow_parties ? 'autorisee' : 'non autorisee';
        $cancelPolicy  = $this->cancellationPolicyLabel($property->cancellation_policy);

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Contrat de Location - {$contract->reference}</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
  .header { text-align: center; border-bottom: 2px solid #F59E0B; padding-bottom: 15px; margin-bottom: 20px; }
  .header h1 { color: #1F2937; font-size: 20px; margin: 0 0 5px; }
  .header .subtitle { color: #F59E0B; font-size: 14px; font-weight: bold; }
  .logo { font-size: 24px; font-weight: bold; color: #F59E0B; }
  .section { margin-bottom: 20px; }
  .section-title { background: #FEF3C7; color: #92400E; padding: 6px 12px; font-weight: bold; font-size: 13px; margin-bottom: 10px; border-left: 4px solid #F59E0B; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  table td { padding: 6px 8px; border: 1px solid #E5E7EB; }
  table td:first-child { background: #F9FAFB; font-weight: bold; width: 40%; }
  .highlight { background: #FEF3C7; font-weight: bold; }
  .footer { margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 15px; font-size: 10px; color: #6B7280; text-align: center; }
  .signature-block { display: inline-block; width: 45%; margin: 10px 2%; text-align: center; border: 1px dashed #D1D5DB; padding: 20px; }
  .signature-row { width: 100%; }
  .article { margin-bottom: 12px; }
  .article-title { font-weight: bold; margin-bottom: 4px; }
  .ref-badge { background: #1F2937; color: white; padding: 4px 10px; border-radius: 4px; font-size: 11px; }
</style>
</head>
<body>

<div class="header">
  <div class="logo">KOLO IMMO</div>
  <h1>CONTRAT DE LOCATION MEUBLÉE</h1>
  <div class="subtitle">Référence : <span class="ref-badge">{$contract->reference}</span></div>
  <p style="color:#6B7280;margin:5px 0 0;">Établi le {$today}</p>
</div>

<div class="section">
  <div class="section-title">ARTICLE 1 — PARTIES AU CONTRAT</div>
  <p><strong>BAILLEUR (Propriétaire) :</strong></p>
  <table>
    <tr><td>Nom complet</td><td>{$owner->name}</td></tr>
    <tr><td>Téléphone</td><td>{$owner->phone}</td></tr>
    <tr><td>Email</td><td>{$owner->email}</td></tr>
    <tr><td>Ville</td><td>{$owner->city}</td></tr>
  </table>
  <p><strong>LOCATAIRE (Preneur) :</strong></p>
  <table>
    <tr><td>Nom complet</td><td>{$tenant->name}</td></tr>
    <tr><td>Téléphone</td><td>{$tenant->phone}</td></tr>
    <tr><td>Email</td><td>{$tenant->email}</td></tr>
    <tr><td>Ville</td><td>{$tenant->city}</td></tr>
  </table>
</div>

<div class="section">
  <div class="section-title">ARTICLE 2 — BIEN LOUÉ</div>
  <table>
    <tr><td>Titre</td><td>{$property->title}</td></tr>
    <tr><td>Type</td><td>{$property->typeLabel()}</td></tr>
    <tr><td>Adresse</td><td>{$property->address}</td></tr>
    <tr><td>Ville</td><td>{$property->city}, {$property->country}</td></tr>
    <tr><td>Capacité</td><td>{$property->capacity} personne(s)</td></tr>
    <tr><td>Chambres</td><td>{$property->bedrooms}</td></tr>
    <tr><td>Salles de bain</td><td>{$property->bathrooms}</td></tr>
  </table>
</div>

<div class="section">
  <div class="section-title">ARTICLE 3 — DURÉE DE LA LOCATION</div>
  <table>
    <tr><td>Date d'arrivée</td><td>{$checkIn} à partir de {$property->check_in_time}</td></tr>
    <tr><td>Date de départ</td><td>{$checkOut} avant {$property->check_out_time}</td></tr>
    <tr><td>Durée</td><td><strong>{$nights} nuit(s)</strong></td></tr>
    <tr><td>Nombre de voyageurs</td><td>{$booking->guests} personne(s)</td></tr>
  </table>
</div>

<div class="section">
  <div class="section-title">ARTICLE 4 — LOYER ET CONDITIONS FINANCIÈRES</div>
  <table>
    <tr><td>Prix par nuit</td><td>{$pricePerNight} FCFA</td></tr>
    <tr><td>Sous-total ({$nights} nuits)</td><td>{$totalAmount} FCFA</td></tr>
    <tr><td>Dépôt de garantie</td><td>{$deposit} FCFA</td></tr>
    <tr class="highlight"><td>MONTANT TOTAL</td><td><strong>{$totalAmount} FCFA</strong></td></tr>
  </table>
  <p>Le paiement est effectué via la plateforme KOLO IMMO. Le dépôt de garantie sera restitué dans un délai de 7 jours suivant le départ, déduction faite des éventuels dommages constatés.</p>
</div>

<div class="section">
  <div class="section-title">ARTICLE 5 — CONDITIONS D'UTILISATION</div>
  <div class="article">
    <div class="article-title">5.1 Animaux de compagnie</div>
    <p>Les animaux de compagnie sont {$allowPets} dans ce logement.</p>
  </div>
  <div class="article">
    <div class="article-title">5.2 Tabagisme</div>
    <p>Le tabagisme est {$allowSmoking} dans ce logement.</p>
  </div>
  <div class="article">
    <div class="article-title">5.3 Fêtes et événements</div>
    <p>L'organisation de fêtes et événements est {$allowParties} dans ce logement.</p>
  </div>
</div>

<div class="section">
  <div class="section-title">ARTICLE 6 — POLITIQUE D'ANNULATION</div>
  <p>Politique appliquée : <strong>{$cancelPolicy}</strong></p>
  <p>Flexible : remboursement complet si annulation 24h avant l'arrivée.<br>
     Modérée : remboursement à 50% si annulation 5 jours avant l'arrivée.<br>
     Stricte : aucun remboursement après confirmation du paiement.</p>
</div>

<div class="section">
  <div class="section-title">ARTICLE 7 — OBLIGATIONS DES PARTIES</div>
  <div class="article">
    <div class="article-title">7.1 Obligations du bailleur</div>
    <p>Le bailleur s'engage à mettre le logement à disposition dans un état propre et conforme à la description, à fournir toutes les commodités indiquées et à être disponible en cas de problème.</p>
  </div>
  <div class="article">
    <div class="article-title">7.2 Obligations du locataire</div>
    <p>Le locataire s'engage à utiliser le logement en bon père de famille, à respecter le règlement intérieur, à signaler tout dommage dans les 24 heures et à restituer le logement dans l'état où il l'a trouvé.</p>
  </div>
</div>

<div class="section">
  <div class="section-title">ARTICLE 8 — MÉDIATION ET LITIGES</div>
  <p>Tout litige relatif à ce contrat sera soumis en premier lieu à la médiation de KOLO IMMO. À défaut d'accord amiable, les parties s'en remettront aux tribunaux compétents du pays où se situe le logement.</p>
</div>

<div class="section">
  <div class="section-title">SIGNATURES ÉLECTRONIQUES</div>
  <p>Les signatures électroniques apposées via OTP ont la même valeur juridique qu'une signature manuscrite conformément aux lois en vigueur.</p>
  <table class="signature-row">
    <tr>
      <td style="width:50%;text-align:center;padding:20px;border:1px dashed #D1D5DB;">
        <p><strong>LE BAILLEUR</strong></p>
        <p>{$owner->name}</p>
        {$this->signatureStatus($contract->owner_signed_at)}
      </td>
      <td style="width:50%;text-align:center;padding:20px;border:1px dashed #D1D5DB;">
        <p><strong>LE LOCATAIRE</strong></p>
        <p>{$tenant->name}</p>
        {$this->signatureStatus($contract->tenant_signed_at)}
      </td>
    </tr>
  </table>
</div>

<div class="footer">
  <p>Ce contrat est généré automatiquement par la plateforme KOLO IMMO · www.koloimmo.com</p>
  <p>Référence de réservation : {$booking->reference} · Référence contrat : {$contract->reference}</p>
  <p>© {$today} KOLO IMMO — Tous droits réservés</p>
</div>

</body>
</html>
HTML;
    }

    protected function cancellationPolicyLabel(string $policy): string
    {
        return match($policy) {
            'flexible' => 'Flexible',
            'moderate' => 'Modérée',
            'strict'   => 'Stricte',
            default    => ucfirst($policy),
        };
    }

    protected function signatureStatus($signedAt): string
    {
        if ($signedAt) {
            $date = \Carbon\Carbon::parse($signedAt)->format('d/m/Y H:i');
            return "<p style='color:#16A34A;font-weight:bold;'>✓ Signé électroniquement<br><small>{$date}</small></p>";
        }
        return "<p style='color:#DC2626;'>En attente de signature</p>";
    }
}

