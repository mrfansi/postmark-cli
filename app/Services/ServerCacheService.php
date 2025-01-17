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

namespace App\Services;

use App\Data\ServerResponse;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Server Cache Service
 *
 * This service handles caching operations for server data.
 */
class ServerCacheService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Cache key prefix for server data
     */
    private const CACHE_PREFIX = 'postmark_servers';

    /**
     * Cache implementation
     */
    private CacheInterface $cache;

    /**
     * Constructor
     *
     * @param  CacheInterface  $cache  Cache implementation
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get servers from cache
     *
     * @param  string  $key  Cache key
     * @return Collection<ServerResponse>|null
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?Collection
    {
        return $this->cache->get($key);
    }

    /**
     * Store servers in cache
     *
     * @param  string  $key  Cache key
     * @param  Collection<ServerResponse>  $servers  Servers to cache
     *
     * @throws InvalidArgumentException
     */
    public function put(string $key, Collection $servers): bool
    {
        return $this->cache->set($key, $servers, self::CACHE_TTL);
    }

    /**
     * Check if key exists in cache
     *
     * @param  string  $key  Cache key
     *
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * Generate cache key for server list
     *
     * @param  int  $count  Number of items per page
     * @param  int  $offset  Pagination offset
     * @param  string  $name  Server name filter
     * @return string Cache key
     */
    public function generateKey(int $count, int $offset, string $name): string
    {
        return sprintf('%s_%d_%d_%s', self::CACHE_PREFIX, $count, $offset, $name);
    }
}
