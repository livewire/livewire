<?php

namespace Livewire\Features\SupportChecksumErrorDebugging;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SupportChecksumErrorDebugging
{
    function boot()
    {
        // @todo: dont write to this file unless the command is running...
        return;

        $file = storage_path('framework/cache/lw-checksum-log.json');

        Artisan::command('livewire:monitor-checksum', function () use ($file) {
            File::put($file, json_encode(['checksums' => [], 'failure' => null]));

            $this->info('Monitoring for checksum errors...');

            while (true) {
                $cache = json_decode(File::get($file), true);

                if ($cache['failure']) {
                    $this->info('Failure: '.$cache['failure']);

                    $cache['failure'] = null;
                }

                File::put($file, json_encode($cache));

                sleep(1);
            }

        })->purpose('Debug checksum errors in Livewire');

        on('checksum.fail', function ($checksum, $comparitor, $tamperedSnapshot) use ($file) {
            $cache = json_decode(File::get($file), true);

            if (! isset($cache['checksums'][$checksum])) return;

            $canonicalSnapshot = $cache['checksums'][$checksum];

            $good = $this->array_diff_assoc_recursive($canonicalSnapshot, $tamperedSnapshot);
            $bad = $this->array_diff_assoc_recursive($tamperedSnapshot, $canonicalSnapshot);

            $cache['failure'] = "\nBefore: ".json_encode($good)."\nAfter: ".json_encode($bad);

            File::put($file, json_encode($cache));
        });

        on('checksum.generate', function ($checksum, $snapshot) use ($file) {
            $cache = json_decode(File::get($file), true);

            $cache['checksums'][$checksum] = $snapshot;

            File::put($file, json_encode($cache));
        });
    }

    // https://www.php.net/manual/en/function.array-diff-assoc.php#111675
    function array_diff_assoc_recursive($array1, $array2) {
        $difference=array();

        foreach($array1 as $key => $value) {
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }
}
