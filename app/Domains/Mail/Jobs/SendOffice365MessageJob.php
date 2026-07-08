<?php

namespace App\Domains\Mail\Jobs;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class SendOffice365MessageJob
{
    public function __invoke(PHPMailer $mailer): void
    {
        try {
            $mailer->send();
        } catch (MailException $exception) {
            throw new RuntimeException('Office 365 mail send failed: '.$exception->getMessage(), 0, $exception);
        }
    }
}
