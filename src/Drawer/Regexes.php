<?php

namespace Livewire\Drawer;

class Regexes
{
    static $livewireOpeningTag = "
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
                            @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            (\:\\\$)(\w+)
                        )
                        |
                        (?:
                            [\w\-:.@%]+
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

    static $livewireOpeningTagOrSelfClosingTag = "
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
                            @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            (\:\\\$)(\w+)
                        )
                        |
                        (?:
                            [\w\-:.@%]+
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

    static $livewireSelfClosingTag = "
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
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                (\:\\\$)(\w+)
                            )
                            |
                            (?:
                                [\w\-:.@%]+
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

    static $livewireClosingTag = '<\/\s*livewire[-\:][\w\-\:\.]*\s*>';

    static $slotOpeningTag = "
        <
            \s*
            livewire[\-\:]slot
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
                            @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                        )
                        |
                        (?:
                            \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                        )
                        |
                        (?:
                            (\:\\\$)(\w+)
                        )
                        |
                        (?:
                            [\w\-:.@%]+
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

    static $slotClosingTag = '<\/\s*livewire[\-\:]slot[^>]*>';

    static $bladeDirective = "\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?";

    static function specificBladeDirective($directive) {
        return "(@?$directive(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))";
    }
}
