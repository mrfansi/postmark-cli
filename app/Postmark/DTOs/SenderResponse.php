<?php

namespace App\Postmark\DTOs;

/**
 * Sender Response Data Transfer Object
 *
 * This class represents the response data structure from the Postmark API
 * for server-related operations.
 *
 * @package App\Postmark\DTOs
 */
readonly class SenderResponse
{
    /**
     * Create a new SenderResponse instance
     *
     * @param string $domain Domain associated with sender signature.
     * @param string $emailAddress string of objects that each represent a sender signature.
     * @param string $replyToEmailAddress Reply-To email associated with sender signature.
     * @param string $name From name of sender signature.
     * @param bool $confirmed Indicates whether this sender signature has been confirmed.
     * @param bool $spfVerified See our blog post to learn why this field was deprecated.
     * @param string|null $spfHost Host name used for the SPF configuration.
     * @param string|null $spfTextValue Value that can be optionally setup with your DNS host. See our blog post to learn why this field is no longer necessary.
     * @param bool $dkimVerified Specifies whether DKIM has ever been verified for the domain or not. Once DKIM is verified, this response will stay true, even if the record is later removed from DNS.
     * @param bool $weakDKIM DKIM is using a strength weaker than 1024 bit. If so, it’s possible to request a new DKIM using the RequestNewDKIM function below.
     * @param string|null $dkimHost DNS TXT host being used to validate messages sent in.
     * @param string|null $dkimTextValue DNS TXT value being used to validate messages sent in.
     * @param string|null $dkimPendingHost If a DKIM renewal has been initiated or this DKIM is from a new Sender Signature, this field will show the pending DKIM DNS TXT host which has yet to be setup and confirmed at your registrar or DNS host.
     * @param string|null $dkimPendingTextValue Similar to the DKIMPendingHost field, this will show the DNS TXT value waiting to be confirmed at your registrar or DNS host.
     * @param string|null $dkimRevokedHost Once a new DKIM has been confirmed at your registrar or DNS host, Postmark will revoke the old DKIM host in preparation for removing it permanently from the system.
     * @param string|null $dkimRevokedTextValue Similar to DKIMRevokedHost, this field will show the DNS TXT value that will soon be removed from the Postmark system.
     * @param bool $safeToRemoveRevokedKeyFromDNS Indicates whether you may safely delete the old DKIM DNS TXT records at your registrar or DNS host. The new DKIM is now safely in use.
     * @param string|null $dkimUpdateStatus While DKIM renewal or new DKIM operations are being conducted or setup, this field will indicate Pending. After all DNS TXT records are up to date and any pending renewal operations are finished, it will indicate Verified.
     * @param bool $returnPathDomain The custom Return-Path domain for this signature. For more information about this field, please read our support page.
     * @param bool $returnPathDomainVerified The verification state of the Return-Path domain. Tells you if the Return-Path is actively being used or still needs further action to be used.
     * @param string|null $returnPathDomainCNAMEValue The CNAME DNS record that Postmark expects to find at the ReturnPathDomain value.
     * @param int $id Unique ID of sender signature.
     * @param string|null $confirmationPersonalNote The text of the personal note sent to the recipient.
     */
    public function __construct(
        public string  $domain,
        public string  $emailAddress,
        public string  $replyToEmailAddress,
        public string  $name,
        public bool    $confirmed,
        public bool    $spfVerified,
        public ?string $spfHost = null,
        public ?string $spfTextValue = null,
        public bool    $dkimVerified,
        public bool    $weakDKIM,
        public ?string $dkimHost = null,
        public ?string $dkimTextValue = null,
        public ?string $dkimPendingHost = null,
        public ?string $dkimPendingTextValue = null,
        public ?string $dkimRevokedHost = null,
        public ?string $dkimRevokedTextValue = null,
        public bool    $safeToRemoveRevokedKeyFromDNS,
        public ?string $dkimUpdateStatus = null,
        public bool    $returnPathDomain,
        public bool    $returnPathDomainVerified,
        public ?string $returnPathDomainCNAMEValue = null,
        public int     $id,
        public ?string $confirmationPersonalNote = null
    )
    {
    }

    /**
     * Create a SenderResponse instance from an array
     *
     * @param array $data Sender data from API response
     * @return self New SenderResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['Domain'],
            emailAddress: $data['EmailAddress'],
            replyToEmailAddress: $data['ReplyToEmailAddress'],
            name: $data['Name'],
            confirmed: $data['Confirmed'],
            spfVerified: $data['SPFVerified'],
            spfHost: $data['SPFHost'],
            spfTextValue: $data['SPFTextValue'],
            dkimVerified: $data['DKIMVerified'],
            weakDKIM: $data['WeakDKIM'],
            dkimHost: $data['DKIMHost'],
            dkimTextValue: $data['DKIMTextValue'],
            dkimPendingHost: $data['DKIMPendingHost'],
            dkimPendingTextValue: $data['DKIMPendingTextValue'],
            dkimRevokedHost: $data['DKIMRevokedHost'],
            dkimRevokedTextValue: $data['DKIMRevokedTextValue'],
            safeToRemoveRevokedKeyFromDNS: $data['SafeToRemoveRevokedKeyFromDNS'],
            dkimUpdateStatus: $data['DKIMUpdateStatus'],
            returnPathDomain: $data['ReturnPathDomain'],
            returnPathDomainVerified: $data['ReturnPathDomainVerified'],
            returnPathDomainCNAMEValue: $data['ReturnPathDomainCNAMEValue'],
            id: $data['ID'],
            confirmationPersonalNote: $data['ConfirmationPersonalNote'],
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
            'domain' => $this->domain,
            'emailAddress' => $this->emailAddress,
            'replyToEmailAddress' => $this->replyToEmailAddress,
            'name' => $this->name,
            'confirmed' => $this->confirmed,
            'spfVerified' => $this->spfVerified,
            'spfHost' => $this->spfHost,
            'spfTextValue' => $this->spfTextValue,
            'dkimVerified' => $this->dkimVerified,
            'weakDKIM' => $this->weakDKIM,
            'dkimHost' => $this->dkimHost,
            'dkimTextValue' => $this->dkimTextValue,
            'dkimPendingHost' => $this->dkimPendingHost,
            'dkimPendingTextValue' => $this->dkimPendingTextValue,
            'dkimRevokedHost' => $this->dkimRevokedHost,
            'dkimRevokedTextValue' => $this->dkimRevokedTextValue,
            'safeToRemoveRevokedKeyFromDNS' => $this->safeToRemoveRevokedKeyFromDNS,
            'dkimUpdateStatus' => $this->dkimUpdateStatus,
            'returnPathDomain' => $this->returnPathDomain,
            'returnPathDomainVerified' => $this->returnPathDomainVerified,
            'returnPathDomainCNAMEValue' => $this->returnPathDomainCNAMEValue,
            'id' => $this->id,
            'confirmationPersonalNote' => $this->confirmationPersonalNote,
        ];
    }
}
