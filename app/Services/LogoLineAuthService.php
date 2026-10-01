<?php

namespace App\Services;

use App\Models\LogoLineToken;
use Illuminate\Support\Facades\Http;

/**
 * Logo Line's Bearer auth issues a single-use refresh_token on every refresh —
 * two processes refreshing concurrently would race and lock each other out
 * (docs: "if your refresh_token is lost, overwritten, or used more than once,
 * you will be locked out and will need to contact support@logoline.com.au").
 * All reads/writes of the token pair go through this service so the DB row
 * stays the single source of truth across queue workers, matching the docs'
 * "must be stored securely and centrally" requirement.
 */
class LogoLineAuthService
{
    // Refresh this many seconds before actual expiry so a slow request never
    // gets a token that dies mid-flight.
    const EXPIRY_BUFFER_SECONDS = 90;

    public function getAccessToken(): string
    {
        $token = LogoLineToken::query()->orderByDesc('id')->first();

        if (!$token) {
            throw new \RuntimeException(
                'No Logo Line token on file. Run: php artisan logoline:initiate {auth_token} once with a fresh auth_token from the Logo Line dashboard.'
            );
        }

        if (now()->addSeconds(self::EXPIRY_BUFFER_SECONDS)->greaterThanOrEqualTo($token->expires_at)) {
            $token = $this->refresh();
        }

        return $token->access_token;
    }

    public function refresh(): LogoLineToken
    {
        $lock = cache()->lock('logoline_token_refresh', 30);

        try {
            $lock->block(30);

            // Re-check inside the lock — another worker may have already
            // refreshed while we were waiting.
            $token = LogoLineToken::query()->orderByDesc('id')->first();

            if (!$token) {
                throw new \RuntimeException('No Logo Line token on file to refresh — run logoline:initiate first.');
            }

            if (now()->addSeconds(self::EXPIRY_BUFFER_SECONDS)->lessThan($token->expires_at)) {
                return $token;
            }

            $baseUrl = rtrim(config('services.logoline.base_url'), '/');

            $response = Http::acceptJson()
                ->timeout(30)
                ->post("{$baseUrl}/v4/Authentication/Refresh/Bearer", [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $token->refresh_token,
                    'client_id'     => config('services.logoline.client_id'),
                    'client_secret' => config('services.logoline.client_secret'),
                    'scope'         => 'read',
                ]);

            if ($response->failed()) {
                throw new \RuntimeException(
                    "Logo Line token refresh failed: HTTP {$response->status()} — {$response->body()}. "
                    . "The refresh_token may already be used/invalidated — re-run logoline:initiate with a new auth_token from support@logoline.com.au."
                );
            }

            $body = $response->json();

            $token->update([
                'access_token'  => $body['access_token'],
                'refresh_token' => $body['refresh_token'],
                'expires_at'    => now()->addSeconds((int) ($body['expires_in'] ?? 1800)),
            ]);

            \Log::info('LogoLineAuthService: token refreshed, expires ' . $token->expires_at);

            return $token->fresh();

        } finally {
            optional($lock)->release();
        }
    }

    public function initiate(string $authToken): LogoLineToken
    {
        $baseUrl = rtrim(config('services.logoline.base_url'), '/');

        $response = Http::acceptJson()
            ->timeout(30)
            ->post("{$baseUrl}/v4/Authentication/Initiate/Bearer", [
                'auth_token' => $authToken,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Logo Line Initiate failed: HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body = $response->json();

        return LogoLineToken::query()->create([
            'access_token'  => $body['access_token'],
            'refresh_token' => $body['refresh_token'],
            'expires_at'    => now()->addSeconds((int) ($body['expires_in'] ?? 1800)),
        ]);
    }
}
