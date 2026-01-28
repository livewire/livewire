<?php

namespace Livewire\Features\SupportMorphAwareBladeCompilation;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys;
use Livewire\Livewire;

class BenchmarkTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();
        config()->set('livewire.smart_wire_keys', true);
        config()->set('livewire.inject_morph_markers', true);

        // Reload features
        $precompilers = \Livewire\invade(app('blade.compiler'))->precompilers;
        \Livewire\invade(app('blade.compiler'))->precompilers = array_filter($precompilers, function ($precompiler) {
            if (! $precompiler instanceof \Closure) return true;
            $closureClass = (new \ReflectionFunction($precompiler))->getClosureScopeClass()->getName();
            return $closureClass !== SupportCompiledWireKeys::class
                && $closureClass !== SupportMorphAwareBladeCompilation::class;
        });
        ComponentHookRegistry::register(SupportMorphAwareBladeCompilation::class);
        ComponentHookRegistry::register(SupportCompiledWireKeys::class);
    }

    public function test_benchmark()
    {
        $templates = [
            'simple' => <<<'BLADE'
<div>
    @if($show)
        <span>Hello</span>
    @endif
</div>
BLADE,

            'medium' => <<<'BLADE'
<div>
    @foreach($items as $item)
        @if($item > 5)
            <span>{{ $item }}</span>
        @endif
    @endforeach

    @foreach($other as $thing)
        {{ $thing > 0 ? 'yes' : 'no' }}
    @endforeach
</div>
BLADE,

            'complex' => <<<'BLADE'
<div class="container">
    @foreach($categories as $category)
        <div class="category">
            <h2>{{ $category->name }}</h2>
            @if($category->items->count() > 0)
                <ul>
                    @foreach($category->items as $item)
                        @if($loop->iteration > 1)
                            <li class="not-first">
                        @else
                            <li class="first">
                        @endif
                            {{ $item->name }}
                            @if($item->price > 100)
                                <span class="expensive">{{ $item->price }}</span>
                            @else
                                <span>{{ $item->price }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No items</p>
            @endif
        </div>
    @endforeach

    @forelse($featured as $product)
        <div @if($product->highlighted) class="highlight" @endif>
            {{ $product->name }}
        </div>
    @empty
        <p>No featured products</p>
    @endforelse
</div>
BLADE,

            'many_directives' => str_repeat(<<<'BLADE'
<div>
    @if($a > 1)
        {{ $a }}
    @endif
    @foreach($items as $i)
        {{ $i > 0 ? 'pos' : 'neg' }}
    @endforeach
</div>
BLADE, 20),

            'deeply_nested' => <<<'BLADE'
<div>
    @foreach($a as $item1)
        @foreach($item1 as $item2)
            @foreach($item2 as $item3)
                @if($item3 > 10)
                    @foreach($item3->children as $child)
                        @if($child->value > 5)
                            {{ $child->name }}
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endforeach
    @endforeach
</div>
BLADE,

            // Real-world template structure from a budget management page
            // Stripped custom components but preserved all Blade directives
            'real_world' => <<<'BLADE'
<div>
    <div>
        <h1>Show Budget</h1>
        <div>
            <label wire:model="expandCategories">Expand Categories</label>
            <button>Create Item</button>
        </div>
    </div>

    <div>
        <table x-data="{ categoryStatus: {} }">
            @foreach ($budgetData as $bankAccountId => $bankAccountData)
                @php
                    $startingBalance = $bankAccountData['startingBalance'];
                    $currentBalance = $startingBalance;
                    $cashFlowTotals = [];
                @endphp

                @unless ($loop->first)
                    <tr>
                        <td colspan="10"></td>
                    </tr>
                @endunless

                <tr>
                    <td>{{ $bankAccountData['bankAccount'] }}</td>
                    <td colspan="10"></td>
                </tr>

                <tr>
                    <td></td>
                    @foreach ($occurrences as $occurrence)
                        <td>{{ $occurrence->format('D') }}</td>
                    @endforeach
                </tr>

                <tr>
                    <td>{{ $startingBalance }}</td>
                    @foreach ($occurrences as $occurrence)
                        <td>{{ $occurrence->toLocalDateString() }}</td>
                    @endforeach
                </tr>

                @foreach (['INCOME', 'EXPENSE'] as $groupType)
                    @php
                        $totals = [];
                    @endphp

                    <tr>
                        <td></td>
                        @foreach ($occurrences as $occurrence)
                            <td></td>
                        @endforeach
                    </tr>

                    <tr>
                        <td>{{ $groupType }}</td>
                        @foreach ($occurrences as $occurrence)
                            <td></td>
                        @endforeach
                    </tr>

                    @php
                        $shouldHighlightRow = true;
                    @endphp

                    @foreach ($items as $item)
                        @php
                            $itemData = $item['itemData'];
                        @endphp

                        <tr class="{{ $shouldHighlightRow ? 'bg-zinc-100' : 'bg-white' }}">
                            <td>
                                {{ $item['name'] }}
                                <button wire:click="$dispatch('edit', { id: {{ $item['id'] }}})">Edit</button>
                            </td>

                            @php
                                $shouldHighlightRow = !$shouldHighlightRow;
                            @endphp

                            @foreach ($occurrences as $occurrence)
                                @php
                                    $occurrenceDate = $occurrence->toDateString();
                                    $amount = $itemData[$occurrenceDate] ?? null;
                                    if ($amount) {
                                        $totals[$occurrenceDate] = ($totals[$occurrenceDate] ?? 0) + $amount;
                                    }
                                @endphp
                                <td>{{ $itemData[$occurrence->toDateString()] ?? null }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    @foreach ($categories as $category)
                        @php
                            $categoryTotals = [];
                        @endphp

                        <tr x-show="categoryVisible('{{ $groupType }}', '{{ $category['id'] }}')">
                            <td x-on:click="toggleCategory('{{ $groupType }}', '{{ $category['id'] }}')">
                                {{ $category['name'] }}
                                <span x-show="categoryVisible('{{ $groupType }}', '{{ $category['id'] }}')"></span>
                            </td>
                            @foreach ($occurrences as $occurrence)
                                <td></td>
                            @endforeach
                        </tr>

                        @foreach ($category['items'] as $categoryItem)
                            @php
                                $categoryItemData = $categoryItem['itemData'];
                            @endphp

                            <tr x-show="categoryVisible('{{ $groupType }}', '{{ $category['id'] }}')"
                                class="{{ $shouldHighlightRow ? 'bg-zinc-100' : 'bg-white' }}">
                                <td>
                                    {{ $categoryItem['name'] }}
                                    <button wire:click="$dispatch('edit', { id: {{ $categoryItem['id'] }}})">Edit</button>
                                </td>

                                @php
                                    $shouldHighlightRow = !$shouldHighlightRow;
                                @endphp

                                @foreach ($occurrences as $occurrence)
                                    @php
                                        $occurrenceDate = $occurrence->toDateString();
                                        $amount = $categoryItemData[$occurrenceDate] ?? null;
                                        if ($amount) {
                                            $totals[$occurrenceDate] = ($totals[$occurrenceDate] ?? 0) + $amount;
                                            $categoryTotals[$occurrenceDate] = ($categoryTotals[$occurrenceDate] ?? 0) + $amount;
                                        }
                                    @endphp
                                    <td>{{ $categoryItemData[$occurrence->toDateString()] ?? null }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        <tr>
                            <td x-on:click="toggleCategory('{{ $groupType }}', '{{ $category['id'] }}')">
                                {{ $category['name'] }}
                                <span x-show="categoryVisible('{{ $groupType }}', '{{ $category['id'] }}')">Total</span>
                                <span x-show="!categoryVisible('{{ $groupType }}', '{{ $category['id'] }}')"></span>
                            </td>
                            @foreach ($occurrences as $occurrence)
                                <td>{{ $categoryTotals[$occurrence->toDateString()] ?? null }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    <tr>
                        <td>Total {{ $groupType }}</td>
                        @foreach ($occurrences as $occurrence)
                            @php
                                $occurrenceDate = $occurrence->toDateString();
                                $amount = $totals[$occurrence->toDateString()] ?? 0;
                                if ($groupType === 'INCOME') {
                                    $cashFlowTotals[$occurrenceDate] = ($cashFlowTotals[$occurrenceDate] ?? 0) + $amount;
                                } else {
                                    $cashFlowTotals[$occurrenceDate] = ($cashFlowTotals[$occurrenceDate] ?? 0) - $amount;
                                }
                            @endphp
                            <td>{{ $totals[$occurrence->toDateString()] ?? 0 }}</td>
                        @endforeach
                    </tr>
                @endforeach

                <tr>
                    <td>Cash Flow</td>
                    @foreach ($occurrences as $occurrence)
                        @php
                            $occurrenceDate = $occurrence->toDateString();
                        @endphp
                        <td>{{ $cashFlowTotals[$occurrenceDate] ?? 0 }}</td>
                    @endforeach
                </tr>

                <tr>
                    <td>Closing Balance</td>
                    @foreach ($occurrences as $occurrence)
                        @php
                            $occurrenceDate = $occurrence->toDateString();
                            $currentBalance = $currentBalance + $cashFlowTotals[$occurrenceDate];
                        @endphp
                        <td>{{ $currentBalance }}</td>
                    @endforeach
                </tr>

                @unless ($loop->last)
                @endunless
            @endforeach
        </table>
    </div>
</div>
BLADE,
        ];

        $iterations = 100;

        echo "\n\nRunning benchmark with {$iterations} iterations per template...\n\n";

        $results = [];

        foreach ($templates as $name => $template) {
            // Warm up
            for ($i = 0; $i < 5; $i++) {
                Blade::compileString($template);
            }

            // Benchmark
            $start = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                Blade::compileString($template);
            }
            $end = hrtime(true);

            $totalMs = ($end - $start) / 1_000_000;
            $avgMs = $totalMs / $iterations;

            $results[$name] = [
                'total_ms' => round($totalMs, 2),
                'avg_ms' => round($avgMs, 4),
                'template_size' => strlen($template),
            ];

            // Count Blade directives (opening and closing separately)
            preg_match_all('/@(if|else|elseif|endif|unless|endunless|foreach|endforeach|forelse|empty|endforelse|for|endfor|while|endwhile|switch|case|break|default|endswitch|isset|endisset|auth|endauth|guest|endguest|error|enderror|php|endphp|use|class|style)\b/', $template, $directiveMatches);
            $directiveCount = count($directiveMatches[0]);

            // Count HTML tags (opening and closing separately)
            preg_match_all('/<\/?[a-zA-Z][a-zA-Z0-9-]*/', $template, $tagMatches);
            $tagCount = count($tagMatches[0]);

            printf("%-20s: %8.2f ms total, %8.4f ms avg (size: %5d bytes, %3d directives, %3d tags)\n",
                $name, $totalMs, $avgMs, strlen($template), $directiveCount, $tagCount);
        }

        echo "\nJSON: " . json_encode($results) . "\n\n";

        $this->assertTrue(true);
    }
}
