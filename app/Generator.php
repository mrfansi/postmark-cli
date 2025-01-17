<?php

namespace App;

use Illuminate\Support\Collection;

class Generator
{
    /**
     * Transform a collection of servers into a table, with optional filtering
     *
     * @return array The table with headers and rows
     */
    public static function getTable(Collection $collection): array
    {

        $headers = $collection->first() ? collect($collection->first())
            ->keys()
            ->toArray() : [];

        $rows = $collection->map(function ($server) {

            return collect($server)
                ->map(function ($value) {
                    if (is_array($value)) {
                        return implode(', ', $value); // Flatten arrays into strings
                    }

                    return $value;
                })
                ->values() // Ensure values align with headers
                ->toArray();
        })->toArray();

        return [$headers, $rows];

    }

    public static function getDetailTable(Collection $collection, array $filteredKeys = []): array
    {

        $headers = ['Key', 'Value'];

        $rows = $collection
            ->when(! empty($filteredKeys), function ($collection) use ($filteredKeys) {
                return $collection->only($filteredKeys); // Filter keys if $filteredKeys is provided
            })
            ->map(function ($value, $key) {

                return [
                    'key' => $key,
                    'value' => is_array($value) ? implode(', ', $value) : $value, // Flatten arrays into strings
                ];
            })
            ->values() // Ensure sequential numeric keys
            ->all();

        return [$headers, $rows];
    }
}
