<?php

namespace Nubanstack\DTO;

class AccountValidation
{
    public string $accountNumber;
    public string $accountName;
    public ?string $bankId;

    /**
     * Create an AccountValidation instance from API response data
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $account = new self();
        $account->accountNumber = $data['account_number'] ?? '';
        $account->accountName = $data['account_name'] ?? '';
        $account->bankId = $data['bank_id'] ?? null;

        return $account;
    }

    /**
     * Convert AccountValidation instance to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
            'bank_id' => $this->bankId,
        ];
    }
}
