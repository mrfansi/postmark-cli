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

namespace App\Data\Email;

/**
 * Email Attachment Data Transfer Object
 *
 * This class represents the data structure for email attachments.
 * It ensures type safety and data validation.
 */
readonly class AttachmentData
{
    /**
     * Create a new AttachmentData instance
     *
     * @param  string  $name  The name of the attachment
     * @param  string  $content  Base64 encoded content of the attachment
     * @param  string  $contentType  The MIME type of the attachment
     * @param  string|null  $contentId  Content ID for inline images
     */
    public function __construct(
        public string $name,
        public string $content,
        public string $contentType,
        public ?string $contentId = null,
    ) {}

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'Name' => $this->name,
            'Content' => $this->content,
            'ContentType' => $this->contentType,
            'ContentID' => $this->contentId,
        ]);
    }
}
