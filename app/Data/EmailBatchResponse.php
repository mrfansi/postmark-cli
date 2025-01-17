<?php

/*
 * Copyright (c) 2025 Muhammad irfan.
 * All rights reserved.
 *
 * This project is created and maintained by Muhammad Irfan. Redistribution or modification
 * of this code is permitted only under the terms specified in the license.
 *
 * @package    postmark-cli
 * @license    MIT
 * @author     Muhammad Irfan <mrfansi@outlook.com>
 * @version    1.0.0
 * @since      2025-01-18
 */

namespace App\Data;

/**
 * Email Batch Response Data Transfer Object
 *
 * This class represents the response data structure after sending batch emails through Postmark.
 * It ensures type safety and data validation.
 */
readonly class EmailBatchResponse
{
    /**
     * Create a new EmailBatchResponse instance
     *
     * @param  string|null  $to  The recipient email address
     * @param  string|null  $submittedAt  The timestamp when the email was submitted (ISO 8601 format)
     * @param  string|null  $messageId  The unique identifier for the email message
     * @param  int  $errorCode  The error code (0 indicates success)
     * @param  string  $message  The response message
     */
    public function __construct(
        public ?string $to,
        public ?string $submittedAt,
        public ?string $messageId,
        public int $errorCode,
        public string $message,
    ) {}

    /**
     * Create an EmailBatchResponse instance from an array
     *
     * @param  array  $data  Email batch response data from API response
     * @return self New EmailBatchResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            to: $data['To'] ?? null,
            submittedAt: $data['SubmittedAt'] ?? null,
            messageId: $data['MessageID'] ?? null,
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
     */
    public function isSuccess(): bool
    {
        return $this->errorCode === 0;
    }
}
