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
 * Sender Data Transfer Object
 *
 * This class represents the data structure for creating or updating a sender.
 * It ensures type safety and data validation.
 */
readonly class SenderData
{
    /**
     * Create a new SenderData instance
     *
     * @param  string  $fromEmail  From email associated with sender signature.
     * @param  string  $name  From name associated with sender signature.
     * @param  string|null  $replyToEmail  Override for reply-to address.
     * @param  string|null  $returnPathDomain  A custom value for the Return-Path domain. It is an optional field, but it must be a subdomain of your From Email domain and must have a CNAME record that points to pm.mtasv.net
     * @param  string|null  $confirmationPersonalNote  Optional. A way to provide a note to the recipient of the confirmation email to have context of what Postmark is. Max length of 400 characters.
     */
    public function __construct(
        public string $fromEmail,
        public string $name,
        public ?string $replyToEmail,
        public ?string $returnPathDomain,
        public ?string $confirmationPersonalNote,
    ) {}

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'FromEmail' => $this->fromEmail,
            'Name' => $this->name,
            'ReplyToEmail' => $this->replyToEmail,
            'ReturnPathDomain' => $this->returnPathDomain,
            'ConfirmationPersonalNote' => $this->confirmationPersonalNote,
        ];
    }
}
