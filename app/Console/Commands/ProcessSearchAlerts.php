<?php

namespace App\Console\Commands;

use App\Mail\SearchAlertMail;
use App\Models\SearchAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessSearchAlerts extends Command
{
    protected $signature   = 'alerts:process';
    protected $description = 'Send search alert emails for matching new properties';

    public function handle(): int
    {
        $alerts = SearchAlert::where('is_active', true)
            ->with('user')
            ->get()
            ->filter(fn($a) => $a->isDue());

        $this->info("Processing {$alerts->count()} due alert(s)...");

        foreach ($alerts as $alert) {
            try {
                // Get already-sent property IDs for this alert
                $sentIds = DB::table('search_alert_sent')
                    ->where('search_alert_id', $alert->id)
                    ->pluck('property_id');

                // Find new matching properties not yet sent
                $newProperties = $alert->buildQuery()
                    ->whereNotIn('id', $sentIds)
                    ->latest()
                    ->limit(10)
                    ->get();

                if ($newProperties->isEmpty()) {
                    $alert->update(['last_sent_at' => now()]);
                    continue;
                }

                // Send email
                if ($alert->user->email) {
                    Mail::to($alert->user->email)->queue(new SearchAlertMail($alert, $newProperties));
                }

                // Mark as sent
                $rows = $newProperties->map(fn($p) => [
                    'search_alert_id' => $alert->id,
                    'property_id'     => $p->id,
                ]);
                DB::table('search_alert_sent')->insertOrIgnore($rows->toArray());

                $alert->update(['last_sent_at' => now()]);
                $this->info("  ✓ Alert #{$alert->id} ({$alert->name}): {$newProperties->count()} properties sent to {$alert->user->email}");

            } catch (\Throwable $e) {
                Log::error("[SearchAlerts] Alert #{$alert->id} failed: " . $e->getMessage());
                $this->error("  ✗ Alert #{$alert->id} failed: " . $e->getMessage());
            }
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
