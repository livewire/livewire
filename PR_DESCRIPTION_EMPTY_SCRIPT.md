# Fix: Multi-File Components Generate Empty Script Files

## Problem

The `MultiFileParser::generateScriptContents()` method always returns a non-null value, even when multi-file components don't have a JavaScript file. This causes unnecessary empty script files to be generated and served, triggering JavaScript module loading errors in the browser.

### Root Cause

When a multi-file component has no `.js` file:
1. `$this->scriptPortion` is set to `null` (line 36 in `parse()` method)
2. `generateScriptContents()` calls `trim($this->scriptPortion)` 
3. `trim(null)` returns an empty string `""` (not `null`)
4. The method returns a non-null heredoc string with empty content
5. The compiler detects non-null content and writes a script file
6. `scriptModuleSrc()` method is injected into the compiled class
7. The frontend tries to dynamically import the script, which returns 500 or 404

### Error in Browser

```
GET /livewire/js/component-name.js?v=... net::ERR_ABORTED 404 (Not Found)
Uncaught (in promise) TypeError: Failed to fetch dynamically imported module
```

### Example Component That Fails

A simple multi-file component without JavaScript:

```
resources/views/components/my-component/
├── my-component.php          # PHP class
├── my-component.blade.php    # Blade template
└── (no .js file)              # ← No JavaScript needed
```

**Current behavior**: Livewire generates an empty script file and tries to load it in the browser, causing errors.

**Expected behavior**: No script file should be generated, and no JavaScript loading should be attempted.

## Solution

Add an early return in `generateScriptContents()` to check if the script portion is null or empty before generating the wrapper:

```php
public function generateScriptContents(): ?string
{
    // Return null if there's no script content
    if ($this->scriptPortion === null || trim($this->scriptPortion) === '') {
        return null;
    }

    $scriptContents = trim($this->scriptPortion);

    return <<<JS
    export function run(\$wire, \$js) {
        {$scriptContents}
    }
    JS;
}
```

This ensures:
- Components without `.js` files return `null`
- The compiler skips script file generation (`if ($scriptContents !== null)` check on line 52 of `Compiler.php`)
- No `scriptModuleSrc()` method is injected into the compiled class
- The frontend doesn't attempt to load non-existent JavaScript files

## Testing

### Before Fix
1. Create a multi-file component without a `.js` file
2. Render the component on a page
3. Browser console shows: `Failed to fetch dynamically imported module: /livewire/js/component-name.js`

### After Fix
1. Create a multi-file component without a `.js` file
2. Render the component on a page
3. ✅ No JavaScript errors
4. ✅ No unnecessary HTTP requests for script files

### Components With JavaScript Still Work
1. Create a multi-file component WITH a `.js` file containing valid code
2. The script loads and executes correctly
3. `$wire` and `$js` are available in the script context

## Related Issues

- Affects Livewire v4 beta when using multi-file components without JavaScript
- Similar to the OPcache issue where compiled files are cached in memory
- Part of the multi-file component architecture improvements

## PHP Version Compatibility

The fix also addresses a deprecation warning in PHP 8.1+:

```
DEPRECATED: trim(): Passing null to parameter #1 ($string) of type string is deprecated
```

By checking for `null` before calling `trim()`, we avoid this deprecation warning.

