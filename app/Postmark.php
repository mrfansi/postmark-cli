<?php

namespace App;

use App\Contracts\HttpClientFactory;
use App\Contracts\PostmarkFactoryInterface;
use App\Postmark\Email;
use App\Postmark\Server;
use App\Postmark\Sender;
use App\Services\ServerCacheService;
use App\Services\SenderCacheService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * Postmark API Client Factory
 *
 * This class serves as a factory for creating Postmark API clients.
 * It provides centralized configuration and client instantiation.
 *
 * @package App
 */
class Postmark implements HttpClientFactory, PostmarkFactoryInterface
{
    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache implementation for storing API responses
     */
    private CacheInterface $cache;

    /**
     * Server cache service instance
     */
    private ?ServerCacheService $serverCache = null;

    /**
     * Sender cache service instance
     */
    private ?SenderCacheService $senderCache = null;

    /**
     * Constructor for Postmark factory
     *
     * @param CacheInterface $cache Cache implementation for storing API responses
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function __construct(CacheInterface $cache)
    {
        $this->validateConfiguration();
        $this->cache = $cache;
        $this->initializeHttpClient();
    }

    /**
     * Validates that all required configuration values are present
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    private function validateConfiguration(): void
    {
        if (empty(config('services.postmark.endpoint'))) {
            throw new InvalidArgumentException('Postmark API endpoint must be configured');
        }

        if (empty(config('services.postmark.account'))) {
            throw new InvalidArgumentException('Postmark account token must be configured');
        }
    }

    /**
     * Initializes the HTTP client with base configuration
     */
    private function initializeHttpClient(): void
    {
        $this->client = Http::baseUrl(config('services.postmark.endpoint'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Account-Token' => config('services.postmark.account')
            ])
            ->throw();
    }

    /**
     * Creates and returns a new Server API instance
     *
     * @return Server A configured Server instance for making API requests
     * @throws RuntimeException If dependencies cannot be resolved
     */
    public function server(): Server
    {
        if ($this->serverCache === null) {
            $this->serverCache = new ServerCacheService($this->cache);
        }

        return new Server($this->getClient(), $this->serverCache);
    }

    /**
     * Creates and returns a new Sender API instance
     *
     * @return Sender A configured Sender instance for making API requests
     * @throws RuntimeException If dependencies cannot be resolved
     */
    public function sender(): Sender
    {
        if ($this->senderCache === null) {
            $this->senderCache = new SenderCacheService($this->cache);
        }

        return new Sender($this->getClient(), $this->senderCache);
    }

    /**
     * Creates and returns a new Email API instance
     *
     * @return Email A configured Email instance for making API requests
     */
    public function email(): Email
    {
        return new Email($this->getClient());
    }

    /**
     * Returns the configured HTTP client
     *
     * @return PendingRequest The configured HTTP client
     */
    public function getClient(): PendingRequest
    {
        return $this->client;
    }

    /**
     * Creates a new instance of the Postmark factory
     *
     * @param CacheInterface|null $cache Optional cache implementation
     * @return static New Postmark factory instance
     */
    public static function create(?CacheInterface $cache = null): static
    {
        return new static($cache ?? app(CacheInterface::class));
    }
}
