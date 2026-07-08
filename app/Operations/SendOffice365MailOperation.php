<?php

namespace App\Operations;

use App\Domains\Mail\Jobs\BuildOffice365MailerJob;
use App\Domains\Mail\Jobs\ConfigureOffice365MessageJob;
use App\Domains\Mail\Jobs\SendOffice365MessageJob;

class SendOffice365MailOperation
{
    public function __construct(
        private readonly BuildOffice365MailerJob $buildOffice365Mailer,
        private readonly ConfigureOffice365MessageJob $configureOffice365Message,
        private readonly SendOffice365MessageJob $sendOffice365Message,
    ) {
    }

    public function __invoke(
        array|string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array|string $cc = [],
        array|string $bcc = [],
        array|string $replyTo = [],
        array $attachments = [],
    ): void {
        $mailer = ($this->buildOffice365Mailer)();

        ($this->configureOffice365Message)(
            $mailer,
            $to,
            $subject,
            $htmlBody,
            $textBody,
            $cc,
            $bcc,
            $replyTo,
            $attachments,
        );

        ($this->sendOffice365Message)($mailer);
    }
}
