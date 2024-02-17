<?php


// $template = <<<'HTML'
//     <div @if($other > 5) disabled @endif> 
//         @if (($someProperty) > 0) 
//             <span> {{ $someProperty }} </span>
//         @endif

//         <div>
//             @if (($someProperty) > 0) 
//                 <span> {{ $someProperty }} </span>
//             @endif
//         </div>
//     </div>
// HTML;

// ray()->clearScreen();

// ray("template", $template);

// $newTemplate = compileStatements($template);

// ray("newTemplate", $newTemplate);

// function compileStatements($template)
// {
//     preg_match_all(
//         '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x',
//         $template,
//         $matches
//     );

//     // ray($matches);

//     $offset = 0;

//     for ($i = 0; isset($matches[0][$i]); $i++) {
//         $match = [
//             $matches[0][$i],
//             $matches[1][$i],
//             $matches[2][$i],
//             $matches[3][$i] ?: null,
//             $matches[4][$i] ?: null
//         ];

//         // Here we check to see if we have properly found the closing parenthesis by
//         // regex pattern or not, and will recursively continue on to the next ")"
//         // then check again until the tokenizer confirms we find the right one.
//         while (
//             isset($match[4]) &&
//             str($match[0])->endsWith(")") &&
//             !hasEvenNumberOfParentheses($match[0])
//         ) {
//             if (($after = str($template)->after($match[0])) === $template) {
//                 break;
//             }

//             $rest = str($after)->before(")");

//             if (
//                 isset($matches[0][$i + 1]) &&
//                 str($rest . ")")->contains($matches[0][$i + 1])
//             ) {
//                 unset($matches[0][$i + 1]);
//                 $i++;
//             }

//             $match[0] = $match[0] . $rest . ")";
//             $match[3] = $match[3] . $rest . ")";
//             $match[4] = $match[4] . $rest;
//         }

//         if (str($match[0])->startsWith("@if")) {
//             // ray("startsWithIf", $match[0], $template);

//             $found = $match[0];

//             $foundEscaped = preg_quote($match[0]);

//             $prefix = "<!--[if BLOCK]><![endif]-->";

//             $prefixEscaped = preg_quote($prefix);

//             $foundWithPrefix = $prefix . $found;

//             // ray($foundWithPrefix);

//             $pattern = "/(?<!$prefixEscaped)$foundEscaped(?![^<]*(?<![?=-])>)/";

//             // ray("patterns", $pattern, $foundWithPrefix, $template);

//             $template = preg_replace($pattern, $foundWithPrefix, $template);

//             // ray("matching",$template);
//         }

//         // [$template, $offset] = $this->replaceFirstStatement(
//         //     $match[0],
//         //     $this->compileStatement($match),
//         //     $template,
//         //     $offset
//         // );
//     }

//     return $template;
// }

// function hasEvenNumberOfParentheses(string $expression)
// {
//     $tokens = token_get_all("<?php " . $expression);

//     if (Arr::last($tokens) !== ")") {
//         return false;
//     }

//     $opening = 0;
//     $closing = 0;

//     foreach ($tokens as $token) {
//         if ($token == ")") {
//             $closing++;
//         } elseif ($token == "(") {
//             $opening++;
//         }
//     }

//     return $opening === $closing;
// }