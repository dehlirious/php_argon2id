<?php
/**
 * Convert a memory limit value to bytes.
 *
 * This function converts a memory limit value from various formats (e.g., '2G', '512M', '2048K') to bytes.
 *
 * @param string $val The memory limit value to convert.
 * @return int The memory limit in bytes.
 * @throws InvalidArgumentException If the input format is invalid.
 */
function memoryLimitToBytes($val) {
    $val = trim($val);

    // Check if the value is purely numeric, which means it's already in bytes
    if (is_numeric($val)) {
        return (int)$val;
    }

    // Regular expression to separate the number from the unit
    if (preg_match('/^(\d+)([gmk])$/i', $val, $matches)) { // Update here
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

    // If the input doesn't match expected patterns, throw an exception
    throw new InvalidArgumentException("Invalid memory limit format: {$val}");
}

/**
 * Determine the preferred hashing algorithm based on memory cost and availability.
 *
 * @param int $memoryCost The memory cost in kilobytes.
 * @param int $memoryCutoff The optional memory cutoff in kilobytes.
 * @return int The preferred hashing algorithm constant.
 */
function determinePreferredAlgorithm($memoryCost = null, $memoryCutoff = null) {
    // Define the default preferred algorithm
    $preferredAlgorithm = PASSWORD_DEFAULT;

    // Check if the memory cutoff is provided
    if ($memoryCutoff !== null && $memoryCost !== null) {
        // Check if the memory cost exceeds the memory cutoff and if Argon2id is available
        if ($memoryCost >= $memoryCutoff && defined('PASSWORD_ARGON2ID')) {
            $preferredAlgorithm = PASSWORD_ARGON2ID;
        } 
        // Check if the memory cost exceeds the memory cutoff and if Argon2i is available
        elseif ($memoryCost >= $memoryCutoff && defined('PASSWORD_ARGON2I')) {
            $preferredAlgorithm = PASSWORD_ARGON2I;
        } 
        // Fallback to BCRYPT if neither Argon2id nor Argon2i are available
        elseif (defined('PASSWORD_BCRYPT')) {
            $preferredAlgorithm = PASSWORD_BCRYPT;
        }
    } else {
        // If memory cutoff is not provided, check if any of the algorithms are defined
        if (defined('PASSWORD_ARGON2ID')) {
            $preferredAlgorithm = PASSWORD_ARGON2ID;
        } elseif (defined('PASSWORD_ARGON2I')) {
            $preferredAlgorithm = PASSWORD_ARGON2I;
        } elseif (defined('PASSWORD_BCRYPT')) {
            $preferredAlgorithm = PASSWORD_BCRYPT;
        }
    }

    return $preferredAlgorithm;
}

/**
 * Generate algorithm options for Argon2 hashing based on provided parameters.
 *
 * @param int|null $memoryCost The memory cost in kilobytes.
 * @param int|null $iterations The number of iterations.
 * @param int|null $threads The number of threads.
 * @param int $preferredAlgorithm The preferred hashing algorithm constant.
 * @return array The algorithm options for Argon2 hashing.
 */
function generateArgon2Options($preferredAlgorithm, $memoryCost = null, $iterations = null, $threads = null) {
    // Initialize algorithm options array
    $al_options = [];
    
    // Check if the preferred algorithm is Argon2 and if memory cost, iterations, and threads are defined
    if (($preferredAlgorithm === PASSWORD_ARGON2ID || $preferredAlgorithm === PASSWORD_ARGON2I) &&
        isset($memoryCost, $iterations, $threads)) {
        // Ensure memory cost does not exceed the available memory and the default memory cost multiplied by 3 for security
        $al_options = ['memory_cost' => $memoryCost, 'time_cost' => $iterations, 'threads' => $threads];
    }
    
    return $al_options;
}

/**
 * Rehash the password using the preferred algorithm and options if necessary.
 *
 * @param string $password The password to hash.
 * @param string $hashedPassword The previously hashed password.
 * @param int $preferredAlgorithm The preferred hashing algorithm constant.
 * @param array $al_options The algorithm options.
 * @return string The rehashed password, or the original hashed password if no rehash is required.
 */
function rehashPassword($password, $hashedPassword, $preferredAlgorithm, $al_options) {
    // Check if password needs rehashing
    if (password_needs_rehash($hashedPassword, $preferredAlgorithm, $al_options)) {
        // Rehash the password using the preferred algorithm and options
        for ($i = 0; $i < 5; $i++) {
            $hashedPassword = password_hash($password, $preferredAlgorithm, $al_options);
            if ($i >= 4) {
                // Limit iterations to 5 to avoid endless loops
                break;
            }
        }
    }
    return $hashedPassword;
}



/**
 * Hashes a password using a secure algorithm and configurable options.
 *
 * This function securely hashes a password using bcrypt or Argon2, with options to control memory usage, iterations, and threads.
 *
 * @param string $password The password to hash.
 * @param array $options An associative array of hashing options.
 *   Available options:
 *     - defaultMemory: Default memory in bytes (default: 128 MB in KiB)
 *     - maxMemoryMultiplier: Maximum memory multiplier (default: 3)
 *     - threads: Default number of threads (default: 4) // Corrected key here
 *     - maxThreads: Maximum number of threads (default: 8)
 *     - iterations: Default number of iterations (default: 4) // Updated key here
 *     - maxIterations: Maximum number of iterations (default: 6)
 *     - memoryCutoff: Memory cutoff for algorithm switch (default: 24 MB)
 *     - debug: Debug mode flag (default: false)
 * @return string The hashed password.
 * @throws InvalidArgumentException If the password is empty or not a string.
 * @throws RuntimeException If hashing the password fails.
 */
function hashPassword($password, $options = []) {
    // Basic validation for the $password parameter
    if (!is_string($password) || strlen($password) === 0) {
        throw new InvalidArgumentException('Password must be a non-empty string.');
    }
    
    // Merge options with defaults
    $defaults = array_merge([
        'defaultMemory' => 128 * 1024, // Default memory in bytes (128 MB in KiB)
        'maxMemoryMultiplier' => 3, // Max memory multiplier
        'threads' => 4, // Default threads // Updated key here
        'maxThreads' => 8, // Max threads
        'iterations' => 4, // Default iterations
        'maxIterations' => 6, // Max iterations
        'memoryCutoff' => 24 * 1024, // Memory cutoff for algorithm switch (24MB)
        'debug' => false, // Debug mode flag
    ], $options);
    
    // Extract configuration variables
    extract($defaults);

    // Get memory limit and handle -1 scenario
    $memoryLimit = function_exists('ini_get') ? ini_get('memory_limit') : '128M';
    if ($memoryLimit === '-1') { // Unlimited memory
        $memoryLimit = '2G'; // Default to 2GB if memory limit is unlimited
    }
    $memoryLimitBytes = memoryLimitToBytes($memoryLimit);

    // Calculate available memory with safety buffer
    $usedMemory = max((function_exists('memory_get_usage') ? memory_get_usage(true) : 0), (function_exists('memory_get_peak_usage') ? memory_get_peak_usage(true) : 0));
    $availableMemory = $memoryLimitBytes - $usedMemory;

    // Convert to KiB for Argon2 and adjust memory cost within the allowed range, ensuring a safety buffer
    $availableMemoryKiB = (int)(($availableMemory - ($availableMemory * 0.2)) / 1024);
    $memoryCost = min($availableMemoryKiB, $defaultMemory); // Adjust memory cost based on available memory

    // Define the preferred algorithm based on memory cost & availability
    $preferredAlgorithm = determinePreferredAlgorithm($memoryCost, $memoryCutoff);

    // Adjust memory cost based on available memory
    for (;$memoryCost < ($availableMemoryKiB * 0.75) && $iterations < $maxIterations; ++$iterations);
    for (;$memoryCost < ($availableMemoryKiB * 0.75) && $threads < $maxThreads; ++$threads);
    
    $memoryCost = min($availableMemoryKiB, $defaultMemory * $maxMemoryMultiplier);

    // Define the options array based on the preferred algorithm
	$al_options = generateArgon2Options($preferredAlgorithm, $memoryCost, $iterations, $threads);
    
    // Hash the password using bcrypt or Argon2
    $hashedPassword = password_hash($password, $preferredAlgorithm, $al_options);
    
    /* Check if the password needs to be rehashed
    *  Note: This case should never happen under normal circumstances. 
    *  The password rehashing is typically performed upon user sign-in using password_verify(). 
    *  This code has been added for thoroughness and as a safeguard measure. 
    */
    $hashedPassword = rehashPassword($password, $hashedPassword, $preferredAlgorithm, $al_options);
    
    if ($hashedPassword === false) {
        throw new RuntimeException('Failed to hash password.');
    }
    
    // Debug output if debug mode is enabled
    if ($debug) {
        echo "\nMemory Limit: $memoryLimit";
        echo "\nUsed Memory: ". $usedMemory/1024/1024;
        echo "\nAvailable Memory: " . $availableMemory/1024/1024;
        echo "\nMemory Cost: " .$memoryCost/1024 ;
        echo "\nPreferred Algorithm: $preferredAlgorithm";
        echo "\nIterations: $iterations";
        echo "\nThreads: $threads\n";
    }
    
    return $hashedPassword;
}
