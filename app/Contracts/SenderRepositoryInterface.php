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

use App\Data\SenderData;
use App\Data\SenderResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Sender Repository
 *
 * This interface defines the contract for interacting with Postmark Senders.
 */
interface SenderRepositoryInterface
{
    /**
     * Get all servers with pagination
     *
     * @param  int  $count  Items per page
     * @param  int  $offset  Pagination offset
     * @return Collection<SenderResponse>
     */
    public function all(int $count = 10, int $offset = 0): Collection;

    /**
     * Find server by ID
     *
     * @param  int  $id  Sender ID
     */
    public function find(int $id): SenderResponse;

    /**
     * Create new server
     *
     * @param  SenderData  $data  Sender data
     */
    public function create(SenderData $data): SenderResponse;

    /**
     * Update server
     *
     * @param  int  $id  Sender ID
     * @param  SenderData  $data  Sender data
     */
    public function update(int $id, SenderData $data): SenderResponse;

    /**
     * Delete server
     *
     * @param  int  $id  Sender ID
     */
    public function delete(int $id): bool;
}
