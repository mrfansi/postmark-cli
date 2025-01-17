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

use App\Contracts\SenderRepositoryInterface;
use App\Data\SenderData;
use App\Data\SenderListResponse;
use App\Data\SenderResponse;
use App\Services\SenderCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Sender API Client
 *
 * This class handles all sender-related operations with the Postmark API.
 * It implements the SenderRepositoryInterface for standardized sender operations.
 */
class Sender implements SenderRepositoryInterface
{
    /**
     * Maximum number of senders that can be retrieved
     */
    private const MAX_COUNT = 500;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for sender data
     */
    private SenderCacheService $cacheService;

    /**
     * Constructor for Sender API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  SenderCacheService  $cacheService  Cache service for sender data
     */
    public function __construct(PendingRequest $client, SenderCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieve a list of senders from Postmark API
     *
     * @param  int  $count  Number of senders to retrieve (1-500)
     * @param  int  $offset  Pagination offset (>= 0)
     * @return Collection<SenderResponse> Collection of sender objects
     *
     * @throws Throwable When API response is not successful
     */
    public function all(int $count = 100, int $offset = 0): Collection
    {
        $this->validatePaginationParams($count, $offset);

        $cacheKey = $this->cacheService->generateKey($count, $offset);

        try {
            if ($this->cacheService->has($cacheKey)) {
                return $this->cacheService->get($cacheKey);
            }

            $response = $this->client->get('/senders', [
                'count' => $count,
                'offset' => $offset,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve senders: %s', $response->body())
                );
            }

            $senders = collect($response->json('SenderSignatures'))
                ->map(fn (array $sender) => SenderListResponse::fromArray($sender));

            $this->cacheService->put($cacheKey, $senders);

            return $senders;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve senders', [
                'count' => $count,
                'offset' => $offset,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve sender details by ID
     *
     * @param  int  $id  Sender ID to retrieve
     * @return SenderResponse Sender details
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(int $id): SenderResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/senders/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve sender details: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to find sender', [
                'sender_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Create new sender
     *
     * @param  SenderData  $data  Sender data
     * @return SenderResponse Created sender details
     *
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function create(SenderData $data): SenderResponse
    {
        try {
            $response = $this->client->post('/senders', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create sender: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to create sender', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update sender
     *
     * @param  int  $id  Sender ID to update
     * @param  SenderData  $data  Sender data
     * @return SenderResponse Updated sender details
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(int $id, SenderData $data): SenderResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("/senders/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update sender: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update sender', [
                'sender_id' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete sender
     *
     * @param  int  $id  Sender ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(int $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/senders/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete sender', [
                'sender_id' => $id,
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
     * Validate sender ID
     *
     * @param  int  $id  Sender ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Sender ID must be greater than 0');
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
