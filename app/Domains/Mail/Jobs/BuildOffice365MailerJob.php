<?php

namespace App\Domains\Mail\Jobs;

use Greew\OAuth2\Client\Provider\Azure;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class BuildOffice365MailerJob
{
    public function __invoke(): PHPMailer
    {
        $mailerConfig = config('mail.mailers.office365', []);
        $oauthConfig = config('services.microsoft_mail', []);

        foreach (['host', 'port', 'username'] as $key) {
            if (blank($mailerConfig[$key] ?? null)) {
                throw new RuntimeException("Office 365 mailer setting [{$key}] is missing.");
            }
        }

        foreach (['tenant_id', 'client_id', 'refresh_token', 'username'] as $key) {
            if (blank($oauthConfig[$key] ?? null)) {
                throw new RuntimeException("Microsoft mail OAuth setting [{$key}] is missing.");
            }
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = (string) $mailerConfig['host'];
        $mailer->Port = (int) $mailerConfig['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = (string) $mailerConfig['username'];
        $mailer->SMTPSecure = (string) ($mailerConfig['scheme'] ?? 'tls');
        $mailer->AuthType = 'XOAUTH2';
        $mailer->CharSet = 'UTF-8';

        $mailer->setOAuth(new OAuth([
            'provider' => new Azure([
                'clientId' => (string) $oauthConfig['client_id'],
                'tenantId' => (string) $oauthConfig['tenant_id'],
                'clientSecret' => (string) ($oauthConfig['client_secret'] ?? ''),
            ]),
            'clientId' => (string) $oauthConfig['client_id'],
            'clientSecret' => (string) ($oauthConfig['client_secret'] ?? ''),
            'refreshToken' => (string) $oauthConfig['refresh_token'],
            'userName' => (string) $oauthConfig['username'],
        ]));

        return $mailer;
    }
}
