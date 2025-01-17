<?php

namespace App\Postmark\DTOs;

use App\Postmark\DTOs\Email\AttachmentData;
use App\Postmark\DTOs\Email\HeaderData;

/**
 * Email Data Transfer Object
 *
 * This class represents the data structure for sending an email through Postmark.
 * It ensures type safety and data validation.
 *
 * @package App\Postmark\DTOs
 */
readonly class EmailData
{
    /**
     * Create a new EmailData instance
     *
     * @param string $from Required. The sender email address. Must have a registered and confirmed Sender Signature.
     *                     To include a name, use the format "Full Name <sender@domain.com>".
     *                     Punctuation in the name would need to be escaped.
     * @param string $to Required. Recipient email address. Multiple addresses are comma separated. Max 50.
     * @param string $subject Required. Email subject.
     * @param string|null $cc Optional. Cc recipient email address. Multiple addresses are comma separated. Max 50.
     * @param string|null $bcc Optional. Bcc recipient email address. Multiple addresses are comma separated. Max 50.
     * @param string|null $htmlBody Required if TextBody is not specified. HTML email message.
     * @param string|null $textBody Required if HtmlBody is not specified. Plain text email message.
     * @param string|null $replyTo Optional. Reply To override email address. Defaults to the Reply To set in the sender signature.
     *                             Multiple addresses are comma separated.
     * @param HeaderData[] $headers Optional. List of custom headers to include.
     * @param bool $trackOpens Optional. Activate open tracking for this email.
     * @param string|null $trackLinks Optional. Activate link tracking for links in the HTML or Text bodies of this email.
     *                                Possible values: None, HtmlAndText, HtmlOnly, TextOnly.
     * @param AttachmentData[] $attachments Optional. List of attachments.
     * @param array<string, string> $metadata Optional. Custom metadata key/value pairs.
     * @param string|null $messageStream Optional. Set message stream ID that's used for sending.
     *                                   If not provided, message will default to the "outbound" transactional stream.
     * @param string|null $tag Optional. Email tag that allows you to categorize outgoing emails and get detailed statistics.
     *                         Max characters 1000.
     */
    public function __construct(
        public string $from,
        public string $to,
        public string $subject,
        public ?string $cc = null,
        public ?string $bcc = null,
        public ?string $htmlBody = null,
        public ?string $textBody = null,
        public ?string $replyTo = null,
        public array $headers = [],
        public bool $trackOpens = false,
        public ?string $trackLinks = null,
        public array $attachments = [],
        public array $metadata = [],
        public ?string $messageStream = null,
        public ?string $tag = null,
    ) {
        if ($htmlBody === null && $textBody === null) {
            throw new \InvalidArgumentException('Either HtmlBody or TextBody must be specified');
        }
    }

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'From' => $this->from,
            'To' => $this->to,
            'Subject' => $this->subject,
            'Cc' => $this->cc,
            'Bcc' => $this->bcc,
            'HtmlBody' => $this->htmlBody,
            'TextBody' => $this->textBody,
            'ReplyTo' => $this->replyTo,
            'Headers' => array_map(fn(HeaderData $header) => $header->toArray(), $this->headers),
            'TrackOpens' => $this->trackOpens,
            'TrackLinks' => $this->trackLinks,
            'Attachments' => array_map(fn(AttachmentData $attachment) => $attachment->toArray(), $this->attachments),
            'Metadata' => $this->metadata,
            'MessageStream' => $this->messageStream,
            'Tag' => $this->tag,
        ];

        return array_filter($data, fn($value) => $value !== null);
    }
}
