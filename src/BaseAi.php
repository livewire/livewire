<?php

namespace Livewire;

class BaseAi
{
    function __call($method, $params)
    {
        return $this->_generateMethod($this, $method, $params);
    }

    static function __callStatic($method, $params)
    {
        return (new static)->_generateMethod(new static, $method, $params);
    }

    function _generateMethod($caller, $method, $params)
    {
        $context = $this->_getCallingContext($method);

        $body = $this->_fetchFunctionBodyFromOpenAi($method, $context);

        $this->_addMethodToClassForNextRequest($caller, $body);

        $runtimeProxy = $this->_generateRuntimeProxy($body);

        return $runtimeProxy->$method(...$params);
    }

    function _getCallingContext($method) {
        foreach (debug_backtrace(limit: 5) as $trace) {
            if ($trace['function'] === '__callStatic' && str($trace['class'])->endsWith(self::class) && $trace['args'][0] === $method) {
                $file = $trace['file'];
                $line = $trace['line'];

                return file_get_contents($file);
            }
        }
    }

    function _fetchFunctionBodyFromOpenAi($method, $context)
    {
        $key = env('OPENAI_KEY', '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');

        $response = $this->_callOpenAi($key, [
            'model' => 'code-davinci-002',
            'prompt' => <<<PHP
            // The language is PHP and the framework is Laravel
            // Please write the source code for the static function call to Ai::$method
            $context

            class Ai {
                public statuc function
            PHP,
            'suffix' => <<<PHP
                }
            }

            ?>
            PHP,
            'temperature' => 0,
            'max_tokens' => 1000,
        ]);

        if (empty($response['choices'])) throw new \Exception('No choices generated...');

        ['text' => $text, 'finish_reason' => $reason] = $response['choices'][0];

        if ($reason !== 'stop') throw new \Exception('Generated incomplete answer, maybe reached token limit?');

        if (empty($text)) throw new \Exception('Could not generate function body...');

        return trim($text);
    }

    function _callOpenAi($key, $options)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/completions',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => json_encode($options),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$key,
            ],
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 0,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, associative: true);
    }

    function _addMethodToClassForNextRequest($obj, $body)
    {
        $file = (new \ReflectionClass($obj))->getFileName();

        $contents = file_get_contents($file);

        $withNewMethod = (string) str($contents)->replaceLast('}', <<<PHP

            public static function $body
            } // __END__
        }
        PHP);

        file_put_contents($file, $withNewMethod);
    }

    function _generateRuntimeProxy($body)
    {
        return eval(<<<PHP
            return new class {
                public function $body
                }
            };
        PHP);
    }
}
