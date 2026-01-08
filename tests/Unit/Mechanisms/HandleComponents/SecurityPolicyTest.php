<?php

namespace Tests\Unit\Mechanisms\HandleComponents;

use Illuminate\Console\Command;
use Livewire\Mechanisms\HandleComponents\SecurityPolicy;

class SecurityPolicyTest extends \Tests\TestCase
{
    public function test_validates_safe_classes()
    {
        // Should not throw for regular classes
        SecurityPolicy::validateClass(\stdClass::class);
        SecurityPolicy::validateClass(\DateTime::class);

        $this->assertTrue(true);
    }

    public function test_rejects_console_command_classes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(Command::class);
    }

    public function test_rejects_subclasses_of_denied_classes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        // Create an anonymous subclass of Command
        $subclass = get_class(new class extends Command {
            protected $signature = 'test';
        });

        SecurityPolicy::validateClass($subclass);
    }

    public function test_rejects_process_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(\Symfony\Component\Process\Process::class);
    }

    public function test_can_add_classes_to_denylist()
    {
        SecurityPolicy::denyClasses([\DateTimeImmutable::class]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(\DateTimeImmutable::class);
    }

    public function test_get_denied_classes_returns_list()
    {
        $denied = SecurityPolicy::getDeniedClasses();

        $this->assertIsArray($denied);
        $this->assertContains('Illuminate\Console\Command', $denied);
        $this->assertContains('Symfony\Component\Process\Process', $denied);
    }
}
