<?php

namespace App\Postmark\DTOs;

/**
 * Email Response Data Transfer Object
 *
 * This class represents the response data structure after sending an email through Postmark.
 * It ensures type safety and data validation.
 *
 * @package App\Postmark\DTOs
 */
readonly class EmailResponse
{
    /**
     * Create a new EmailResponse instance
     *
     * @param string $to The recipient email address
     * @param string $submittedAt The timestamp when the email was submitted (ISO 8601 format)
     * @param string $messageId The unique identifier for the email message
     * @param int $errorCode The error code (0 indicates success)
     * @param string $message The response message (e.g., "OK" for successful submission)
     */
    public function __construct(
        public string $to,
        public string $submittedAt,
        public string $messageId,
        public int $errorCode,
        public string $message,
    ) {}

    /**
     * Create an EmailResponse instance from an array
     *
     * @param array $data Email response data from API response
     * @return self New EmailResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            to: $data['To'],
            submittedAt: $data['SubmittedAt'],
            messageId: $data['MessageID'],
            errorCode: $data['ErrorCode'],
            message: $data['Message'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'To' => $this->to,
            'SubmittedAt' => $this->submittedAt,
            'MessageID' => $this->messageId,
            'ErrorCode' => $this->errorCode,
            'Message' => $this->message,
        ];
    }

    /**
     * Check if the email was sent successfully
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->errorCode === 0;
    }
}
