<?php

namespace Livewire\Drawer;

use DOMDocument;
use Livewire\Exceptions\RootTagMissingFromViewException;

use function Livewire\invade;

class Utils extends BaseUtils
{
    static function insertAttributesIntoHtmlRoot($html, $attributes, $override) {
        preg_match('/(?:\n\s*|^\s*)(<([a-zA-Z0-9\-]+).*?>)/', $html, $matches, PREG_OFFSET_CAPTURE);

        throw_unless(count($matches), new RootTagMissingFromViewException);

        $openingTag = $matches[0][0];
        $closingTagName = $matches[2][0];
        $closingTag = '</'.$closingTagName.'>';
        $fauxRootTag = $openingTag.$closingTag;

        $doc = new \DOMDocument;
        $doc->formatOutput = false;
        $doc->loadHTML(
            // Give DOMDocument will mess with the encoding...
            mb_convert_encoding($fauxRootTag, 'HTML-ENTITIES', 'UTF-8'),
            // Prevent DOMDocument from adding its own tags...
            LIBXML_HTML_NODEFDTD|LIBXML_HTML_NOIMPLIED
        );
        $fauxRootElement = $doc->documentElement;

        foreach ($attributes as $key => $value) {
            if (! $override && $fauxRootElement->hasAttribute($key)) continue;

            if (! (is_string($value) || is_numeric($value))) $value = json_encode($value);

            // Take out single and double quotes and replace the back at the end.
            // The reason is because DOMDocument will not encode quotes.
            // If we encode double quotes, it will double encode the "&" used in the encoding...
            // If we don't AND the content contains a double quote, rather than encoding it,
            // the output attribute will use single quotes which we don't want: foo='..."...'
            $value = str($value)->replace("'", '--single--')->replace('"', '--double--');

            $value = (string) str($value)->replace("'", '');

            $fauxRootElement->setAttribute($key, $value);
        }

        $output = $doc->saveHTML();

        $newOpeningTag = (string) str($output)
            ->replace('--single--', '&#039;')
            ->replace('--double--', '&quot;')
            ->beforeLast($closingTag);

        return (string) str($html)->replaceFirst($openingTag, $newOpeningTag);
    }

    static function escapeStringForHtml($subject)
    {
        if (is_string($subject) || is_numeric($subject)) {
            return htmlspecialchars($subject, ENT_QUOTES|ENT_SUBSTITUTE);
        }

        return htmlspecialchars(json_encode($subject), ENT_QUOTES|ENT_SUBSTITUTE);
    }

    static function pretendResponseIsFile($file, $mimeType = 'application/javascript')
    {
        $expires = strtotime('+1 year');
        $lastModified = filemtime($file);
        $cacheControl = 'public, max-age=31536000';

        if (static::matchesCache($lastModified)) {
            return response()->make('', 304, [
                'Expires' => static::httpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        return response()->file($file, [
            'Content-Type' => "$mimeType; charset=utf-8",
            'Expires' => static::httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => static::httpDate($lastModified),
        ]);
    }

    static function matchesCache($lastModified)
    {
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

        return @strtotime($ifModifiedSince) === $lastModified;
    }

    static function httpDate($timestamp)
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }

    static function containsDots($subject)
    {
        return strpos($subject, '.') !== false;
    }

    static function dotSegments($subject)
    {
        return explode('.', $subject);
    }

    static function beforeFirstDot($subject)
    {
        return head(explode('.', $subject));
    }

    static function afterFirstDot($subject) : string
    {
        return str($subject)->after('.');
    }

    static public function hasProperty($target, $property)
    {
        return property_exists($target, static::beforeFirstDot($property));
    }

    static public function shareWithViews($name, $value)
    {
        $old = app('view')->shared($name, 'notfound');

        app('view')->share($name, $value);

        return $revert = function () use ($name, $old) {
            if ($old === 'notfound') {
                unset(invade(app('view'))->shared[$name]);
            } else {
                app('view')->share($name, $old);
            }
        };
    }

    static function anonymousClassToStringClass($target, $class, $namespace = null)
    {
        $raw = file((new \ReflectionObject($target))->getFilename());
        $start = (new \ReflectionObject($target))->getStartLine();
        $end = (new \ReflectionObject($target))->getEndLine();

        $firstLine = $raw[$start - 1];
        $suffix = (string) str($firstLine)->between('new class ', '{');

        $uses = '';

        foreach ($raw as $line) {
            if (str_starts_with($line, 'use ')) {
                $uses .= $line;
            }
        }

        $body = '';
        foreach (range($start, $end - 2) as $line) {
            $body .= $raw[$line];
        }

        $namespace = $namespace ? 'namespace '.$namespace.';' : '';

        return <<<PHP
<?php

$namespace

$uses

class $class $suffix {
$body
}
PHP;
    }
}
