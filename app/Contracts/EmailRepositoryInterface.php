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

namespace App\Contracts;

use App\Data\EmailBatchResponse;
use App\Data\EmailData;
use App\Data\EmailResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Email Repository
 *
 * This interface defines the contract for interacting with Postmark Email API.
 * It provides methods for sending various types of emails through Postmark.
 */
interface EmailRepositoryInterface
{
    /**
     * Set server identifier for authentication
     *
     * @param  int  $serverId  Server identifier from Postmark
     */
    public function withServer(int $serverId): self;

    /**
     * Set server token for authentication
     *
     * @param  string  $serverToken  Server token from Postmark
     */
    public function withServerToken(string $serverToken): self;

    /**
     * Send a single email
     *
     * @param  EmailData  $data  Email data containing from, to, subject, and content
     */
    public function send(EmailData $data): EmailResponse;

    /**
     * Send a batch of emails
     *
     * @param  Collection<EmailData>  $data  Collection of email data
     */
    public function sendBatch(Collection $data): EmailBatchResponse;

    /**
     * Send an email with template
     *
     * @param  int  $templateId  Template identifier
     * @param  EmailData  $data  Email data
     */
    public function sendWithTemplate(int $templateId, EmailData $data): EmailResponse;

    /**
     * Send a batch of emails with template
     *
     * @param  int  $templateId  Template identifier
     * @param  Collection<EmailData>  $data  Collection of email data
     */
    public function sendBatchWithTemplate(int $templateId, Collection $data): EmailBatchResponse;
}
