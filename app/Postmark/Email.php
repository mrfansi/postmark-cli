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

namespace App\Postmark;

use App\Contracts\EmailRepositoryInterface;
use App\Data\EmailBatchResponse;
use App\Data\EmailData;
use App\Data\EmailResponse;
use App\Postmark\Concerns\HasServerToken;
use BadMethodCallException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Email API Client
 *
 * This class handles all email-related operations with the Postmark API.
 * It implements the EmailRepositoryInterface for standardized email operations.
 */
class Email implements EmailRepositoryInterface
{
    use HasServerToken;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Constructor for Email API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     */
    public function __construct(PendingRequest $client)
    {
        $this->client = $client;
    }

    /**
     * Send a single email through Postmark API
     *
     * @param  EmailData  $data  Email data containing from, to, subject, and content
     *
     * @throws BadMethodCallException When server token is not set
     * @throws InvalidArgumentException When authentication is not set
     * @throws Throwable When API response is not successful
     */
    public function send(EmailData $data): EmailResponse
    {
        try {
            $this->validateAuthentication();

            $response = $this->getClientWithServerToken()->post('/email', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to send email: %s', $response->body())
                );
            }

            return EmailResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to send email', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Send a batch of emails through Postmark API
     *
     * @param  Collection<EmailData>  $data  Collection of email data
     *
     * @throws BadMethodCallException When server token is not set
     * @throws InvalidArgumentException When authentication is not set
     * @throws Throwable When API response is not successful
     */
    public function sendBatch(Collection $data): EmailBatchResponse
    {
        try {
            $this->validateAuthentication();

            $response = $this->getClientWithServerToken()->post('/email/batch', [
                'Messages' => $data->map(fn (EmailData $email) => $email->toArray())->toArray(),
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to send batch emails: %s', $response->body())
                );
            }

            return EmailBatchResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to send batch emails', [
                'count' => $data->count(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Send an email with template through Postmark API
     *
     * @param  int  $templateId  Template identifier
     * @param  EmailData  $data  Email data
     *
     * @throws BadMethodCallException When server token is not set
     * @throws InvalidArgumentException When authentication is not set
     * @throws Throwable When API response is not successful
     */
    public function sendWithTemplate(int $templateId, EmailData $data): EmailResponse
    {
        try {
            $this->validateAuthentication();

            $payload = array_merge(
                $data->toArray(),
                ['TemplateId' => $templateId]
            );

            $response = $this->getClientWithServerToken()->post('/email/withTemplate', $payload);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to send email with template: %s', $response->body())
                );
            }

            return EmailResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to send email with template', [
                'templateId' => $templateId,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Send a batch of emails with template through Postmark API
     *
     * @param  int  $templateId  Template identifier
     * @param  Collection<EmailData>  $data  Collection of email data
     *
     * @throws BadMethodCallException When server token is not set
     * @throws InvalidArgumentException When authentication is not set
     * @throws Throwable When API response is not successful
     */
    public function sendBatchWithTemplate(int $templateId, Collection $data): EmailBatchResponse
    {
        try {
            $this->validateAuthentication();

            $response = $this->getClientWithServerToken()->post('/email/batchWithTemplates', [
                'Messages' => $data->map(function (EmailData $email) use ($templateId) {
                    return array_merge(
                        $email->toArray(),
                        ['TemplateId' => $templateId]
                    );
                })->toArray(),
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to send batch emails with template: %s', $response->body())
                );
            }

            return EmailBatchResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to send batch emails with template', [
                'templateId' => $templateId,
                'count' => $data->count(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Handle exceptions from API calls
     *
     * @param  Throwable  $e  The exception to handle
     * @return Throwable The processed exception
     */
    protected function handleException(Throwable $e): Throwable
    {
        if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException || $e instanceof BadMethodCallException) {
            return $e;
        }

        return new RuntimeException(
            'An error occurred while processing the request',
            0,
            $e
        );
    }
}
