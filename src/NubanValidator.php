<?php

namespace Nubanstack;

use Nubanstack\Exceptions\NubanstackException;

class NubanValidator
{
    /**
     * Bank code weights as per CBN NUBAN specification
     * @see https://www.cbn.gov.ng/out/2018/psmd/exposure%20circular%20for%20nuban.pdf
     */
    private const BANK_CODE_WEIGHTS = [3, 7, 3, 3, 7, 3];

    /**
     * Serial number weights as per CBN NUBAN specification
     */
    private const SERIAL_NUMBER_WEIGHTS = [3, 7, 3, 3, 7, 3, 3, 7, 3];

    /**
     * Comprehensive list of Nigerian banks
     */
    private const BANKS = [
        ['name' => 'Access Bank', 'code' => '044'],
        ['name' => 'Access Bank (Diamond)', 'code' => '063'],
        ['name' => 'Carbon', 'code' => '565'],
        ['name' => 'Ecobank Nigeria', 'code' => '050'],
        ['name' => 'Fidelity Bank', 'code' => '070'],
        ['name' => 'First Bank of Nigeria', 'code' => '011'],
        ['name' => 'First City Monument Bank', 'code' => '214'],
        ['name' => 'Guaranty Trust Bank', 'code' => '058'],
        ['name' => 'Jaiz Bank', 'code' => '301'],
        ['name' => 'Keystone Bank', 'code' => '082'],
        ['name' => 'Polaris Bank', 'code' => '076'],
        ['name' => 'Providus Bank', 'code' => '101'],
        ['name' => 'Rubies MFB', 'code' => '125'],
        ['name' => 'Signature Bank Ltd', 'code' => '106'],
        ['name' => 'Stanbic IBTC Bank', 'code' => '221'],
        ['name' => 'Standard Chartered Bank', 'code' => '068'],
        ['name' => 'Sterling Bank', 'code' => '232'],
        ['name' => 'Titan Bank', 'code' => '102'],
        ['name' => 'Union Bank of Nigeria', 'code' => '032'],
        ['name' => 'United Bank For Africa', 'code' => '033'],
        ['name' => 'Unity Bank', 'code' => '215'],
        ['name' => 'VFD Microfinance Bank Limited', 'code' => '566'],
        ['name' => 'Wema Bank', 'code' => '035'],
        ['name' => 'Zenith Bank', 'code' => '057'],
        ['name' => 'OPay Digital Services Limited (OPay)', 'code' => '999992'],
        ['name' => 'Paga', 'code' => '100002'],
        ['name' => 'PalmPay', 'code' => '999991'],
        ['name' => 'Paystack-Titan', 'code' => '100039'],
        ['name' => 'Sparkle Microfinance Bank', 'code' => '51310'],
        ['name' => 'Moniepoint MFB', 'code' => '50515'],
        ['name' => 'GoMoney', 'code' => '100022'],
    ];

    /**
     * Calculate weighted sum of a value with given weights
     *
     * @param string $value
     * @param array $weights
     * @return int
     * @throws NubanstackException
     */
    private static function calculateWeightedSum(string $value, array $weights): int
    {
        if (strlen($value) !== count($weights)) {
            throw new NubanstackException('Value and weights must have the same length');
        }

        $sum = 0;
        $digits = str_split($value);

        foreach ($digits as $index => $digit) {
            $sum += (int)$digit * $weights[$index];
        }

        return $sum;
    }

    /**
     * Compute the check digit for a bank account number
     *
     * @param string $bankCode
     * @param string $serialNumber
     * @return int
     * @throws NubanstackException
     */
    public static function computeCheckDigit(string $bankCode, string $serialNumber): int
    {
        $result = self::calculateWeightedSum($bankCode, self::BANK_CODE_WEIGHTS) + 
                  self::calculateWeightedSum($serialNumber, self::SERIAL_NUMBER_WEIGHTS);

        $subtractionResult = 10 - ($result % 10);

        return $subtractionResult === 10 ? 0 : $subtractionResult;
    }

    /**
     * Pad bank code to 6 digits as per NUBAN specification
     *
     * @param string $bankCode
     * @return string
     * @throws NubanstackException
     */
    private static function padBankCode(string $bankCode): string
    {
        // Remove non-digits
        $paddedBankCode = preg_replace('/\D/', '', $bankCode);

        if (strlen($paddedBankCode) === 3) {
            $paddedBankCode = '000' . $paddedBankCode;
        } elseif (strlen($paddedBankCode) === 5) {
            $paddedBankCode = '9' . $paddedBankCode;
        }

        if (strlen($paddedBankCode) !== 6) {
            throw new NubanstackException(
                "Invalid bank code, bank code must be 3, 5 or 6 digits long. {$paddedBankCode} is " . 
                strlen($paddedBankCode) . " digits long"
            );
        }

        return $paddedBankCode;
    }

    /**
     * Validate a Nigerian bank account number using NUBAN algorithm
     *
     * @param string $accountNumber The 10-digit account number
     * @param string $bankCode The bank code (3, 5, or 6 digits)
     * @return bool True if valid, false otherwise
     * @throws NubanstackException
     */
    public static function isBankAccountValid(string $accountNumber, string $bankCode): bool
    {
        if (strlen($accountNumber) !== 10) {
            throw new NubanstackException('Invalid account number, account number must be 10 digits long');
        }

        $paddedBankCode = self::padBankCode($bankCode);
        $serialNumber = substr($accountNumber, 0, 9);
        $accountCheckDigit = $accountNumber[9];

        $checkDigit = self::computeCheckDigit($paddedBankCode, $serialNumber);

        return (string)$checkDigit === $accountCheckDigit;
    }

    /**
     * Predict possible banks for a given account number
     * Returns all banks where the account number is valid
     *
     * @param string $accountNumber The 10-digit account number
     * @param array|null $banks Optional custom bank list (defaults to built-in list)
     * @return array Array of possible banks
     * @throws NubanstackException
     */
    public static function getPossibleBanks(string $accountNumber, ?array $banks = null): array
    {
        if (strlen($accountNumber) !== 10) {
            throw new NubanstackException('Invalid account number, account number must be 10 digits long');
        }

        $bankList = $banks ?? self::BANKS;
        $possibleBanks = [];

        foreach ($bankList as $bank) {
            try {
                if (self::isBankAccountValid($accountNumber, $bank['code'])) {
                    $possibleBanks[] = $bank;
                }
            } catch (NubanstackException $e) {
                // Skip invalid bank codes
                continue;
            }
        }

        return $possibleBanks;
    }

    /**
     * Get the built-in list of Nigerian banks
     *
     * @return array
     */
    public static function getBankList(): array
    {
        return self::BANKS;
    }

    /**
     * Find a bank by code from the built-in list
     *
     * @param string $code
     * @return array|null
     */
    public static function findBankByCode(string $code): ?array
    {
        foreach (self::BANKS as $bank) {
            if ($bank['code'] === $code) {
                return $bank;
            }
        }

        return null;
    }

    /**
     * Find banks by name (partial match, case-insensitive)
     *
     * @param string $name
     * @return array
     */
    public static function findBanksByName(string $name): array
    {
        $results = [];
        $searchName = strtolower($name);

        foreach (self::BANKS as $bank) {
            if (stripos($bank['name'], $searchName) !== false) {
                $results[] = $bank;
            }
        }

        return $results;
    }
}
