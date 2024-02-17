# [Adaptive Security: Dynamic Hashing Based on System Resources]

## Description
This is designed to dynamically adjust hashing parameters, including memory usage and computational complexity, based on the available system resources. This approach ensures that applications can offer the highest level of security without overburdening the system, making it ideal for a wide range of deployment environments, from shared hosting to high-end servers.

By default, the maximum ram usage is 384mb (128mb * 3)

You can modify this by changing this `min($availableMemoryKiB, $MemoryCostDefault * 3)`

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
