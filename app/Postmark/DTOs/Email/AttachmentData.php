<?php

namespace App\Postmark\DTOs\Email;

/**
 * Email Attachment Data Transfer Object
 *
 * This class represents the data structure for email attachments.
 * It ensures type safety and data validation.
 *
 * @package App\Postmark\DTOs\Email
 */
readonly class AttachmentData
{
    /**
     * Create a new AttachmentData instance
     *
     * @param string $name The name of the attachment
     * @param string $content Base64 encoded content of the attachment
     * @param string $contentType The MIME type of the attachment
     * @param string|null $contentId Content ID for inline images
     */
    public function __construct(
        public string $name,
        public string $content,
        public string $contentType,
        public ?string $contentId = null,
    ) {
    }

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
