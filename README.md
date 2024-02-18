# [Adaptive Security: Dynamic Hashing Based on System Resources]
![GitHub Stars](https://img.shields.io/github/stars/dehlirious/php_argon2id)
![GitHub Issues](https://img.shields.io/github/issues/dehlirious/php_argon2id)

## Description
This is designed to dynamically adjust hashing parameters, including memory usage and computational complexity, based on the available system resources. This approach ensures that applications can offer the highest level of security without overburdening the system, making it ideal for a wide range of deployment environments, from shared hosting to high-end servers.

By default, the maximum ram usage is 384mb (128mb * 3)

You can modify this by changing this `min($availableMemoryKiB, $MemoryCostDefault * 3)`

## How It Works
The function's operation is based on an intelligent adaptation to the system's available memory:
- **Baseline Memory Usage:** Starts at 128MB, with the potential to increase up to three times, maxing at 384MB for password hashing operations. This adaptive use of resources ensures maximum efficiency without exceeding practical limits. (but if you want to throw 2gb at it, go ahead!)
- **Adaptive Scaling:** Begins with 4 threads and 4 iterations, scaling up to 6 iterations and 8 threads for systems that can allocate 384MB of RAM to the hashing process. This decision balances execution time with security, finding no significant benefit beyond the 384MB threshold nor the 8 thread threshold.
- **Execution Time:** Varies with the configuration and computing power, from 482ms at the highest setting (384MB RAM, 6 iterations, 8 threads) to 100ms at the baseline (128MB RAM, 4 iterations, 4 threads). For 200MB of available RAM, the function maintains 4 iterations and 4 threads, resulting in a 168ms execution time.
- **Efficiency Across Hardware:** Matches the function's demand with the system's capabilities, ensuring optimal efficiency. This design choice prevents overloading less powerful machines while fully utilizing the capabilities of more robust systems.
- **Fallback Mechanism:** Defaults to BCRYPT if the memory cost calculation suggests a value below 32MB or if Argon2id is not available, ensuring compatibility across various platforms and configurations.

## Features
- **Dynamic Adjustment:** Automatically scales hashing parameters based on the system's memory_limit setting.
- **Resource Efficient:** Optimizes for both low-end and high-end systems, ensuring best use of available resources.
- **Fallback Mechanism:** Defaults to BCRYPT for lower memory configurations or when Argon2id is not available, ensuring wide compatibility.
- **Enhanced Security:** Offers the potential for stronger security on systems capable of supporting higher resource usage.

## Configuring the `hashPassword` Function

The `hashPassword` function offers flexibility, allowing customization to suit the specific requirements of your application. Below is an overview of the configurable parameters and recommendations for adjusting them for different use cases.

### Parameters:

- **defaultMemory:** Initial memory allocation for hashing, defaulting to 128MB.
- **maxMemory:** Maximum memory allocation for hashing, defaulted to 384MB.
- **defaultThreads:** Default number of threads used in hashing, set to 4.
- **maxThreads:** Maximum number of threads for hashing, capped at 8.
- **defaultIterations:** Default number of hashing iterations, starting at 4.
- **maxIterations:** Maximum number of hashing iterations, up to 6.
- **memoryCutoff:** Memory usage threshold for algorithm switch, set at 32MB.

### Use Case Examples:

**High-Security Application:**
For stringent security requirements, such as storing highly sensitive data, consider increasing memory and iteration counts. Here's an example of how you can adjust the parameters:
```
$options = [
    'defaultMemory' => 256 * 1024, // For 256MB
    'maxMemoryMultiplier' => 3, // * Note: This value determines the maximum multiplier for memory allocation. For instance, if there's sufficient available RAM, it will multiply the default memory limit by 3 (e.g., 256 MB * 3 = 768 MB). Keep in mind that increasing this multiplier can lead to longer execution times.
    'iterations' => 6, // * Note: Increasing this value will enhance security but will also prolong the hashing execution time.
    'maxIterations' => 8,
    'threads' => 8, // * Note: Setting this value higher than the number of CPU cores may not provide additional performance benefits and could potentially degrade performance due to increased contention and context switching overhead.
    'maxThreads' => 16, // Max threads
];
```
Invoke `hashPassword` with `$options` for user passwords.

**General Use Web Application:**
For typical security needs, sticking to the default parameters or making minor adjustments may suffice.
```
$options = [
    'defaultMemory' => 128 * 1024, // Keeps default 128MB
];
```
Adjust other parameters as needed and invoke `hashPassword`.

### Adjusting Parameters:

- **Memory and Iterations:** Increase `defaultMemory` and `iterations` for heightened security. Adjust `maxMemory` and `iterations` based on system performance and capability.
- **Threads:** Modify `threads` and `maxThreads` based on CPU core availability and desired concurrency. More threads can accelerate hashing but will consume more CPU resources.
- Ensure to test your configuration under operational conditions to strike a balance between security and performance according to your requirements.



## Optimizing Argon2id Parameters
When configuring Argon2id, the parameter adjustment should follow a specific order to maximize security:

1. **Maximize Memory Usage:** Begin with increasing memory usage as much as possible, starting with a time cost parameter of 1. This makes the hashing process more resistant to brute-force attacks by requiring significant memory resources to attempt cracking.

2. **Increase Parallelism:** Then, increase the parallelism up to the number of cores available on your system. This should be done without reducing the memory usage set in the previous step, leveraging multi-core processors to enhance performance without compromising security.

3. **Adjust Time Cost:** Finally, increase the time cost as much as possible without affecting the previously set parameters. This lengthens the computation time for each hash, further securing against brute-force attempts.

For more in-depth discussions and recommendations on Argon2id parameter selection, refer to discussions on security forums, such as [this detailed thread on Security Stack Exchange](https://security.stackexchange.com/questions/247936/since-gpus-have-gigabytes-of-memory-does-argon2id-need-to-use-gigabytes-of-memo).

## Additional Considerations

### Memory Factor vs. Password Entropy
It's important to balance the memory factor with the actual entropy of passwords and the work factor involved in hash generation. For instance, a 20-character password combined with a hash generation time of 1 second significantly reduces the feasibility of brute-forcing to nearly impossible levels. Therefore, a large memory factor becomes critical primarily when the risk of brute-forcing is elevated, and you aim to decrease this risk by 1-3 orders of magnitude. The decision on the acceptable values for memory, time, and parallelism factors should be carefully calculated based on your specific security needs and the potential risk scenario.

### Choosing Argon2id Over PBKDF2
The preference for Argon2id over older algorithms like PBKDF2 isn't arbitrary. One of the key advantages of Argon2id lies in its enhanced resistance to side-channel attacks. This attribute makes Argon2id a more secure choice in environments where the risk of such attacks is a concern. The algorithm's design to effectively utilize multiple system resources (memory, CPU) further aids in its ability to provide a robust defense against a wide range of attack vectors, including those that PBKDF2 is more susceptible to.

## Benchmark Results
For benchmark results demonstrating the impact of increasing bcrypt cost factors on various hardware configurations, refer to [this article](https://php.watch/versions/8.4/password_hash-bcrypt-cost-increase). The article provides insights into the performance implications of adjusting bcrypt cost factors and highlights the importance of finding the right balance between security and performance. Understanding these benchmarks can help in making informed decisions when configuring password hashing parameters to achieve optimal security without sacrificing user experience.


## Argon2id Security Considerations

- **Use Argon2id:** Argon2id is recommended for password hashing due to its resistance against various types of attacks, including side-channel attacks.

- **Memory Usage:** The amount of memory used in Argon2id significantly affects the advantage an attacker has compared to your CPU. It's recommended to use at least 128 MiB of memory for secure hashing. Using higher memory configurations, such as 2 GiB or more, further strengthens security.

- **Against GPUs:** Against 2021 GPUs, using 128 MiB of memory provides a high level of security. However, for enhanced security against GPUs, consider using higher memory configurations, such as 2 GiB or more.

- **Against ASICs:** Against ASICs, which are specialized hardware for cracking passwords, it's essential to use higher memory configurations. A few gigabytes of RAM used for password hashing can significantly increase the cost for attackers using ASICs.

- **Cracking Software:** As of 2021, there is no efficient Argon2 cracker available, making Argon2 a robust choice against less-powerful adversaries.

- **Optimizing Parameters:** When configuring Argon2id parameters, follow the recommended order:
  1. Maximize memory usage with a time cost parameter of 1.
  2. Increase parallelism up to the number of CPU cores.
  3. Increase the time cost parameter while ensuring it does not lower the previous parameters.

- **Practical Considerations:** Ensure the parameters do not exceed the system resources (RAM, CPU cores) or make the hashing process too slow for user experience.



## Why Use This?
If you're developing web applications that require user authentication, this library offers an adaptive, secure, and efficient solution for password hashing. It's particularly beneficial in environments where system resources vary or are constrained. By intelligently adjusting its operations based on available resources, it provides an optimal balance between security and performance.

## Contribution
Contributions are welcome! Whether you're fixing bugs, improving the documentation, or adding new features, your help is appreciated to make this library even better.

Thank you for considering [Adaptive Security: Dynamic Hashing Based on System Resources] for your password hashing needs. Together, we can make web applications more secure and efficient.
