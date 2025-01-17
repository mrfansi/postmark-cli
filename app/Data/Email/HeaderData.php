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

namespace App\Data\Email;

/**
 * Email Header Data Transfer Object
 *
 * This class represents the data structure for custom email headers.
 * It ensures type safety and data validation.
 */
readonly class HeaderData
{
    /**
     * Create a new HeaderData instance
     *
     * @param  string  $name  The name of the custom header
     * @param  string  $value  The value of the custom header
     */
    public function __construct(
        public string $name,
        public string $value,
    ) {}

    /**
     * Convert the DTO to an array for API requests
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'Name' => $this->name,
            'Value' => $this->value,
        ];
    }
}
