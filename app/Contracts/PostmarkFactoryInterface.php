<?php

namespace App\Contracts;

use App\Postmark\Email;
use App\Postmark\Server;
use App\Postmark\Sender;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

/**
 * Interface for Postmark Factory
 *
 * This interface defines the contract for creating various Postmark API clients.
 *
 * @package App\Contracts
 */
interface PostmarkFactoryInterface
{
    /**
     * Creates and returns a new Server API instance
     *
     * @return Server A configured Server instance for making API requests
     */
    public function server(): Server;

    /**
     * Creates and returns a new Sender API instance
     *
     * @return Sender A configured Sender instance for making API requests
     */
    public function sender(): Sender;

    /**
     * Creates and returns a new Email API instance
     *
     * @return Email A configured Email instance for making API requests
     */
    public function email(): Email;

    /**
     * Creates a new instance of the Postmark factory
     *
     * @param CacheInterface|null $cache Optional cache implementation
     * @return static New Postmark factory instance
     */
    public static function create(?CacheInterface $cache = null): static;
}
