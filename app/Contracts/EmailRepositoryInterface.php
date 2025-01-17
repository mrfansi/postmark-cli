<?php

namespace App\Contracts;

use App\Postmark\DTOs\EmailBatchResponse;
use App\Postmark\DTOs\EmailData;
use App\Postmark\DTOs\EmailResponse;
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
     * Set server token
     *
     * @param  string  $token  Server token
     */
    public function withServerToken(string $token): void;

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
