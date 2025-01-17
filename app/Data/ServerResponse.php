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

namespace App\Data;

/**
 * Server Response Data Transfer Object
 *
 * This class represents the response data structure from the Postmark API
 * for server-related operations.
 */
readonly class ServerResponse
{
    /**
     * Create a new ServerResponse instance
     *
     * @param  int  $id  Server ID
     * @param  string  $name  Server name
     * @param  array  $apiTokens  API tokens
     * @param  string  $serverLink  Server link
     * @param  string  $color  Server color
     * @param  bool  $smtpApiActivated  Whether SMTP API is activated
     * @param  bool  $rawEmailEnabled  Whether raw email is enabled
     * @param  string|null  $deliveryHookUrl  Delivery webhook URL
     * @param  string|null  $inboundHookUrl  Inbound webhook URL
     * @param  string|null  $bounceHookUrl  Bounce webhook URL
     * @param  string|null  $openHookUrl  Open webhook URL
     * @param  bool  $postFirstOpenOnly  Whether to post first open only
     * @param  bool  $trackOpens  Whether to track opens
     * @param  string  $trackLinks  Track links setting
     * @param  string|null  $inboundDomain  Inbound domain
     * @param  int  $inboundSpamThreshold  Inbound spam threshold
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $apiTokens,
        public string $serverLink,
        public string $color,
        public bool $smtpApiActivated,
        public bool $rawEmailEnabled,
        public ?string $deliveryHookUrl,
        public ?string $inboundHookUrl,
        public ?string $bounceHookUrl,
        public ?string $openHookUrl,
        public bool $postFirstOpenOnly,
        public bool $trackOpens,
        public string $trackLinks,
        public ?string $inboundDomain,
        public int $inboundSpamThreshold,
    ) {}

    /**
     * Create a ServerResponse instance from an array
     *
     * @param  array  $data  Server data from API response
     * @return self New ServerResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['ID'],
            name: $data['Name'],
            apiTokens: $data['ApiTokens'],
            serverLink: $data['ServerLink'],
            color: $data['Color'],
            smtpApiActivated: $data['SmtpApiActivated'],
            rawEmailEnabled: $data['RawEmailEnabled'],
            deliveryHookUrl: $data['DeliveryHookUrl'] ?? null,
            inboundHookUrl: $data['InboundHookUrl'] ?? null,
            bounceHookUrl: $data['BounceHookUrl'] ?? null,
            openHookUrl: $data['OpenHookUrl'] ?? null,
            postFirstOpenOnly: $data['PostFirstOpenOnly'],
            trackOpens: $data['TrackOpens'],
            trackLinks: $data['TrackLinks'],
            inboundDomain: $data['InboundDomain'] ?? null,
            inboundSpamThreshold: $data['InboundSpamThreshold'],
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ID' => $this->id,
            'Name' => $this->name,
            'ApiTokens' => $this->apiTokens,
            'ServerLink' => $this->serverLink,
            'Color' => $this->color,
            'SmtpApiActivated' => $this->smtpApiActivated,
            'RawEmailEnabled' => $this->rawEmailEnabled,
            'DeliveryHookUrl' => $this->deliveryHookUrl,
            'InboundHookUrl' => $this->inboundHookUrl,
            'BounceHookUrl' => $this->bounceHookUrl,
            'OpenHookUrl' => $this->openHookUrl,
            'PostFirstOpenOnly' => $this->postFirstOpenOnly,
            'TrackOpens' => $this->trackOpens,
            'TrackLinks' => $this->trackLinks,
            'InboundDomain' => $this->inboundDomain,
            'InboundSpamThreshold' => $this->inboundSpamThreshold,
        ];
    }
}
