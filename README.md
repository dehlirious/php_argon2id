# [Adaptive Security: Dynamic Hashing Based on System Resources]

<p align="center">
    <a href="https://badgen.net/github/commits/dehlirious/php_argon2id">
        <img src="https://badgen.net/github/commits/dehlirious/php_argon2id" alt="GitHub commits" />
    </a>
    <a href="https://GitHub.com/dehlirious/php_argon2id/stargazers/">
        <img src="https://badgen.net/github/stars/dehlirious/php_argon2id" alt="GitHub stars" />
    </a>
    <a href="https://GitHub.com/dehlirious/php_argon2id/network/">
        <img src="https://badgen.net/github/forks/dehlirious/php_argon2id/" alt="GitHub forks" />
    </a>
    <a href="https://GitHub.com/dehlirious/php_argon2id/watchers/">
        <img src="https://badgen.net/github/watchers/dehlirious/php_argon2id/" alt="GitHub watchers" />
    </a>
    <a href="https://GitHub.com/dehlirious/php_argon2id/pull/">
        <img src="https://badgen.net/github/prs/dehlirious/php_argon2id" alt="GitHub total pull requests" />
    </a>
    <a href="https://github.com/dehlirious/php_argon2id/issues">
        <img src="https://badgen.net/github/issues/dehlirious/php_argon2id" alt="GitHub issues" />
    </a>
    <a href="http://isitmaintained.com/project/dehlirious/php_argon2id">
        <img src="http://isitmaintained.com/badge/open/dehlirious/php_argon2id.svg" alt="Percentage of issues still open" />
    </a>
    <!-- Releases and Downloads -->
    <a href="https://github.com/dehlirious/php_argon2id/releases">
        <img src="https://img.shields.io/github/downloads/dehlirious/php_argon2id/total.svg" alt="GitHub downloads" />
    </a>
    <a href="https://github.com/dehlirious/php_argon2id/releases">
        <img src="https://badgen.net/github/releases/dehlirious/php_argon2id" alt="GitHub release" />
    </a>
    <br/>
    <!-- Support -->
    <a href="https://buymeacoffee.com/devsir">
        <img src="https://badgen.net/badge/icon/buymeacoffee?icon=buymeacoffee&label" alt="Buymeacoffee" />
    </a>
</p>





## Description
This is designed to dynamically adjust hashing parameters, including memory usage and computational complexity, based on the available system resources. This approach ensures that applications can offer the highest level of security without overburdening the system, making it ideal for a wide range of deployment environments, from shared hosting to high-end servers.

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

## Explanation of Parameters
The `hashPassword` function offers flexibility, allowing customization to suit the specific requirements of your application. Below is an overview of the configurable parameters and their impact on the hashing process:

- **defaultMemory:** Initial memory allocation for hashing, defaulting to 128MB. This parameter determines the amount of memory allocated for the hashing process, impacting both performance and security.
- **maxMemory:** Maximum memory allocation for hashing, defaulted to 384MB. This parameter sets the upper limit for memory usage during hashing, balancing security with resource consumption.
- **defaultThreads:** Default number of threads used in hashing, set to 4. Threads affect the parallelism of the hashing process, influencing performance on multi-core systems.
- **maxThreads:** Maximum number of threads for hashing, capped at 8. This parameter limits the concurrency of the hashing process to prevent resource exhaustion.
- **defaultIterations:** Default number of hashing iterations, starting at 4. Iterations determine the computational complexity of the hashing process, impacting security.
- **maxIterations:** Maximum number of hashing iterations, up to 6. Increasing iterations enhances security but may also prolong execution time.
- **memoryCutoff:** Memory usage threshold for algorithm switch, set at 32MB. This parameter defines the point at which the function switches to a fallback hashing algorithm for compatibility and resource efficiency.

## Performance Considerations
Adjusting hashing parameters, such as memory usage and iteration counts, can have significant performance implications. Here are some insights to consider when configuring the function for your applications:

- **Memory Usage:** Increasing the memory allocation (`defaultMemory` and `maxMemory`) can improve performance by allowing the hashing process to utilize more resources efficiently. However, excessive memory usage may lead to resource contention and performance degradation on shared hosting environments.
- **Iteration Counts:** Higher iteration counts (`iterations` and `maxIterations`) enhance security by increasing the computational complexity of the hashing process. However, this comes at the cost of increased execution time, particularly on systems with limited computational resources.
- **Threads:** Adjusting the number of threads (`defaultThreads` and `maxThreads`) can impact performance by influencing parallelism and concurrency in the hashing process. More threads can accelerate hashing but may also consume additional CPU resources and introduce overhead in thread management.

Consider your application's requirements and the available system resources when adjusting hashing parameters to strike a balance between security and performance.


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

## Security Considerations

When implementing password hashing in your applications, it's crucial to adhere to security best practices to safeguard user credentials. Here are some essential considerations:

- **Use a Strong Hashing Algorithm:** Choose a secure and proven hashing algorithm like Argon2 or bcrypt. These algorithms are specifically designed for password hashing and offer resistance against brute-force and dictionary attacks.

- **Salting Passwords:** Always use a unique salt for each password before hashing. Salting adds random data to each password, making it more difficult for attackers to use precomputed tables (rainbow tables) for cracking passwords.

- **Secure Storage:** Ensure that hashed passwords are stored securely in your database. Implement proper access controls and encryption mechanisms to protect the password database from unauthorized access.

- **Authentication System Integration:** Integrate the hashed passwords into your authentication system securely. Implement strong password policies, multi-factor authentication, and session management to enhance overall security.

- **Regularly Update Password Hashing Parameters:** Periodically review and update the hashing parameters (such as memory usage, iteration counts, and thread counts) based on evolving security requirements and system capabilities.

By following these security best practices, you can significantly improve the security of your application's authentication system and protect user passwords from unauthorized access.


## To-Do
- Implement dynamic cost for BCRYPT.
- Test function compatibility and performance across different PHP versions and environments
- Implement logging for debug information instead of echoing directly
- Profile and optimize critical sections of the code for better performance
- Change the calculation of determining thread/iteration count. These two do not effect memory consumption yet are based off memory.
- Create a 'verifyAndRehashPassword' function that replaces usage of password_verify, to call password_needs_rehash to determine whether or not rehashPassword needs to be called, then calling password_verify and returning the result.

## Why Use This?

In the realm of web application development, robust user authentication is paramount to safeguarding sensitive user data. This library offers a compelling solution for password hashing, designed to adapt dynamically to varying system resources while maintaining stringent security measures. Here's why you should consider using this library:

- **Adaptive Security:** With the ability to dynamically adjust hashing parameters based on available system resources, this library ensures that your application's security remains resilient across different environments.

- **Efficient Resource Utilization:** By intelligently optimizing its operations, this library strikes a balance between security and performance, making it ideal for applications operating in resource-constrained environments or with fluctuating resource availability.

- **Ease of Integration:** Integrating this library into your web application is straightforward, providing a hassle-free solution for implementing robust password hashing functionality.

- **Future-Proof Security:** The library incorporates industry-standard hashing algorithms like Argon2 and bcrypt, ensuring that your application remains resilient against evolving security threats and vulnerabilities.

By leveraging the adaptive, secure, and efficient features of this library, you can enhance the security posture of your web applications while delivering a seamless user experience.

## Contribution
Contributions are welcome! Whether you're fixing bugs, improving the documentation, or adding new features, your help is appreciated to make this project even better.

Together, we can make web applications more secure and efficient.
