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
- **Execution Time:** Varies with the configuration, from 482ms at the highest setting (384MB RAM, 6 iterations, 8 threads) to 100ms at the baseline (128MB RAM, 4 iterations, 4 threads). For 200MB of available RAM, the function maintains 4 iterations and 4 threads, resulting in a 168ms execution time.
- **Efficiency Across Hardware:** Matches the function's demand with the system's capabilities, ensuring optimal efficiency. This design choice prevents overloading less powerful machines while fully utilizing the capabilities of more robust systems.
- **Fallback Mechanism:** Defaults to BCRYPT if the memory cost calculation suggests a value below 32MB or if Argon2id is not available, ensuring compatibility across various platforms and configurations.

## Security Insights

For those concerned about the capabilities of attackers, it's noteworthy that as of 2021, cracking software is generally not keeping pace with advancements in hashing algorithms. Particularly, there is no efficient Argon2 cracker available, making it a robust choice against less-powerful adversaries.

### Optimizing Argon2id Parameters
When configuring Argon2id, the parameter adjustment should follow a specific order to maximize security:

1. **Maximize Memory Usage:** Begin with increasing memory usage as much as possible, starting with a time cost parameter of 1. This makes the hashing process more resistant to brute-force attacks by requiring significant memory resources to attempt cracking.

2. **Increase Parallelism:** Then, increase the parallelism up to the number of cores available on your system. This should be done without reducing the memory usage set in the previous step, leveraging multi-core processors to enhance performance without compromising security.

3. **Adjust Time Cost:** Finally, increase the time cost as much as possible without affecting the previously set parameters. This lengthens the computation time for each hash, further securing against brute-force attempts.

For more in-depth discussions and recommendations on Argon2id parameter selection, refer to discussions on security forums, such as [this detailed thread on Security Stack Exchange](https://security.stackexchange.com/questions/247936/since-gpus-have-gigabytes-of-memory-does-argon2id-need-to-use-gigabytes-of-memo).

## Features
- **Dynamic Adjustment:** Automatically scales hashing parameters based on the system's memory_limit setting.
- **Resource Efficient:** Optimizes for both low-end and high-end systems, ensuring best use of available resources.
- **Fallback Mechanism:** Defaults to BCRYPT for lower memory configurations or when Argon2id is not available, ensuring wide compatibility.
- **Enhanced Security:** Offers the potential for stronger security on systems capable of supporting higher resource usage.

## Why Use This?
If you're developing web applications that require user authentication, this library offers an adaptive, secure, and efficient solution for password hashing. It's particularly beneficial in environments where system resources vary or are constrained. By intelligently adjusting its operations based on available resources, it provides an optimal balance between security and performance.

## Contribution
Contributions are welcome! Whether you're fixing bugs, improving the documentation, or adding new features, your help is appreciated to make this library even better.

Thank you for considering [Adaptive Security: Dynamic Hashing Based on System Resources] for your password hashing needs. Together, we can make web applications more secure and efficient.
