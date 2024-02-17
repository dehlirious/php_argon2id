<?php
function memoryLimitToBytes($val) {
    $val = trim($val);
    // Check if the value is purely numeric, which means it's already in bytes
    if (is_numeric($val)) {
        return (int)$val;
    }

    // Regular expression to separate the number from the unit
    if (preg_match('/^(\d+)([gmkGMK])$/', $val, $matches)) {
        $value = (int)$matches[1];
        $unit = strtolower($matches[2]);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
        }
    }

    // If the input doesn't match expected patterns, throw an exception or return a default value
    throw new InvalidArgumentException("Invalid memory limit format: {$val}");
}

function hashPassword($password) {
    // Basic validation for the $password parameter
    if (!is_string($password) || strlen($password) === 0) {
        throw new InvalidArgumentException('Password must be a non-empty string.');
    }
    
    // Define constants for Argon2 options
    $MemoryCostDefault = 1 << 17; // Default memory cost for Argon2 (128 MB in KiB)

    // Get memory limit and handle -1 scenario
    $memoryLimit = function_exists('ini_get') ? ini_get('memory_limit') : '128M';
    if ($memoryLimit === '-1') { // Unlimited memory
        $memoryLimit = '2G'; // Default to 2GB if memory limit is unlimited
    }
    $memoryLimitBytes = memoryLimitToBytes($memoryLimit);

    // Calculate available memory with safety buffer
    $usedMemory = max((function_exists('memory_get_usage') ? memory_get_usage() : 0), (function_exists('memory_get_peak_usage') ? memory_get_peak_usage(false) : 0));
    $availableMemory = $memoryLimitBytes - $usedMemory;

    // Convert to KiB for Argon2 and adjust memory cost within the allowed range, ensuring a safety buffer
    $availableMemoryKiB = (int)(($availableMemory - ($availableMemory * 0.2)) / 1024);
    $memoryCost = min($availableMemoryKiB, $MemoryCostDefault); // Adjust memory cost based on available memory

    // Define the preferred algorithm based on memory cost
    $preferredAlgorithm = PASSWORD_BCRYPT; // Default to BCRYPT
    if ($memoryCost >= (32 * 1024) && defined('PASSWORD_ARGON2ID')) { // If memory cost is 32MB or more and ARGON2ID is available
        $preferredAlgorithm = PASSWORD_ARGON2ID;
    } elseif ($memoryCost >= (32 * 1024) && defined('PASSWORD_ARGON2I')) { // Fallback to ARGON2I if ARGON2ID is not available
        $preferredAlgorithm = PASSWORD_ARGON2I;
    }

    // Adjust time cost based on available memory, capped at 6
    for ($A_TIME_COST = 4; $memoryCost < ($availableMemoryKiB * 0.75) && $A_TIME_COST < 6; ++$A_TIME_COST);
    // Adjust threads based on available memory, capped at 8
    for ($A_THREADS = 4; $memoryCost < ($availableMemoryKiB * 0.75) && $A_THREADS < 8; ++$A_THREADS);

    // Define the options array based on the preferred algorithm
    $al_options = [];
    if ($preferredAlgorithm === PASSWORD_ARGON2ID || $preferredAlgorithm === PASSWORD_ARGON2I) {
        // Ensure memory cost does not exceed the available memory and the default memory cost multiplied by 3 for security
        $al_options = ['memory_cost' => min($availableMemoryKiB, $MemoryCostDefault * 3), 'time_cost' => $A_TIME_COST, 'threads' => $A_THREADS];
    }
    
    $hashedPassword = password_hash($password, $preferredAlgorithm, $al_options); // Hash the password using bcrypt
    
    // Check if the password needs to be rehashed
    for ($i = 0; password_needs_rehash($hashedPassword, $preferredAlgorithm, $al_options); $i++) {
        $hashedPassword = password_hash($password, $preferredAlgorithm, $al_options);
        if ($i >= 5) break; // only allow 5 iterations to avoid endless loops
    }
    
    if ($hashedPassword === false) {
        throw new RuntimeException('Failed to hash password.');
    }
    
    return $hashedPassword;
}
