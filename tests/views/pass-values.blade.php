@php( $empty = '' )
@php( $nonEmpty = 'abc' )

tag-syntax-literal-empty: <livewire:accepts-values value=""/>
tag-syntax-literal-non-empty: <livewire:accepts-values value="abc"/>
tag-syntax-value-empty: <livewire:accepts-values :value="$empty"/>
tag-syntax-value-non-empty: <livewire:accepts-values :value="$nonEmpty"/>
old-syntax-empty: @livewire('accepts-values', ['value' => ''])
old-syntax-non-empty: @livewire('accepts-values', ['value' => 'abc'])
