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
 * Server Data Transfer Object
 *
 * This class represents the data structure for creating or updating a server.
 * It ensures type safety and data validation.
 */
readonly class ServerData
{
    /**
     * Create a new ServerData instance
     *
     * @param  string  $name  Server name
     * @param  string  $color  Server color
     * @param  bool  $smtpApiActivated  Whether SMTP API is activated
     * @param  bool  $rawEmailEnabled  Whether raw email is enabled
     * @param  bool  $postFirstOpenOnly  Whether to post first open only
     * @param  bool  $trackOpens  Whether to track opens
     * @param  string  $trackLinks  Track links setting
     * @param  int  $inboundSpamThreshold  Inbound spam threshold
     * @param  string|null  $deliveryHookUrl  Delivery webhook URL
     * @param  string|null  $inboundHookUrl  Inbound webhook URL
     * @param  string|null  $bounceHookUrl  Bounce webhook URL
     * @param  string|null  $openHookUrl  Open webhook URL
     * @param  string|null  $inboundDomain  Inbound domain
     */
    public function __construct(
        public string $name,
        public string $color,
        public bool $smtpApiActivated,
        public bool $rawEmailEnabled,
        public bool $postFirstOpenOnly,
        public bool $trackOpens,
        public string $trackLinks,
        public int $inboundSpamThreshold,
        public ?string $deliveryHookUrl = null,
        public ?string $inboundHookUrl = null,
        public ?string $bounceHookUrl = null,
        public ?string $openHookUrl = null,
        public ?string $inboundDomain = null,
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
            'Color' => $this->color,
            'SmtpApiActivated' => $this->smtpApiActivated,
            'RawEmailEnabled' => $this->rawEmailEnabled,
            'PostFirstOpenOnly' => $this->postFirstOpenOnly,
            'TrackOpens' => $this->trackOpens,
            'TrackLinks' => $this->trackLinks,
            'InboundSpamThreshold' => $this->inboundSpamThreshold,
            'DeliveryHookUrl' => $this->deliveryHookUrl,
            'InboundHookUrl' => $this->inboundHookUrl,
            'BounceHookUrl' => $this->bounceHookUrl,
            'OpenHookUrl' => $this->openHookUrl,
            'InboundDomain' => $this->inboundDomain,
        ];
    }
}
