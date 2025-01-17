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

namespace App\Postmark\Concerns;

use App\Data\ServerResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Trait for handling Postmark Server Token authentication
 *
 * This trait provides functionality for managing server token authentication
 * in Postmark API requests. It supports both direct token usage and server ID lookup.
 */
trait HasServerToken
{
    use RequiresServerToken;

    /**
     * Server ID to authenticate with
     */
    private ?int $serverId = null;

    /**
     * Server token for authentication
     */
    private ?string $serverToken = null;

    /**
     * Set server identifier for authentication
     *
     * @param  int  $serverId  Server identifier from Postmark
     *
     * @throws InvalidArgumentException When server token is already set
     */
    public function withServer(int $serverId): self
    {
        if ($this->serverToken !== null) {
            throw new InvalidArgumentException(
                'Cannot use withServer() when server token is already set. Use either withServer() or withServerToken().'
            );
        }

        $this->serverId = $serverId;
        $this->serverToken = null;
        $this->markServerTokenSet();

        return $this;
    }

    /**
     * Set server token for authentication
     *
     * @param  string  $serverToken  Server token from Postmark
     *
     * @throws InvalidArgumentException When server ID is already set
     */
    public function withServerToken(string $serverToken): self
    {
        if ($this->serverId !== null) {
            throw new InvalidArgumentException(
                'Cannot use withServerToken() when server ID is already set. Use either withServer() or withServerToken().'
            );
        }

        $this->serverToken = $serverToken;
        $this->serverId = null;
        $this->markServerTokenSet();

        return $this;
    }

    /**
     * Get HTTP client with server token header
     */
    protected function getClientWithServerToken(): PendingRequest
    {
        if (! isset($this->client)) {
            throw new RuntimeException('HTTP client is not initialized');
        }

        return $this->client->withHeaders([
            'X-Postmark-Server-Token' => $this->serverToken,
        ]);
    }

    /**
     * Validates authentication and retrieves server token if needed
     *
     * @throws InvalidArgumentException When neither server ID nor token is set
     * @throws Throwable When server details cannot be retrieved
     */
    private function validateAuthentication(): void
    {
        $this->ensureServerTokenIsSet();

        if ($this->serverToken !== null) {
            return;
        }

        if ($this->serverId === null) {
            throw new InvalidArgumentException(
                'Authentication required. Use either withServer() or withServerToken() method.'
            );
        }

        try {
            if (! isset($this->client)) {
                throw new RuntimeException('HTTP client is not initialized');
            }

            $response = $this->client->get("/servers/$this->serverId");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve server details: %s', $response->body())
                );
            }

            $server = ServerResponse::fromArray($response->json());

            if (empty($server->apiTokens)) {
                throw new RuntimeException('No API tokens found for server');
            }

            $this->serverToken = $server->apiTokens[0];
        } catch (Throwable $e) {
            Log::error('Failed to find server', [
                'server_id' => $this->serverId,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Abstract method that must be implemented by classes using this trait
     *
     * @param  Throwable  $e  The exception to handle
     */
    abstract protected function handleException(Throwable $e): Throwable;
}
