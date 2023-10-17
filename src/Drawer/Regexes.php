<?php

namespace Livewire\Drawer;

class Regexes
{
    public static $livewireOpeningTag = "
        <
            \s*
            livewire[-\:]([\w\-\:\.]*)
            (?<attributes>
                (?:
                    \s+
                    (?:
                        (?:
                            @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            [\w\-:.@]+
                            (
                                =
                                (?:
                                    \\\"[^\\\"]*\\\"
                                    |
                                    \'[^\']*\'
                                    |
                                    [^\'\\\"=<>]+
                                )
                            )?
                        )
                    )
                )*
                \s*
            )
            (?<![\/=\-])
        >
    ";

    public static $livewireOpeningTagOrSelfClosingTag = "
        <
            \s*
            livewire[-\:]([\w\-\:\.]*)
            (?<attributes>
                (?:
                    \s+
                    (?:
                        (?:
                            @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            [:][$][\w]+
                        )
                        |
                        (?:
                            [\w\-:.@]+
                            (
                                =
                                (?:
                                    \\\"[^\\\"]*\\\"
                                    |
                                    \'[^\']*\'
                                    |
                                    [^\'\\\"=<>]+
                                )
                            )?
                        )
                    )
                )*
                \s*
            )
        \/?>
    ";

    public static $livewireSelfClosingTag = "
        <
            \s*
                livewire[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                \s*
            )
        \/>
    ";

    public static $livewireClosingTag = "<\/\s*livewire[-\:][\w\-\:\.]*\s*>";

    public static $slotOpeningTag = "
        <
            \s*
            x[\-\:]slot
            (?:\:(?<inlineName>\w+(?:-\w+)*))?
            (?:\s+(:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+)))?
            (?<attributes>
                (?:
                    \s+
                    (?:
                        (?:
                            @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            [\w\-:.@]+
                            (
                                =
                                (?:
                                    \\\"[^\\\"]*\\\"
                                    |
                                    \'[^\']*\'
                                    |
                                    [^\'\\\"=<>]+
                                )
                            )?
                        )
                    )
                )*
                \s*
            )
            (?<![\/=\-])
        >
    ";

    public static $slotClosingTag = "<\/\s*x[\-\:]slot[^>]*>";

    public static $bladeDirective = "\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?";

    public static function specificBladeDirective($directive)
    {
        return "(@?$directive(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))";
    }
}
