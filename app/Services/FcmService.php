<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $serverKey;
    private string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key', '');
    }

    /**
     * Send a push notification to a single user (all their devices).
     */
    public function notifyUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        if (empty($tokens)) return;

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send to multiple users.
     */
    public function notifyUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();

        if (empty($tokens)) return;

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send to a list of FCM tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (empty($this->serverKey) || $this->serverKey === 'your-fcm-server-key') {
            Log::warning('[FCM] Clé serveur non configurée — notification ignorée.', [
                'title' => $title, 'body' => $body,
            ]);
            return;
        }

        // FCM max 500 tokens par requête
        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                $payload = [
                    'registration_ids' => $chunk,
                    'notification'     => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                    ],
                    'data'             => $data,
                    'priority'         => 'high',
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type'  => 'application/json',
                ])->post($this->endpoint, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    Log::info('[FCM] Envoi réussi', [
                        'success' => $result['success'] ?? 0,
                        'failure' => $result['failure'] ?? 0,
                    ]);

                    // Supprimer les tokens invalides
                    if (!empty($result['results'])) {
                        foreach ($result['results'] as $i => $res) {
                            if (isset($res['error']) && in_array($res['error'], ['NotRegistered', 'InvalidRegistration'])) {
                                DeviceToken::where('token', $chunk[$i])->delete();
                            }
                        }
                    }
                } else {
                    Log::error('[FCM] Échec HTTP', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::error('[FCM] Exception', ['error' => $e->getMessage()]);
            }
        }
    }
}
