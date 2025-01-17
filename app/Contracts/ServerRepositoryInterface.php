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

use App\Data\ServerData;
use App\Data\ServerResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Server Repository
 *
 * This interface defines the contract for interacting with Postmark Servers.
 */
interface ServerRepositoryInterface
{
    /**
     * Get server token by ID
     *
     * @param  int  $id  Server ID
     * @return string Server token
     */
    public function getToken(int $id): string;

    /**
     * Get all servers with pagination
     *
     * @param  int  $count  Items per page
     * @param  int  $offset  Pagination offset
     * @param  string  $name  Filter by name
     * @return Collection<ServerResponse>
     */
    public function all(int $count = 10, int $offset = 0, string $name = ''): Collection;

    /**
     * Find server by ID
     *
     * @param  int  $id  Server ID
     */
    public function find(int $id): ServerResponse;

    /**
     * Create new server
     *
     * @param  ServerData  $data  Server data
     */
    public function create(ServerData $data): ServerResponse;

    /**
     * Update server
     *
     * @param  int  $id  Server ID
     * @param  ServerData  $data  Server data
     */
    public function update(int $id, ServerData $data): ServerResponse;

    /**
     * Delete server
     *
     * @param  int  $id  Server ID
     */
    public function delete(int $id): bool;
}
