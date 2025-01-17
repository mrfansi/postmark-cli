<?php

namespace App\Contracts;

use App\Postmark\DTOs\SenderData;
use App\Postmark\DTOs\SenderResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Sender Repository
 *
 * This interface defines the contract for interacting with Postmark Senders.
 *
 * @package App\Contracts
 */
interface SenderRepositoryInterface
{
    /**
     * Get all servers with pagination
     *
     * @param int $count Items per page
     * @param int $offset Pagination offset
     * @return Collection<SenderResponse>
     */
    public function all(int $count = 10, int $offset = 0): Collection;

    /**
     * Find server by ID
     *
     * @param int $id Sender ID
     * @return SenderResponse
     */
    public function find(int $id): SenderResponse;

    /**
     * Create new server
     *
     * @param SenderData $data Sender data
     * @return SenderResponse
     */
    public function create(SenderData $data): SenderResponse;

    /**
     * Update server
     *
     * @param int $id Sender ID
     * @param SenderData $data Sender data
     * @return SenderResponse
     */
    public function update(int $id, SenderData $data): SenderResponse;

    /**
     * Delete server
     *
     * @param int $id Sender ID
     * @return bool
     */
    public function delete(int $id): bool;
}
