# Nubanstack

A simple and elegant PHP package for fetching Nigerian banks and validating account numbers using the Paystack API.

## Features

- 🏦 Fetch all Nigerian banks
- ✅ Validate Nigerian bank account numbers (online via Paystack)
- 🔍 **Offline NUBAN validation** - Validate account numbers without API calls
- 🎯 **Bank prediction** - Predict possible banks for any account number
- 🌍 Filter banks by country
- 🚀 Easy to use with simple API
- 🔒 Type-safe with DTO support
- ⚡ Built on Guzzle HTTP client

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require nubanstack/nubanstack
```

## Configuration

You'll need a Paystack test secret key to use this package. You can get one for free on your [Paystack Dashboard](https://dashboard.paystack.com/#/settings/developer).

### Option 1: Environment Variable

Set your Paystack secret key as an environment variable:

```bash
export PAYSTACK_SECRET_KEY=sk_test_your_secret_key_here
```

### Option 2: Pass Directly to Constructor

```php
use Nubanstack\Nubanstack;

$nubanstack = new Nubanstack('sk_test_your_secret_key_here');
```

## Usage

### Fetch All Nigerian Banks

```php
use Nubanstack\Nubanstack;

$nubanstack = new Nubanstack();

try {
    $banks = $nubanstack->getBanks();
    
    foreach ($banks as $bank) {
        echo $bank['name'] . ' - ' . $bank['code'] . PHP_EOL;
    }
} catch (\Nubanstack\Exceptions\NubanstackException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Validate Account Number

```php
use Nubanstack\Nubanstack;

$nubanstack = new Nubanstack();

try {
    $accountDetails = $nubanstack->validateAccount('0123456789', '057');
    
    echo "Account Name: " . $accountDetails['account_name'] . PHP_EOL;
    echo "Account Number: " . $accountDetails['account_number'] . PHP_EOL;
} catch (\Nubanstack\Exceptions\NubanstackException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Predict Possible Banks for an Account Number

Find all banks where an account number could be valid:

```php
use Nubanstack\Nubanstack;

$nubanstack = new Nubanstack();

try {
    $possibleBanks = $nubanstack->predictBanks('0123456789');
    
    echo "This account number could belong to:" . PHP_EOL;
    foreach ($possibleBanks as $bank) {
        echo "  - {$bank['name']} (Code: {$bank['code']})" . PHP_EOL;
    }
} catch (\Nubanstack\Exceptions\NubanstackException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Search for Banks

```php
use Nubanstack\Nubanstack;

$nubanstack = new Nubanstack();

// Search by name
$banks = $nubanstack->searchBanksByName('Access');
foreach ($banks as $bank) {
    echo $bank['name'] . ' - ' . $bank['code'] . PHP_EOL;
}

// Find by code
$bank = $nubanstack->findBankByCode('057');
if ($bank) {
    echo "Found: " . $bank['name'] . PHP_EOL;
}
```

## API Reference

### Online Validation (Requires API Key)

#### `getBanks(): array`

Fetches all Nigerian banks from Paystack.

**Returns:** Array of bank data

**Throws:** `NubanstackException` on error

#### `validateAccount(string $accountNumber, string $bankCode): array`

Validates a Nigerian bank account number using Paystack API (online validation).

**Parameters:**
- `$accountNumber` - The 10-digit account number
- `$bankCode` - The bank code (e.g., '057' for Zenith Bank)

**Returns:** Array containing account details (account_number, account_name, bank_id)

**Throws:** `NubanstackException` on error

#### `setSecretKey(string $secretKey): self`

Updates the Paystack secret key after initialization.

**Parameters:**
- `$secretKey` - Your Paystack secret key

**Returns:** Self for method chaining

### Offline Validation (No API Key Required)

#### `validateAccountOffline(string $accountNumber, string $bankCode): bool`

Validates account number using NUBAN algorithm without API calls.

**Parameters:**
- `$accountNumber` - The 10-digit account number
- `$bankCode` - The bank code (3, 5, or 6 digits)

**Returns:** `true` if valid, `false` otherwise

**Throws:** `NubanstackException` on invalid input

#### `predictBanks(string $accountNumber): array`

Predicts all possible banks for a given account number.

**Parameters:**
- `$accountNumber` - The 10-digit account number

**Returns:** Array of possible banks with 'name' and 'code' keys

**Throws:** `NubanstackException` on invalid account number

#### `getOfflineBankList(): array`

Gets the built-in list of Nigerian banks for offline validation.

**Returns:** Array of banks

#### `findBankByCode(string $code): ?array`

Finds a bank by its code.

**Parameters:**
- `$code` - Bank code

**Returns:** Bank details array or `null` if not found

#### `searchBanksByName(string $name): array`

Searches for banks by name (case-insensitive partial match).

**Parameters:**
- `$name` - Search term

**Returns:** Array of matching banks

## Response Formats

### Bank Response

```php
[
    'id' => 879,
    'name' => '78 Finance Company Ltd',
    'slug' => '78-finance-company-ltd-ng',
    'code' => '40195',
    'longcode' => '110072',
    'gateway' => null,
    'pay_with_bank' => false,
    'supports_transfer' => true,
    'available_for_direct_debit' => false,
    'active' => true,
    'country' => 'Nigeria',
    'currency' => 'NGN',
    'type' => 'nuban',
    'is_deleted' => false,
    'createdAt' => '2025-11-21T12:32:33.000Z',
    'updatedAt' => '2025-11-21T12:32:33.000Z'
]
```

### Account Validation Response

```php
[
    'account_number' => '0123456789',
    'account_name' => 'JOHN DOE',
    'bank_id' => 9
]
```

## How NUBAN Validation Works

The NUBAN (Nigeria Uniform Bank Account Number) validation uses the algorithm specified by the Central Bank of Nigeria (CBN). The package validates account numbers using:

1. **Check Digit Computation**: Each account number has a check digit (last digit) computed using the bank code and serial number
2. **Weight Arrays**: Specific weights are applied to bank codes and serial numbers
3. **Modulo Calculation**: The algorithm uses modulo 10 arithmetic to verify validity

This allows you to:
- Validate account numbers **instantly offline** without API calls
- Predict which bank(s) an account number could belong to
- Verify account numbers before making online validation requests

**Note:** Offline validation only checks if the account number is mathematically valid for a bank. For verification that the account actually exists and to get the account holder's name, use the online `validateAccount()` method.

## Error Handling

The package throws `NubanstackException` for all errors. Always wrap your calls in try-catch blocks:

```php
try {
    $banks = $nubanstack->getBanks();
} catch (\Nubanstack\Exceptions\NubanstackException $e) {
    // Handle error
    echo "Error: " . $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- Built with [Guzzle HTTP Client](https://github.com/guzzle/guzzle)
- Powered by [Paystack API](https://paystack.com/)
- NUBAN validation algorithm adapted from [nuban-bank-prediction-algorithm](https://github.com/03balogun/nuban-bank-prediction-algorithm) by [@03balogun](https://github.com/03balogun)

## Support

If you encounter any issues or have questions, please [open an issue](https://github.com/thechiddx3/nubanstack/issues) on GitHub.
