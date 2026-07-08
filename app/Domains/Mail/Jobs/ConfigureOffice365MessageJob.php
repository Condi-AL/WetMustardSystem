<?php

namespace App\Domains\Mail\Jobs;

use InvalidArgumentException;
use PHPMailer\PHPMailer\PHPMailer;

class ConfigureOffice365MessageJob
{
    public function __invoke(
        PHPMailer $mailer,
        array|string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array|string $cc = [],
        array|string $bcc = [],
        array|string $replyTo = [],
        array $attachments = [],
    ): PHPMailer {
        $from = config('mail.office365_from', []);

        $mailer->setFrom(
            (string) ($from['address'] ?? ''),
            (string) ($from['name'] ?? '')
        );

        $this->addRecipients($mailer, $to, 'addAddress');
        $this->addRecipients($mailer, $cc, 'addCC');
        $this->addRecipients($mailer, $bcc, 'addBCC');
        $this->addRecipients($mailer, $replyTo, 'addReplyTo');

        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $mailer->addAttachment($attachment);
                continue;
            }

            $path = $attachment['path'] ?? null;

            if (blank($path)) {
                throw new InvalidArgumentException('Attachment path is required.');
            }

            $mailer->addAttachment($path, $attachment['name'] ?? '');
        }

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody ?? trim(strip_tags($htmlBody));

        return $mailer;
    }

    private function addRecipients(PHPMailer $mailer, array|string $recipients, string $method): void
    {
        foreach ($this->normalizeRecipients($recipients) as $recipient) {
            $mailer->{$method}($recipient['email'], $recipient['name']);
        }
    }

    private function normalizeRecipients(array|string $recipients): array
    {
        if (blank($recipients)) {
            return [];
        }

        if (is_string($recipients)) {
            return [[
                'email' => $recipients,
                'name' => '',
            ]];
        }

        if (array_key_exists('email', $recipients)) {
            return [[
                'email' => (string) $recipients['email'],
                'name' => (string) ($recipients['name'] ?? ''),
            ]];
        }

        return array_map(function (mixed $recipient): array {
            if (is_string($recipient)) {
                return [
                    'email' => $recipient,
                    'name' => '',
                ];
            }

            return [
                'email' => (string) ($recipient['email'] ?? ''),
                'name' => (string) ($recipient['name'] ?? ''),
            ];
        }, $recipients);
    }
}
