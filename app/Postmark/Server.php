<?php

namespace App\Postmark;

use App\Contracts\ServerRepositoryInterface;
use App\Postmark\DTOs\ServerData;
use App\Postmark\DTOs\ServerResponse;
use App\Services\ServerCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Server API Client
 *
 * This class handles all server-related operations with the Postmark API.
 * It implements the ServerRepositoryInterface for standardized server operations.
 */
class Server implements ServerRepositoryInterface
{
    /**
     * Maximum number of servers that can be retrieved
     */
    private const MAX_COUNT = 500;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for server data
     */
    private ServerCacheService $cacheService;

    /**
     * Constructor for Server API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  ServerCacheService  $cacheService  Cache service for server data
     */
    public function __construct(PendingRequest $client, ServerCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Get server token by ID
     *
     * @param  int  $id  Server ID
     * @return string Server token
     *
     * @throws RuntimeException When failed to get server token
     */
    public function getToken(int $id): string
    {
        try {
            $server = $this->find($id);

            if (empty($server->apiTokens)) {
                throw new RuntimeException('No API tokens found for server');
            }

            return $server->apiTokens[0];
        } catch (Throwable $e) {
            Log::error('Failed to get server token', [
                'server_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to get server token: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve a list of servers from Postmark API
     *
     * @param  int  $count  Number of servers to retrieve (1-500)
     * @param  int  $offset  Pagination offset (>= 0)
     * @param  string  $name  Filter servers by name (optional)
     * @return Collection<ServerResponse> Collection of server objects
     *
     * @throws InvalidArgumentException When input parameters are invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException|Throwable When API response is not successful
     */
    public function all(int $count = 10, int $offset = 0, string $name = ''): Collection
    {
        $this->validatePaginationParams($count, $offset);

        $cacheKey = $this->cacheService->generateKey($count, $offset, $name);

        try {
            if ($name === '' && $this->cacheService->has($cacheKey)) {
                return $this->cacheService->get($cacheKey);
            }

            $response = $this->client->get('/servers', [
                'count' => $count,
                'offset' => $offset,
                'name' => $name,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve servers: %s', $response->body())
                );
            }

            $servers = collect($response->json('Servers'))
                ->map(fn (array $server) => ServerResponse::fromArray($server));

            if ($name === '') {
                $this->cacheService->put($cacheKey, $servers);
            }

            return $servers;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve servers', [
                'count' => $count,
                'offset' => $offset,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve server details by ID
     *
     * @param int $id Server ID to retrieve
     * @return ServerResponse Server details
     *
     * @throws InvalidArgumentException When server ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(int $id): ServerResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/servers/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve server details: %s', $response->body())
                );
            }

            return ServerResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to find server', [
                'server_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Create new server
     *
     * @param ServerData $data Server data
     * @return ServerResponse Created server details
     *
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function create(ServerData $data): ServerResponse
    {
        try {
            $response = $this->client->post('/servers', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create server: %s', $response->body())
                );
            }

            return ServerResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to create server', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update server
     *
     * @param int $id Server ID to update
     * @param ServerData $data Server data
     * @return ServerResponse Updated server details
     *
     * @throws InvalidArgumentException When server ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(int $id, ServerData $data): ServerResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("/servers/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update server: %s', $response->body())
                );
            }

            return ServerResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update server', [
                'server_id' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete server
     *
     * @param int $id Server ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When server ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(int $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/servers/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete server', [
                'server_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Validate pagination parameters
     *
     * @param  int  $count  Number of items per page
     * @param  int  $offset  Pagination offset
     *
     * @throws InvalidArgumentException When parameters are invalid
     */
    private function validatePaginationParams(int $count, int $offset): void
    {
        if ($count < 1 || $count > self::MAX_COUNT) {
            throw new InvalidArgumentException(
                sprintf('Count must be between 1 and %d, given: %d', self::MAX_COUNT, $count)
            );
        }

        if ($offset < 0) {
            throw new InvalidArgumentException(
                sprintf('Offset cannot be negative, given: %d', $offset)
            );
        }
    }

    /**
     * Validate server ID
     *
     * @param  int  $id  Server ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Server ID must be greater than 0');
        }
    }

    /**
     * Handle exception and convert to appropriate type
     *
     * @param  Throwable  $e  Exception to handle
     * @return Throwable Converted exception
     */
    private function handleException(Throwable $e): Throwable
    {
        if ($e instanceof ConnectionException) {
            return $e;
        }

        if ($e instanceof InvalidArgumentException) {
            return $e;
        }

        return new RuntimeException($e->getMessage(), 0, $e);
    }
}
