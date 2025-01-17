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

use App\Postmark\DTOs\SenderResponse;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Server Cache Service
 *
 * This service handles caching operations for server data.
 *
 * @package App\Services
 */
class SenderCacheService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Cache key prefix for server data
     */
    private const CACHE_PREFIX = 'postmark_senders';

    /**
     * Cache implementation
     */
    private CacheInterface $cache;

    /**
     * Constructor
     *
     * @param CacheInterface $cache Cache implementation
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get servers from cache
     *
     * @param string $key Cache key
     * @return Collection<SenderResponse>|null
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?Collection
    {
        return $this->cache->get($key);
    }

    /**
     * Store senders in cache
     *
     * @param string $key Cache key
     * @param Collection<SenderResponse> $senders Senders to cache
     * @return bool
     * @throws InvalidArgumentException
     */
    public function put(string $key, Collection $senders): bool
    {
        return $this->cache->set($key, $senders, self::CACHE_TTL);
    }

    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * Generate cache key for server list
     *
     * @param int $count Number of items per page
     * @param int $offset Pagination offset
     * @return string Cache key
     */
    public function generateKey(int $count, int $offset): string
    {
        return sprintf('%s_%d_%d', self::CACHE_PREFIX, $count, $offset);
    }
}
