<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Console\Command;
use Livewire\Mechanisms\HandleComponents\SecurityPolicy;

class SecurityPolicyUnitTest extends \Tests\TestCase
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

    public function test_rejects_broadcast_event_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(\Illuminate\Broadcasting\BroadcastEvent::class);
    }

    public function test_rejects_queue_job_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(\Illuminate\Queue\Jobs\Job::class);
    }

    public function test_rejects_mailable_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass(\Illuminate\Mail\Mailable::class);
    }

    public function test_rejects_guzzle_fn_stream_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass('GuzzleHttp\Psr7\FnStream');
    }

    public function test_rejects_flysystem_sharded_prefix_url_generator_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass('League\Flysystem\UrlGeneration\ShardedPrefixPublicUrlGenerator');
    }

    public function test_rejects_laravel_prompts_terminal_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not allowed to be instantiated');

        SecurityPolicy::validateClass('Laravel\Prompts\Terminal');
    }

    public function test_get_denied_classes_returns_list()
    {
        $denied = SecurityPolicy::getDeniedClasses();

        $this->assertIsArray($denied);
        $this->assertContains('Illuminate\Console\Command', $denied);
        $this->assertContains('Symfony\Component\Process\Process', $denied);
        $this->assertContains('Illuminate\Broadcasting\BroadcastEvent', $denied);
        $this->assertContains('Illuminate\Queue\Jobs\Job', $denied);
        $this->assertContains('Illuminate\Mail\Mailable', $denied);
        $this->assertContains('GuzzleHttp\Psr7\FnStream', $denied);
        $this->assertContains('League\Flysystem\UrlGeneration\ShardedPrefixPublicUrlGenerator', $denied);
        $this->assertContains('Laravel\Prompts\Terminal', $denied);
    }
}
