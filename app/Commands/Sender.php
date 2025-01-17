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
use App\Data\SenderData;
use App\Data\SenderListResponse;
use App\Data\SenderResponse;
use App\Generator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;

/**
 * Sender Command
 *
 * This command provides functionality to manage Postmark senders
 * through the command line interface.
 */
class Sender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sender
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : Sender ID for edit/delete actions}
                         {--name= : Sender name for new/edit actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Postmark sender-signatures for your account';

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
     * Dispatch action based on command argument
     *
     * @throws InvalidArgumentException When action is invalid
     */
    private function dispatchAction(string $action): void
    {
        if (! in_array($action, ['list', 'show', 'new', 'edit', 'delete'])) {
            throw new InvalidArgumentException(
                'Invalid action. Use: list, show, new, edit, or delete'
            );
        }

        $this->{$action.'Sender'}();
    }

    /**
     * List all senders
     */
    private function listSender(): void
    {

        /** @var Collection<SenderListResponse> $senders */
        $senders = spin(
            fn () => $this->postmark->sender()->all(),
            'Fetching senders...'
        );

        // Mask sensitive data
        $senders = $senders->map(function (SenderListResponse $sender) {
            return [
                'ID' => $sender->id,
                'Domain' => $sender->domain,
                'Email Address' => $sender->emailAddress,
                'Reply To Email Address' => $sender->replyToEmailAddress,
                'Name' => $sender->name,
                'Confirmed' => $sender->confirmed ? 'Enabled' : 'Disabled',
            ];
        });

        $table = $this->generator->getTable($senders);
        $this->table(...$table);
    }

    /**
     * Create a new sender
     */
    private function newSender(): void
    {
        $data = $this->getSenderData();

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->postmark->sender()->create($data),
            'Creating sender...'
        );

        $this->info('Sender created successfully!');
        $this->displaySenderDetails($sender);
    }

    /**
     * Show sender details
     */
    private function showSender(): void
    {
        $id = $this->getSenderID();

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->postmark->sender()->find($id),
            'Fetching sender details...'
        );

        $this->displaySenderDetails($sender);

    }

    /**
     * Edit sender details
     */
    private function editSender(): void
    {
        $id = $this->getSenderID();
        if (! $id) {
            throw new InvalidArgumentException('Sender ID is required for edit action');
        }

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->postmark->sender()->find($id),
            'Fetching sender details...'
        );

        $data = $this->getSenderData($sender);

        /** @var SenderResponse $updatedSender */
        $updatedSender = spin(
            fn () => $this->postmark->sender()->update($id, $data),
            'Updating sender...'
        );

        $this->info('Sender updated successfully!');
        $this->displaySenderDetails($updatedSender);
    }

    /**
     * Delete a sender
     */
    private function deleteSender(): void
    {
        $id = $this->getSenderID();
        if (! $id) {
            throw new InvalidArgumentException('Sender ID is required for delete action');
        }

        /** @var SenderResponse $sender */
        $sender = spin(
            fn () => $this->postmark->sender()->find($id),
            'Fetching sender details...'
        );

        $this->displaySenderDetails($sender);

        if (! confirm(
            "Want to delete the sender ID $id?",
            hint: 'Deleting a sender cannot be undone! Any emails being sent through this sender will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->postmark->sender()->delete($id),
            'Deleting sender...'
        );

        if ($success) {
            $this->info('Sender deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete sender');
        }
    }

    /**
     * Get sender data from form input
     */
    private function getSenderData(?SenderResponse $current = null): SenderData
    {
        $formData = form()
            ->text(
                label: 'From Email',
                placeholder: 'john.doe@example.com',
                default: $current?->emailAddress ?? '',
                required: true,
                hint: 'From email associated with sender signature.',
                name: 'fromEmail'
            )
            ->text(
                label: 'Name',
                placeholder: 'John Doe',
                default: $current?->name ?? '',
                required: true,
                hint: 'From name associated with sender signature.',
                name: 'name'
            )
            ->text(
                label: 'Reply To Email',
                placeholder: 'reply@example.com',
                default: $current?->replyToEmailAddress ?? '',
                hint: 'Override for reply-to address.',
                name: 'replyToEmail'
            )
            ->text(
                label: 'Return Path Domain',
                placeholder: 'pm-bounces.example.com',
                default: $current?->returnPathDomain ?? '',
                hint: 'A custom value for the Return-Path domain. It is an optional field, but it must be a subdomain of your From Email domain and must have a CNAME record that points to pm.mtasv.net',
                name: 'returnPathDomain'
            )

            ->textarea(
                label: 'Confirmation Personal Note',
                placeholder: 'This is a note visible to the recipient to provide context of what Postmark is.',
                default: $current?->confirmationPersonalNote ?? '',
                hint: 'Optional. A way to provide a note to the recipient of the confirmation email to have context of what Postmark is. Max length of 400 characters.',
                name: 'confirmationPersonalNote'
            )

            ->submit();

        return new SenderData(
            $formData['fromEmail'],
            $formData['name'],
            $formData['replyToEmail'],
            $formData['returnPathDomain'],
            $formData['confirmationPersonalNote'],
        );
    }

    /**
     * Get sender ID from option or search
     *
     * @throws RuntimeException When sender ID cannot be found
     */
    private function getSenderID(): int
    {
        /** @var Collection<SenderListResponse> $senders */
        $senders = spin(
            fn () => $this->postmark->sender()->all(),
            'Fetching senders...'
        );

        if ($id = $this->option('id')) {
            $sender = $senders->firstWhere('id', (int) $id);
            if ($sender) {
                return $sender->id;
            }
        }

        $id = search(
            'Search sender by email',
            fn (string $value) => strlen($value) > 0
                ? $senders->filter(
                    fn (SenderListResponse $sender) => str_contains(
                        strtolower($sender->emailAddress),
                        strtolower($value)
                    )
                )->pluck('emailAddress', 'id')->toArray()
                : []
        );

        if (! $id) {
            throw new RuntimeException('Sender not found');
        }

        return (int) $id;
    }

    /**
     * Display sender details in a table
     */
    private function displaySenderDetails(SenderResponse $sender): void
    {
        $detail = $this->generator->getDetailTable(collect([
            'ID' => $sender->id,
            'Domain' => $sender->domain,
            'Email Address' => $sender->emailAddress,
            'Reply To Email Address' => $sender->replyToEmailAddress == '' ? 'Not set' : $sender->replyToEmailAddress,
            'Name' => $sender->name,
            'Confirmed' => $sender->confirmed ? 'Yes' : 'No',
            'SPF Verified' => $sender->spfVerified ? 'Yes' : 'No',
            'SPF Host' => $sender->spfHost,
            'SPF Text Value' => $sender->spfTextValue,
            'DKIM Verified' => $sender->dkimVerified ? 'Yes' : 'No',
            'Weak DKIM' => $sender->weakDKIM ? 'Yes' : 'No',
            'DKIM Host' => $sender->dkimHost == '' ? 'Not set' : $sender->dkimHost,
            'DKIM Text Value' => '[****]',
            'DKIM Pending Host' => $sender->dkimPendingHost == '' ? 'Not set' : $sender->dkimPendingHost,
            'DKIM Pending Text Value' => $sender->dkimPendingTextValue == '' ? 'Not set' : $sender->dkimPendingTextValue,
            'DKIM Revoked Host' => $sender->dkimRevokedHost === '' ? 'Not set' : $sender->dkimRevokedHost,
            'DKIM Revoked Text Value' => '[****]',
            'Safe To Remove Revoked Key From DNS' => $sender->safeToRemoveRevokedKeyFromDNS ? 'Yes' : 'No',
            'DKIM Update Status' => $sender->dkimUpdateStatus,
            'Return Path Domain' => $sender->returnPathDomain,
            'Return Path Domain Verified' => $sender->returnPathDomainVerified ? 'Yes' : 'No',
            'Return Path Domain CNAME Value' => $sender->returnPathDomainCNAMEValue,
            'Confirmation Personal Note' => $sender->confirmationPersonalNote == '' ? 'Not set' : $sender->confirmationPersonalNote,
        ]));

        $this->table(...$detail);
    }
}
