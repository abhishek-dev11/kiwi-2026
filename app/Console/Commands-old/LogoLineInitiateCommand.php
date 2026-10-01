<?php

namespace App\Console\Commands;

use App\Services\LogoLineAuthService;
use Illuminate\Console\Command;

class LogoLineInitiateCommand extends Command
{
    /**
     * auth_token is single-use — this command should only be run once per
     * token issued by support@logoline.com.au (docs: "Used once only when
     * setting up Bearer Authentication for the first time").
     *
     * @var string
     */
    protected $signature = 'logoline:initiate {auth_token}';

    protected $description = 'One-time: exchange a Logo Line auth_token for a stored access/refresh token pair';

    public function handle(LogoLineAuthService $auth): int
    {
        $token = $auth->initiate($this->argument('auth_token'));

        $this->info("Logo Line Bearer auth initiated. Access token expires at {$token->expires_at}.");

        return self::SUCCESS;
    }
}
