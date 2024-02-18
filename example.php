<?php
/**
 * Example usage of hashPassword function.
 *
 * This script demonstrates how to securely hash a password using the hashPassword function
 * with different sets of options.
 */

$start = hrtime(true);

// Include the function definition
require_once 'argon2id.php'; // Replace 'your_function_file.php' with the actual file name

try {
    // Password to be hashed
    $password = 'mySecurePassword123';

    // Example 1: Using Default Options
    $hashedPasswordDefault = hashPassword($password);
    echo "Example 1: Using Default Options\n";
    echo "Hashed Password: $hashedPasswordDefault\n\n";

    // Example 2: Using Custom Options
    $optionsCustom = [
        'defaultMemory' => 256 * 1024,
        'maxMemoryMultiplier' => 4,
        'threads' => 6,
        'iterations' => 5,
    ];
    $hashedPasswordCustom = hashPassword($password, $optionsCustom);
    echo "Example 2: Using Custom Options\n";
    echo "Hashed Password: $hashedPasswordCustom\n\n";

    // Example 3: Using Additional Options
    $optionsAdditional = [
        'maxThreads' => 10,
        'maxIterations' => 7,
        'memoryCutoff' => 64 * 1024,
    ];
    $hashedPasswordAdditional = hashPassword($password, $optionsAdditional);
    echo "Example 3: Using Additional Options\n";
    echo "Hashed Password: $hashedPasswordAdditional\n\n";

    // Example 4: Using All Options
    $optionsAll = [
        'defaultMemory' => 256 * 1024,
        'maxMemoryMultiplier' => 4,
        'threads' => 6,
        'maxThreads' => 10,
        'iterations' => 5,
        'maxIterations' => 7,
        'memoryCutoff' => 64 * 1024,
        'debug' => true,
    ];
    $hashedPasswordAll = hashPassword($password, $optionsAll);
    echo "Example 4: Using All Options\n";
    echo "Hashed Password: $hashedPasswordAll\n\n";
} catch (InvalidArgumentException $e) {
    // Handle invalid arguments exception
    echo "Error: " . $e->getMessage() . "\n";
} catch (RuntimeException $e) {
    // Handle runtime exception
    echo "Error: " . $e->getMessage() . "\n";
}

$end = hrtime(true);
$eta = $end - $start;
// convert nanoseconds to milliseconds
$eta /= 1e+6;
echo "\r\nCode block was running for $eta milliseconds";
