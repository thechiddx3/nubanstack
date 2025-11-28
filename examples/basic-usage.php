<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nubanstack\Nubanstack;
use Nubanstack\Exceptions\NubanstackException;

// Initialize with your Paystack secret key
$nubanstack = new Nubanstack('sk_test_your_secret_key_here');

echo "=== Nubanstack Examples ===\n\n";

// Example 1: Fetch all banks
echo "1. Fetching all Nigerian banks...\n";
echo str_repeat("-", 50) . "\n";

try {
    $banks = $nubanstack->getBanks();
    
    echo "Total banks: " . count($banks) . "\n\n";
    
    // Display first 5 banks
    echo "First 5 banks:\n";
    foreach (array_slice($banks, 0, 5) as $bank) {
        echo sprintf(
            "  - %s (Code: %s)\n",
            $bank['name'],
            $bank['code']
        );
    }
    
    echo "\n";
} catch (NubanstackException $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Find a specific bank by code
echo "2. Finding a specific bank by code...\n";
echo str_repeat("-", 50) . "\n";

try {
    $banks = $nubanstack->getBanks();
    $searchCode = '057'; // Zenith Bank code
    
    $foundBank = null;
    foreach ($banks as $bank) {
        if ($bank['code'] === $searchCode) {
            $foundBank = $bank;
            break;
        }
    }
    
    if ($foundBank) {
        echo "Bank found:\n";
        echo "  Name: " . $foundBank['name'] . "\n";
        echo "  Code: " . $foundBank['code'] . "\n";
        echo "  Type: " . $foundBank['type'] . "\n";
        echo "  Currency: " . $foundBank['currency'] . "\n";
    } else {
        echo "Bank not found\n";
    }
    
    echo "\n";
} catch (NubanstackException $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Validate an account number
echo "3. Validating an account number...\n";
echo str_repeat("-", 50) . "\n";

try {
    // Replace with actual account number and bank code for testing
    $accountNumber = '0123456789'; // Example account number
    $bankCode = '057'; // Zenith Bank code
    
    $accountDetails = $nubanstack->validateAccount($accountNumber, $bankCode);
    
    echo "Account validation successful:\n";
    echo "  Account Number: " . $accountDetails['account_number'] . "\n";
    echo "  Account Name: " . $accountDetails['account_name'] . "\n";
    echo "  Bank ID: " . ($accountDetails['bank_id'] ?? 'N/A') . "\n";
    
    echo "\n";
} catch (NubanstackException $e) {
    echo "Validation failed: " . $e->getMessage() . "\n\n";
}

// Example 4: Predict Possible Banks
echo "4. Predict possible banks for account number...\n";
echo str_repeat("-", 50) . "\n";

try {
    $accountNumber = '0123456789';
    
    $possibleBanks = $nubanstack->predictBanks($accountNumber);
    
    echo "Account: {$accountNumber}\n";
    echo "Possible banks: " . count($possibleBanks) . "\n\n";
    
    if (!empty($possibleBanks)) {
        foreach ($possibleBanks as $bank) {
            echo "  • {$bank['name']} (Code: {$bank['code']})\n";
        }
    }
    
    echo "\n";
} catch (NubanstackException $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Examples completed ===\n";
