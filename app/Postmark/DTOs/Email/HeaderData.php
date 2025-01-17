<?php

namespace App\Postmark\DTOs\Email;

/**
 * Email Header Data Transfer Object
 *
 * This class represents the data structure for custom email headers.
 * It ensures type safety and data validation.
 *
 * @package App\Postmark\DTOs\Email
 */
readonly class HeaderData
{
    /**
     * Create a new HeaderData instance
     *
     * @param string $name The name of the custom header
     * @param string $value The value of the custom header
     */
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }

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
