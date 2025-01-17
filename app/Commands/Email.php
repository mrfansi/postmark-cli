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

namespace App\Commands;

use App\Contracts\PostmarkFactoryInterface;
use App\Data\EmailData;
use App\Data\EmailResponse;
use App\Data\SenderListResponse;
use App\Data\ServerResponse;
use App\Generator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\form;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\textarea;

/**
 * Email Command
 *
 * This command provides functionality to send emails through Postmark API
 * via command line interface.
 */
class Email extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email
                         {action=send : Action to perform (send)}
                         {--from= : Email sender address}
                         {--to= : Email recipient address}
                         {--subject= : Email subject}
                         {--text-body= : Email text body}
                         {--html-body= : Email HTML body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails through Postmark API';

    /**
     * Postmark factory instance
     */
    private PostmarkFactoryInterface $postmark;

    /**
     * Generator instance for output formatting
     */
    private Generator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(PostmarkFactoryInterface $postmark, Generator $generator)
    {
        parent::__construct();

        $this->postmark = $postmark;
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $action = $this->argument('action');
            $this->dispatchAction($action);
        } catch (InvalidArgumentException $e) {
            $this->error('Invalid input: '.$e->getMessage());
        } catch (RuntimeException $e) {
            $this->error('API error: '.$e->getMessage());
        } catch (Throwable $e) {
            $this->error('Unexpected error: '.$e->getMessage());
            if (app()->environment('local')) {
                $this->error($e->getTraceAsString());
            }
        }
    }

    /**
     * Dispatch the appropriate action based on command input
     */
    private function dispatchAction(string $action): void
    {
        match ($action) {
            'send' => $this->sendEmail(),
            default => throw new InvalidArgumentException("Action '$action' not supported")
        };
    }

    /**
     * Handle the email sending process
     */
    private function sendEmail(): void
    {
        $data = $this->collectEmailData();

        $response = spin(
            fn () => $this->postmark->email()
                ->withServer($this->getServerID())
                ->send($data),
            'Sending email...'
        );

        $this->displayEmailResponse($response);
    }

    /**
     * Collect email data from command options or prompt user for input
     */
    private function collectEmailData(): EmailData
    {
        if ($this->allOptionsProvided()) {
            return $this->createEmailDataFromOptions();
        }

        /** @var Collection<SenderListResponse> $senders */
        $senders = spin(
            fn () => $this->postmark->sender()->all(),
            'Fetching senders...'
        );

        $formData = form()
            ->search(
                label: 'From',
                options: function (string $value) use ($senders) {
                    if (strlen($value) > 0) {
                        return $senders
                            ->filter(
                                fn (SenderListResponse $sender) => str_contains(
                                    strtolower($sender->emailAddress),
                                    strtolower($value)
                                )
                            )
                            ->pluck('emailAddress')->toArray();
                    }

                    return [];
                },
                placeholder: 'john.doe@example.com',
                name: 'from'
            )
            ->text(
                label: 'To',
                placeholder: 'reply@example.com',
                required: true,
                name: 'to'
            )
            ->text(
                label: 'Subject',
                placeholder: 'Hello, World!',
                required: true,
                name: 'subject'
            )
            ->confirm('Want to use default content?', name: 'useDefaultContent')
            ->addIf(fn ($response) => ! $response['useDefaultContent'], function () {
                return textarea(
                    label: 'Text Body',
                    placeholder: 'Hello, World!',
                    required: true,
                    hint: 'Leave blank to use HTML body',
                );
            }, 'textBody')
            ->addIf(fn ($response) => ! $response['useDefaultContent'], function () {
                return textarea(
                    label: 'HTML Body',
                    placeholder: '<h1>Hello, World!</h1>',
                    required: true,
                    hint: 'Leave blank to use Text body',
                );
            }, 'htmlBody')
            ->submit();

        return new EmailData(
            from: $formData['from'],
            to: $formData['to'],
            subject: $formData['subject'],
            htmlBody: $formData['useDefaultContent'] ? $this->generator->generateTestEmailContent()['html_body'] : $formData['htmlBody'],
            textBody: $formData['useDefaultContent'] ? $this->generator->generateTestEmailContent()['plain_text'] : $formData['textBody'],
        );
    }

    /**
     * Check if all required options are provided
     */
    private function allOptionsProvided(): bool
    {
        return $this->option('from')
            && $this->option('to')
            && $this->option('subject')
            && ($this->option('text-body') || $this->option('html-body'));
    }

    /**
     * Create EmailData instance from command options
     */
    private function createEmailDataFromOptions(): EmailData
    {
        return new EmailData(
            from: $this->option('from'),
            to: $this->option('to'),
            subject: $this->option('subject'),
            htmlBody: $this->option('html-body'),
            textBody: $this->option('text-body')
        );
    }

    /**
     * Get server ID from option or search
     *
     * @throws RuntimeException When server ID cannot be found
     */
    private function getServerID(): int
    {
        /** @var Collection<ServerResponse> $servers */
        $servers = spin(
            fn () => $this->postmark->server()->all(),
            'Fetching servers...'
        );

        $id = search(
            'Search server by name',
            fn (string $value) => strlen($value) > 0
                ? $servers->filter(
                    fn (ServerResponse $server) => str_contains(
                        strtolower($server->name),
                        strtolower($value)
                    )
                )->pluck('name', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Server not found');
        }

        return (int) $id;
    }

    /**
     * Display the email sending response
     */
    private function displayEmailResponse(EmailResponse $response): void
    {
        $this->info('Email sent successfully!');
        $this->generator->getDetailTable(collect([
            'ID' => $response->messageId,
            'To' => $response->to,
            'Submitted At' => $response->submittedAt,
            'Error Code' => $response->errorCode ?? 'None',
            'Message' => $response->message ?? 'Success',
        ]));
    }
}
