<?php

namespace Livewire\Mechanisms\HandleComponents;

class SecurityPolicy
{
    /**
     * Classes that should never be instantiated by Livewire synthesizers.
     * These are known-dangerous classes that could be exploited if an attacker
     * somehow bypassed the checksum protection.
     */
    protected static $deniedClasses = [
        // Console commands - could execute arbitrary system commands
        'Illuminate\Console\Command',
        'Symfony\Component\Console\Command\Command',

        // Process execution - direct system command execution
        'Symfony\Component\Process\Process',

        // Known serialization gadgets
        'Illuminate\Broadcasting\PendingBroadcast',
        'Illuminate\Foundation\Testing\PendingCommand',

        // Queue jobs - could execute arbitrary code
        'Illuminate\Queue\CallQueuedClosure',

        // Notifications - could send arbitrary notifications
        'Illuminate\Notifications\Notification',
    ];

    /**
     * Validate that a class is safe to instantiate.
     * Throws an exception if the class is in the denylist.
     */
    public static function validateClass(string $class): void
    {
        foreach (static::$deniedClasses as $denied) {
            if (is_a($class, $denied, true)) {
                throw new \Exception("Livewire: Class [{$class}] is not allowed to be instantiated.");
            }
        }
    }

    /**
     * Add classes to the denylist at runtime.
     */
    public static function denyClasses(array $classes): void
    {
        static::$deniedClasses = array_merge(static::$deniedClasses, $classes);
    }

    /**
     * Get the current denylist (useful for testing).
     */
    public static function getDeniedClasses(): array
    {
        return static::$deniedClasses;
    }
}
