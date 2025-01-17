<?php

namespace App\Factories;

use App\Contracts\PostmarkFactoryInterface;
use App\Postmark\Email;
use App\Postmark\Sender;
use App\Postmark\Server;
use App\Services\SenderCacheService;
use App\Services\ServerCacheService;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Factory for creating Postmark API clients
 */
class PostmarkFactory implements PostmarkFactoryInterface
{
    /**
     * Cache service instance
     */
    private ?CacheInterface $cache;

    /**
     * Create a new factory instance
     */
    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Create a new Server API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function server(): Server
    {
        $client = $this->createClient();

        return new Server(
            $client,
            new ServerCacheService($this->cache)
        );
    }

    /**
     * Create a new Sender API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function sender(): Sender
    {
        $client = $this->createClient();

        return new Sender(
            $client,
            new SenderCacheService($this->cache)
        );
    }

    /**
     * Create a new Email API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function email(): Email
    {
        $client = $this->createClient();

        return new Email(
            $client
        );
    }

    /**
     * Create HTTP client with Postmark configuration
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    private function createClient(): PendingRequest
    {
        $endpoint = config('services.postmark.endpoint');
        $token = config('services.postmark.account');

        if (empty($endpoint)) {
            throw new InvalidArgumentException(
                'Postmark API endpoint is not configured. Please set POSTMARK_ENDPOINT in your .env file.'
            );
        }

        if (empty($token)) {
            throw new InvalidArgumentException(
                'Postmark account token is not configured. Please set POSTMARK_ACCOUNT_TOKEN in your .env file.'
            );
        }

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Postmark-Account-Token' => $token,
        ])->baseUrl($endpoint);
    }

    /**
     * Create a new factory instance
     */
    public static function create(?CacheInterface $cache = null): static
    {
        return new static($cache);
    }
}
