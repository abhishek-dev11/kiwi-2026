<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class XeroController extends Controller
{
    // -------------------------------------------------------------------------
    // STEP 1 — Redirect to Xero login page
    // Visit: https://kiwi.infosparkles.net/xero/connect
    // -------------------------------------------------------------------------
    public function connect()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.xero.client_id'),
            'redirect_uri'  => config('services.xero.redirect_uri'),
            'scope'         => 'accounting.transactions accounting.contacts offline_access',
            'state'         => csrf_token(),
        ]);
        //dd($query);

        return redirect('https://login.xero.com/identity/connect/authorize?' . $query);
    }

    // -------------------------------------------------------------------------
    // STEP 2 — Xero redirects back here with a `code`
    // Xero hits: https://kiwi.infosparkles.net/xero/callback?code=xxx
    // -------------------------------------------------------------------------
    public function callback(Request $request)
    {

        // --- 2a. Exchange the code for access + refresh tokens ---
        $tokenResponse = Http::asForm()
            ->withBasicAuth(
                config('services.xero.client_id'),
                config('services.xero.client_secret')
            )
            ->post('https://identity.xero.com/connect/token', [
                'grant_type'   => 'authorization_code',
                'code'         => $request->input('code'),
                'redirect_uri' => config('services.xero.redirect_uri'),
            ]);

        if ($tokenResponse->failed()) {
            Log::error('Xero token exchange failed', $tokenResponse->json());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Xero tokens. Check logs.',
            ], 500);
        }

        $tokenData = $tokenResponse->json();

        // --- 2b. Fetch the Tenant ID (Xero Organisation ID) ---
        $connectionsResponse = Http::withToken($tokenData['access_token'])
            ->get('https://api.xero.com/connections');

        if ($connectionsResponse->failed()) {
            Log::error('Xero connections fetch failed', $connectionsResponse->json());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Xero tenant. Check logs.',
            ], 500);
        }

        $connections = $connectionsResponse->json();

        if (empty($connections)) {
            return response()->json([
                'success' => false,
                'message' => 'No Xero organisations found. Make sure you are logged into the correct Xero account.',
            ], 400);
        }

        $tenantId = $connections[0]['tenantId'];
        $tenantName = $connections[0]['tenantName'];

        // --- 2c. Store tokens in xero_tokens (only 1 row needed) ---
        DB::table('xero_tokens')->truncate();
        DB::table('xero_tokens')->insert([
            'access_token'  => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'tenant_id'     => $tenantId,
            'expires_at'    => Carbon::now()->addSeconds($tokenData['expires_in']),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // --- 2d. Also write Tenant ID to .env automatically ---
        $this->writeEnvValue('XERO_TENANT_ID', $tenantId);

        return response()->json([
            'success'     => true,
            'message'     => 'Xero connected successfully!',
            'tenant_id'   => $tenantId,
            'tenant_name' => $tenantName,
            'expires_in'  => $tokenData['expires_in'] . ' seconds',
            'note'        => 'XERO_TENANT_ID has been written to your .env file.',
        ]);
    }

    // -------------------------------------------------------------------------
    // STEP 3 — Check connection status (useful for admin dashboard)
    // GET: /xero/status
    // -------------------------------------------------------------------------
    public function status()
    {
        $token = DB::table('xero_tokens')->latest()->first();

        if (!$token) {
            return response()->json([
                'connected' => false,
                'message'   => 'Xero is not connected. Visit /xero/connect to authenticate.',
            ]);
        }

        $isExpired = Carbon::now()->gte($token->expires_at);

        return response()->json([
            'connected'   => true,
            'tenant_id'   => $token->tenant_id,
            'expires_at'  => $token->expires_at,
            'is_expired'  => $isExpired,
            'message'     => $isExpired
                ? 'Token is expired — it will auto-refresh on next invoice creation.'
                : 'Xero is connected and token is valid.',
        ]);
    }

    // -------------------------------------------------------------------------
    // STEP 4 — Disconnect Xero (admin use only)
    // DELETE: /xero/disconnect
    // -------------------------------------------------------------------------
    public function disconnect()
    {
        DB::table('xero_tokens')->truncate();

        return response()->json([
            'success' => true,
            'message' => 'Xero disconnected. Visit /xero/connect to reconnect.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper — Write XERO_TENANT_ID into .env automatically
    // -------------------------------------------------------------------------
    private function writeEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        // If key exists — replace the value
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Key doesn't exist — append it
            $envContent .= "\n{$key}={$value}\n";
        }

        file_put_contents($envPath, $envContent);
    }
}