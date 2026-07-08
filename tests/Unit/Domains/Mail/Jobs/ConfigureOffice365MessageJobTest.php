<?php

namespace Tests\Unit\Domains\Mail\Jobs;

use App\Domains\Mail\Jobs\ConfigureOffice365MessageJob;
use PHPMailer\PHPMailer\PHPMailer;
use Tests\TestCase;

class ConfigureOffice365MessageJobTest extends TestCase
{
    public function test_it_populates_the_message_with_addresses_body_and_attachments(): void
    {
        config()->set('mail.office365_from', [
            'address' => 'winman@condimentum.co.uk',
            'name' => 'WinMan',
        ]);

        $attachmentPath = tempnam(sys_get_temp_dir(), 'mail-attachment-');
        file_put_contents($attachmentPath, 'attachment body');

        $mailer = new PHPMailer(true);

        app(ConfigureOffice365MessageJob::class)(
            $mailer,
            [['email' => 'to@example.com', 'name' => 'Primary User']],
            'Test Subject',
            '<p>Hello world</p>',
            'Hello world',
            'cc@example.com',
            [['email' => 'bcc@example.com', 'name' => 'Blind Copy']],
            [['email' => 'reply@example.com', 'name' => 'Reply User']],
            [$attachmentPath],
        );

        $this->assertSame('winman@condimentum.co.uk', $mailer->From);
        $this->assertSame('WinMan', $mailer->FromName);
        $this->assertSame([['to@example.com', 'Primary User']], $mailer->getToAddresses());
        $this->assertSame([['cc@example.com', '']], $mailer->getCcAddresses());
        $this->assertSame([['bcc@example.com', 'Blind Copy']], $mailer->getBccAddresses());
        $this->assertSame([['reply@example.com', 'Reply User']], $mailer->getReplyToAddresses());
        $this->assertSame('Test Subject', $mailer->Subject);
        $this->assertSame('<p>Hello world</p>', $mailer->Body);
        $this->assertSame('Hello world', $mailer->AltBody);
        $this->assertCount(1, $mailer->getAttachments());

        @unlink($attachmentPath);
    }
}
