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
 * Sender Response Data Transfer Object
 *
 * This class represents the response data structure from the Postmark API
 * for server-related operations.
 */
readonly class SenderListResponse
{
    /**
     * Create a new SenderResponse instance
     *
     * @param  int  $id  Unique ID of sender signature.
     * @param  string  $domain  Domain associated with sender signature.
     * @param  string  $emailAddress  string of objects that each represent a sender signature.
     * @param  string  $replyToEmailAddress  Reply-To email associated with sender signature.
     * @param  string  $name  From name of sender signature.
     * @param  bool  $confirmed  Indicates whether this sender signature has been confirmed.
     */
    public function __construct(
        public int $id,
        public string $domain,
        public string $emailAddress,
        public string $replyToEmailAddress,
        public string $name,
        public bool $confirmed,
    ) {}

    /**
     * Create a SenderResponse instance from an array
     *
     * @param  array  $data  Sender data from API response
     * @return self New SenderResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['ID'],
            domain: $data['Domain'],
            emailAddress: $data['EmailAddress'],
            replyToEmailAddress: $data['ReplyToEmailAddress'],
            name: $data['Name'],
            confirmed: $data['Confirmed'],
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
            'id' => $this->id,
            'domain' => $this->domain,
            'emailAddress' => $this->emailAddress,
            'replyToEmailAddress' => $this->replyToEmailAddress,
            'name' => $this->name,
            'confirmed' => $this->confirmed,
        ];
    }
}
