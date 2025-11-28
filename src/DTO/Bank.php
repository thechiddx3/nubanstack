<?php

namespace Nubanstack\DTO;

class Bank
{
    public int $id;
    public string $name;
    public string $slug;
    public string $code;
    public string $longcode;
    public ?string $gateway;
    public bool $payWithBank;
    public bool $supportsTransfer;
    public bool $availableForDirectDebit;
    public bool $active;
    public string $country;
    public string $currency;
    public string $type;
    public bool $isDeleted;
    public string $createdAt;
    public string $updatedAt;

    /**
     * Create a Bank instance from API response data
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $bank = new self();
        $bank->id = $data['id'] ?? 0;
        $bank->name = $data['name'] ?? '';
        $bank->slug = $data['slug'] ?? '';
        $bank->code = $data['code'] ?? '';
        $bank->longcode = $data['longcode'] ?? '';
        $bank->gateway = $data['gateway'] ?? null;
        $bank->payWithBank = $data['pay_with_bank'] ?? false;
        $bank->supportsTransfer = $data['supports_transfer'] ?? false;
        $bank->availableForDirectDebit = $data['available_for_direct_debit'] ?? false;
        $bank->active = $data['active'] ?? false;
        $bank->country = $data['country'] ?? '';
        $bank->currency = $data['currency'] ?? '';
        $bank->type = $data['type'] ?? '';
        $bank->isDeleted = $data['is_deleted'] ?? false;
        $bank->createdAt = $data['createdAt'] ?? '';
        $bank->updatedAt = $data['updatedAt'] ?? '';

        return $bank;
    }

    /**
     * Convert Bank instance to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'longcode' => $this->longcode,
            'gateway' => $this->gateway,
            'pay_with_bank' => $this->payWithBank,
            'supports_transfer' => $this->supportsTransfer,
            'available_for_direct_debit' => $this->availableForDirectDebit,
            'active' => $this->active,
            'country' => $this->country,
            'currency' => $this->currency,
            'type' => $this->type,
            'is_deleted' => $this->isDeleted,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
