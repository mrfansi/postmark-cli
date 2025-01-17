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

use BadMethodCallException;

/**
 * Trait for enforcing server token requirement
 *
 * This trait ensures that certain methods can only be called after server authentication
 * has been properly set up using either withServer() or withServerToken().
 */
trait RequiresServerToken
{
    /**
     * Flag to track if server token has been set
     */
    private bool $serverTokenSet = false;

    /**
     * Mark server token as set
     */
    protected function markServerTokenSet(): void
    {
        $this->serverTokenSet = true;
    }

    /**
     * Ensure server token is set before proceeding
     *
     * @throws BadMethodCallException When server token is not set
     */
    protected function ensureServerTokenIsSet(): void
    {
        if (! $this->isServerTokenSet()) {
            throw new BadMethodCallException(
                'Server token must be set first. Call withServer() or withServerToken() before this operation.'
            );
        }
    }

    /**
     * Check if server token is set
     */
    protected function isServerTokenSet(): bool
    {
        return $this->serverTokenSet;
    }
}
