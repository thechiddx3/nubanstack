<?php

namespace Nubanstack;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nubanstack\Exceptions\NubanstackException;
use Nubanstack\NubanValidator;

class Nubanstack
{
    private const BASE_URL = 'https://api.paystack.co';
    
    private Client $client;
    private string $secretKey;

    /**
     * Initialize Nubanstack with Paystack secret key
     *
     * @param string|null $secretKey Paystack secret key (defaults to env SK_TEST_KEY or provided key)
     */
    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? getenv('PAYSTACK_SECRET_KEY') ?? '';
        
        if (empty($this->secretKey)) {
            throw new NubanstackException('Paystack secret key is required');
        }

        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Fetch all Nigerian banks
     *
     * @return array
     * @throws NubanstackException
     */
    public function getBanks(): array
    {
        try {
            $response = $this->client->get('/bank');
            $body = json_decode($response->getBody()->getContents(), true);

            if (!$body['status']) {
                throw new NubanstackException($body['message'] ?? 'Failed to fetch banks');
            }

            return $body['data'] ?? [];
        } catch (GuzzleException $e) {
            throw new NubanstackException('Error fetching banks: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Validate a Nigerian bank account number
     *
     * @param string $accountNumber The account number to validate
     * @param string $bankCode The bank code
     * @return array Account details if valid
     * @throws NubanstackException
     */
    public function validateAccount(string $accountNumber, string $bankCode): array
    {
        if (strlen($accountNumber) !== 10) {
            throw new NubanstackException('Account number must be 10 digits');
        }

        if (empty($bankCode)) {
            throw new NubanstackException('Bank code is required');
        }

        try {
            $response = $this->client->get('/bank/resolve', [
                'query' => [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!$body['status']) {
                throw new NubanstackException($body['message'] ?? 'Failed to validate account');
            }

            return $body['data'] ?? [];
        } catch (GuzzleException $e) {
            throw new NubanstackException('Error validating account: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get banks filtered by country
     *
     * @param string $country Country code (default: 'Nigeria')
     * @return array
     * @throws NubanstackException
     */
    public function getBanksByCountry(string $country = 'Nigeria'): array
    {
        try {
            $response = $this->client->get('/bank', [
                'query' => [
                    'country' => $country,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!$body['status']) {
                throw new NubanstackException($body['message'] ?? 'Failed to fetch banks');
            }

            return $body['data'] ?? [];
        } catch (GuzzleException $e) {
            throw new NubanstackException('Error fetching banks: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Set custom secret key after initialization
     *
     * @param string $secretKey
     * @return self
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;
        
        // Reinitialize client with new key
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        return $this;
    }

    /**
     * Validate a Nigerian bank account number using NUBAN algorithm
     * This is an offline validation that checks if the account number is valid for a given bank
     *
     * @param string $accountNumber The 10-digit account number
     * @param string $bankCode The bank code (3, 5, or 6 digits)
     * @return bool True if valid, false otherwise
     * @throws NubanstackException
     */
    public function validateAccountOffline(string $accountNumber, string $bankCode): bool
    {
        return NubanValidator::isBankAccountValid($accountNumber, $bankCode);
    }

    /**
     * Predict possible banks for a given account number
     * Returns all banks where the account number is mathematically valid
     *
     * @param string $accountNumber The 10-digit account number
     * @return array Array of possible banks with 'name' and 'code' keys
     * @throws NubanstackException
     */
    public function predictBanks(string $accountNumber): array
    {
        return NubanValidator::getPossibleBanks($accountNumber);
    }

    /**
     * Get the built-in list of Nigerian banks for offline validation
     *
     * @return array
     */
    public function getOfflineBankList(): array
    {
        return NubanValidator::getBankList();
    }

    /**
     * Find a bank by code from the built-in list
     *
     * @param string $code Bank code
     * @return array|null Bank details or null if not found
     */
    public function findBankByCode(string $code): ?array
    {
        return NubanValidator::findBankByCode($code);
    }

    /**
     * Search for banks by name (case-insensitive partial match)
     *
     * @param string $name Search term
     * @return array Array of matching banks
     */
    public function searchBanksByName(string $name): array
    {
        return NubanValidator::findBanksByName($name);
    }
}
