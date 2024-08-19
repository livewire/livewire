<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use function Laravel\Prompts\{ info, text, note, spin, warning, error, alert, intro, outro, suggest };
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 *
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 * If you found this command, can you plz not share your findings all over the internet? Thanks - Caleb
 */
#[AsCommand(name: 'flux:install')]
class FluxInstallCommand extends Command
{
    protected $signature = 'flux:install {key?}';

    protected $description = 'Install and activate Flux';

    protected $hidden = true;

    public function handle()
    {
        $key = $this->argument('key');

        if ($key) {
            $this->installFlux($key);
        } else {
            $key = text(
                label: 'Enter your license key',
                hint: 'Purchase a license key: https://fluxui.dev/purchase',
                required: true,
            );

            $this->installFlux($key);
        }
    }

    public function installFlux($key)
    {
        $hashKey = app('encrypter')->getKey();

        $fingerprint = hash_hmac('sha256', json_encode($key), $hashKey);

        $response = spin(
            message: 'Activating your license...',
            callback: fn () => Http::post('https://fluxui.dev/api/activate', [ 'key' => $key, 'fingerprint' =>  $fingerprint ]),
        );

        if ($response->failed() && $response->json('error') === 'not-found') {
            warning('Invalid license key');
            note('Contact support@fluxui.dev for help');
            return;
        } elseif ($response->failed()) {
            $response->throw();
            alert('Failed to activate license');
            note('Contact support@fluxui.dev for help');
            return;
        }

        $key = (string) $response->json('key');
        $email = (string) $response->json('email');
        $expiresAt = Carbon::parse($response->json('expires_at'));

        if ($key === '' || $email === '') {
            error('Whoops, something went wrong. Either your license key or email is invalid.');
            note('Contact support@fluxui.dev for help');
            return;
        }

        // Add creds to auth.json...
        $process = new Process([
            'composer', 'config', '-a',
            'http-basic.flux.composer.sh', $email, $key
        ]);
        $process->run();

        if (! $process->isSuccessful()) {
            echo "Failed to add license to auth.json. Console output: " . $process->getErrorOutput();
            note('Contact support@fluxui.dev for help');
            return;
        }

        info('[√] License key added to auth.json');

        // Add repository to composer.json...
        $process = new Process(['composer', 'config', 'repositories.flux', 'composer', 'https://flux.composer.sh']);
        $process->run();

        if (! $process->isSuccessful()) {
            echo "Failed to add repository to composer.json. Console output: " . $process->getErrorOutput();
            note('Contact support@fluxui.dev for help');
            return;
        }

        info('[√] Repository added to composer.json');

        // Run composer require...
        note('Running: composer require livewire/flux...');

        $process = new Process(['composer', 'require', 'livewire/flux']);
        $process->setTty(true);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (! $process->isSuccessful()) {
            error("We are unable to install Flux automatically. Try running `composer require livewire/flux` manually.");
            note('Contact support@fluxui.dev for help');
            return;
        }

        if ($expiresAt->isPast()) {
            note('');
            warning('This license has expired. You will need to purchase a new license to receive updates.');
            note('Extend your license here: https://fluxui.dev/licenses');
        }

        note('');
        outro('Thanks for using Flux!');
        note('Your support is an investment in the future of Livewire ❤️');
    }
}
