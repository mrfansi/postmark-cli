<?php

namespace App\Commands;

use App\Contracts\PostmarkFactoryInterface;
use App\Generator;
use App\Postmark\DTOs\ServerData;
use App\Postmark\DTOs\ServerResponse;
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
 * Server Command
 *
 * This command provides functionality to manage Postmark servers
 * through the command line interface.
 */
class Server extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : Server ID for edit/delete actions}
                         {--name= : Server name for new/edit actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Postmark servers for your account';

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

        $this->{$action.'Server'}();
    }

    /**
     * List all servers
     */
    private function listServer(): void
    {

        /** @var Collection<ServerResponse> $servers */
        $servers = spin(
            fn () => $this->postmark->server()->all(),
            'Fetching servers...'
        );

        // Mask sensitive data
        $servers = $servers->map(function (ServerResponse $server) {
            return [
                'ID' => $server->id,
                'Name' => $server->name,
                'API Tokens' => ['*****'],
                'Color' => $server->color,
                'SMTP API' => $server->smtpApiActivated ? 'Enabled' : 'Disabled',
                'Raw Email' => $server->rawEmailEnabled ? 'Enabled' : 'Disabled',
                'Track Opens' => $server->trackOpens ? 'Enabled' : 'Disabled',
                'Track Links' => $server->trackLinks,
                'Spam Threshold' => $server->inboundSpamThreshold,
                'Server Link' => $server->serverLink,
            ];
        });

        $table = $this->generator->getTable($servers);
        $this->table(...$table);
    }

    /**
     * Create a new server
     */
    private function newServer(): void
    {
        $data = $this->getServerData();

        /** @var ServerResponse $server */
        $server = spin(
            fn () => $this->postmark->server()->create($data),
            'Creating server...'
        );

        $this->info('Server created successfully!');
        $this->displayServerDetails($server);
    }

    /**
     * Show server details
     */
    private function showServer(): void
    {
        $id = $this->getServerID();

        /** @var ServerResponse $server */
        $server = spin(
            fn () => $this->postmark->server()->find($id),
            'Fetching server details...'
        );

        $this->displayServerDetails($server);
    }

    /**
     * Edit server details
     */
    private function editServer(): void
    {
        $id = $this->getServerID();
        if (! $id) {
            throw new InvalidArgumentException('Server ID is required for edit action');
        }

        /** @var ServerResponse $server */
        $server = spin(
            fn () => $this->postmark->server()->find($id),
            'Fetching server details...'
        );

        $data = $this->getServerData($server);

        /** @var ServerResponse $updatedServer */
        $updatedServer = spin(
            fn () => $this->postmark->server()->update($id, $data),
            'Updating server...'
        );

        $this->info('Server updated successfully!');
        $this->displayServerDetails($updatedServer);
    }

    /**
     * Delete a server
     */
    private function deleteServer(): void
    {
        $id = $this->getServerID();
        if (! $id) {
            throw new InvalidArgumentException('Server ID is required for delete action');
        }

        /** @var ServerResponse $server */
        $server = spin(
            fn () => $this->postmark->server()->find($id),
            'Fetching server details...'
        );

        $this->displayServerDetails($server);

        if (! confirm(
            "Want to delete the server ID $id?",
            hint: 'Deleting a server cannot be undone! Any emails being sent through this server will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->postmark->server()->delete($id),
            'Deleting server...'
        );

        if ($success) {
            $this->info('Server deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete server');
        }
    }

    /**
     * Get server data from form input
     */
    private function getServerData(?ServerResponse $current = null): ServerData
    {
        $formData = form()
            ->text(
                label: 'Server Name',
                placeholder: 'Staging, Production, My Server',
                default: $current?->name ?? '',
                required: true,
                hint: 'Name of server',
                name: 'name'
            )
            ->select(
                label: 'Server Color',
                options: [
                    'blue' => 'Blue',
                    'red' => 'Red',
                    'green' => 'Green',
                    'orange' => 'Orange',
                    'purple' => 'Purple',
                    'yellow' => 'Yellow',
                    'pink' => 'Pink',
                    'black' => 'Black',
                    'white' => 'White',
                ],
                default: $current?->color ?? 'blue',
                hint: 'Color for quick identification',
                name: 'color'
            )
            ->confirm(
                label: 'Activate SMTP API',
                default: $current?->smtpApiActivated ?? false,
                hint: 'Enable SMTP for this server',
                name: 'smtpApiActivated'
            )
            ->confirm(
                label: 'Enable Raw Email',
                default: $current?->rawEmailEnabled ?? true,
                hint: 'Include raw email content in webhooks',
                name: 'rawEmailEnabled'
            )
            ->text(
                label: 'Inbound Hook URL',
                placeholder: 'https://your-domain.com/webhook/inbound',
                default: $current?->inboundHookUrl ?? '',
                hint: 'Webhook URL for inbound events',
                name: 'inboundHookUrl'
            )
            ->confirm(
                label: 'Post First Open Only',
                default: $current?->postFirstOpenOnly ?? false,
                hint: 'Only trigger webhook for first open',
                name: 'postFirstOpenOnly'
            )
            ->text(
                label: 'Inbound Domain',
                placeholder: 'inbound.your-domain.com',
                default: $current?->inboundDomain ?? '',
                hint: 'Domain for inbound email',
                name: 'inboundDomain'
            )
            ->text(
                label: 'Inbound Spam Threshold',
                placeholder: '0-30',
                default: (string) ($current?->inboundSpamThreshold ?? 0),
                hint: 'Maximum spam score (0-30)',
                name: 'inboundSpamThreshold'
            )
            ->confirm(
                label: 'Track Opens',
                default: $current?->trackOpens ?? true,
                hint: 'Enable open tracking',
                name: 'trackOpens'
            )
            ->select(
                label: 'Track Links',
                options: [
                    'None' => 'None',
                    'HtmlAndText' => 'HTML and Text',
                    'HtmlOnly' => 'HTML Only',
                    'TextOnly' => 'Text Only',
                ],
                default: $current?->trackLinks ?? 'None',
                hint: 'Link tracking options',
                name: 'trackLinks'
            )
            ->submit();

        return new ServerData(
            name: $formData['name'],
            color: $formData['color'],
            smtpApiActivated: $formData['smtpApiActivated'],
            rawEmailEnabled: $formData['rawEmailEnabled'],
            postFirstOpenOnly: $formData['postFirstOpenOnly'],
            trackOpens: $formData['trackOpens'],
            trackLinks: $formData['trackLinks'],
            inboundSpamThreshold: (int) $formData['inboundSpamThreshold'],
            inboundHookUrl: $formData['inboundHookUrl'] ?: null,
            inboundDomain: $formData['inboundDomain'] ?: null
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

        if ($id = $this->option('id')) {
            $server = $servers->firstWhere('id', (int) $id);
            if ($server) {
                return $server->id;
            }
        }

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
     * Display server details in a table
     */
    private function displayServerDetails(ServerResponse $server): void
    {
        $detail = $this->generator->getDetailTable(collect([
            'ID' => $server->id,
            'Name' => $server->name,
            'API Tokens' => ['*****'],
            'Color' => $server->color,
            'SMTP API' => $server->smtpApiActivated ? 'Enabled' : 'Disabled',
            'Raw Email' => $server->rawEmailEnabled ? 'Enabled' : 'Disabled',
            'Inbound Hook URL' => $server->inboundHookUrl == '' ? 'Not set' : $server->inboundHookUrl,
            'Post First Open Only' => $server->postFirstOpenOnly ? 'Yes' : 'No',
            'Track Opens' => $server->trackOpens ? 'Enabled' : 'Disabled',
            'Track Links' => $server->trackLinks,
            'Inbound Domain' => $server->inboundDomain === '' ? 'Not set' : $server->inboundDomain,
            'Spam Threshold' => $server->inboundSpamThreshold,
            'Server Link' => $server->serverLink,
        ]));

        $this->table(...$detail);
    }
}
