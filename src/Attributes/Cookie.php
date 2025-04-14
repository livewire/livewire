<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportCookie\BaseCookie;

/**
 * This class is designed to provide sensible defaults for cookie handling while
 * ensuring security best practices. Developers can customize the behavior, but
 * they should be cautious about potential risks when deviating from these defaults.
 *
 * **Important Notes:**
 * - **Do not use cookies to store sensitive data** (e.g., passwords, personal information, or data subject to GDPR or other regulations).
 * - Cookies are inherently less secure than server-side storage. Always consider alternative storage mechanisms for critical data.
 *
 * **Default Values and Security Considerations:**
 * - `$secure`: Defaults to `true`, ensuring cookies are sent only over HTTPS connections. This helps prevent data interception on insecure networks.
 * - `$httpOnly`: Defaults to `true`, making cookies inaccessible to JavaScript and mitigating the risk of XSS attacks.
 * - `$sameSite`: Defaults to `'Lax'`, providing protection against CSRF attacks while maintaining usability in most cases.
 * - `$minutes`: Defaults to 30 days (60 * 24 * 30). For shorter-lived sessions, reduce this value as needed.
 *
 * **Usage Recommendations:**
 * - Always use HTTPS in your application to maximize the effectiveness of the `Secure` flag.
 * - Avoid using this mechanism for storing sensitive or long-term authentication tokens.
 * - If custom values are set for `$secure`, `$httpOnly`, or `$sameSite`, ensure they align with your application's security policies.
 *
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Cookie extends BaseCookie
{
    //
}
