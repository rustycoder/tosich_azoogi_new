<?php

namespace App\Services;

use App\Services\Contracts\ITurnstileService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService implements ITurnstileService
{
    public function verify(?string $response, ?string $remoteIp = null): bool
    {
        if (! (bool) config('services.turnstile.enabled', true)) {
            return true;
        }

        if (empty($response)) {
            return false;
        }

        $secret = config('services.turnstile.secret_key');
        if (empty($secret)) {
            Log::warning('Turnstile secret key is missing in configuration.');

            return false;
        }

        $url = config('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

        try {
            $payload = [
                'secret' => $secret,
                'response' => $response,
            ];

            if (! empty($remoteIp)) {
                $payload['remoteip'] = $remoteIp;
            }

            $result = Http::asForm()
                ->timeout(5)
                ->post($url, $payload);

            if (! $result->successful()) {
                Log::warning('Turnstile siteverify HTTP request failed with status: '.$result->status());

                return false;
            }

            $body = $result->json();
            $success = (bool) ($body['success'] ?? false);

            if (! $success) {
                Log::info('Turnstile verification failed.', [
                    'errors' => $body['error-codes'] ?? [],
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::error('Turnstile verification encountered an exception: '.$e->getMessage());

            return false;
        }
    }
}
