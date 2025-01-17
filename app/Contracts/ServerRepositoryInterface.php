<?php

namespace App\Contracts;

use App\Postmark\DTOs\ServerData;
use App\Postmark\DTOs\ServerResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Server Repository
 *
 * This interface defines the contract for interacting with Postmark Servers.
 *
 * @package App\Contracts
 */
interface ServerRepositoryInterface
{
    /**
     * Get server token by ID
     *
     * @param int $id Server ID
     * @return string Server token
     */
    public function getToken(int $id): string;

    /**
     * Get all servers with pagination
     *
     * @param int $count Items per page
     * @param int $offset Pagination offset
     * @param string $name Filter by name
     * @return Collection<ServerResponse>
     */
    public function all(int $count = 10, int $offset = 0, string $name = ''): Collection;

    /**
     * Find server by ID
     *
     * @param int $id Server ID
     * @return ServerResponse
     */
    public function find(int $id): ServerResponse;

    /**
     * Create new server
     *
     * @param ServerData $data Server data
     * @return ServerResponse
     */
    public function create(ServerData $data): ServerResponse;

    /**
     * Update server
     *
     * @param int $id Server ID
     * @param ServerData $data Server data
     * @return ServerResponse
     */
    public function update(int $id, ServerData $data): ServerResponse;

    /**
     * Delete server
     *
     * @param int $id Server ID
     * @return bool
     */
    public function delete(int $id): bool;
}
