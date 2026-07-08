<?php

namespace Tests\Unit\Domains\Mail\Jobs;

use App\Domains\Mail\Jobs\BuildOffice365MailerJob;
use PHPMailer\PHPMailer\PHPMailer;
use Tests\TestCase;

class BuildOffice365MailerJobTest extends TestCase
{
    public function test_it_builds_a_phpmailer_instance_for_office365_oauth(): void
    {
        config()->set('mail.mailers.office365', [
            'host' => 'smtp.office365.com',
            'port' => 587,
            'username' => 'winman@condimentum.co.uk',
            'scheme' => 'tls',
        ]);

        config()->set('services.microsoft_mail', [
            'tenant_id' => 'tenant-id',
            'client_id' => 'client-id',
            'client_secret' => '',
            'refresh_token' => 'refresh-token',
            'username' => 'winman@condimentum.co.uk',
        ]);

        $mailer = app(BuildOffice365MailerJob::class)();

        $this->assertInstanceOf(PHPMailer::class, $mailer);
        $this->assertSame('smtp', $mailer->Mailer);
        $this->assertSame('smtp.office365.com', $mailer->Host);
        $this->assertSame(587, $mailer->Port);
        $this->assertTrue($mailer->SMTPAuth);
        $this->assertSame('winman@condimentum.co.uk', $mailer->Username);
        $this->assertSame('tls', $mailer->SMTPSecure);
        $this->assertSame('XOAUTH2', $mailer->AuthType);
    }
}
