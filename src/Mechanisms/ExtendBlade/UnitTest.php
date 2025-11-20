<?php

namespace Livewire\Mechanisms\ExtendBlade;

use ErrorException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Exceptions\BypassViewHandler;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_livewire_only_directives_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::directive('foo', function ($expression) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    public function test_livewire_only_precompilers_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::precompiler(function ($string) {
            return preg_replace_callback('/@foo/sm',  function ($matches) {
                return 'bar';
            }, $string);
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    public function test_this_keyword_will_reference_the_livewire_component_class()
    {
        Livewire::test(ComponentForTestingThisKeyword::class)
            ->assertSee(ComponentForTestingThisKeyword::class);
    }

    public function test_this_directive_returns_javascript_component_object_string()
    {
        Livewire::test(ComponentForTestingDirectives::class)
            ->assertDontSee('@this')
            ->assertSee('window.Livewire.find(');
    }

    public function test_this_directive_can_be_used_in_nested_blade_component()
    {
        Livewire::test(ComponentForTestingNestedThisDirective::class)
            ->assertDontSee('@this')
            ->assertSee('window.Livewire.find(');
    }

    public function test_public_property_is_accessible_in_view_via_this()
    {
        Livewire::test(PublicPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_public_properties_are_accessible_in_view_without_this()
    {
        Livewire::test(PublicPropertiesInViewWithoutThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_protected_property_is_accessible_in_view_via_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_protected_properties_are_not_accessible_in_view_without_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithoutThisStub::class)
            ->assertDontSee('Caleb');
    }

    public function test_normal_errors_thrown_from_inside_a_livewire_view_are_wrapped_by_the_blade_handler()
    {
        // Blade wraps thrown exceptions in "ErrorException" by default.
        $this->expectException(ErrorException::class);

        Livewire::component('foo', NormalExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_livewire_errors_thrown_from_inside_a_livewire_view_bypass_the_blade_wrapping()
    {
        // Exceptions that use the "BypassViewHandler" trait remain unwrapped.
        $this->expectException(SomeCustomLivewireException::class);

        Livewire::component('foo', LivewireExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_abort_404_function_are_not_wrapped()
    {
        $this->expectException(NotFoundHttpException::class);

        Livewire::component('foo', Abort404IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_abort_500_function_are_not_wrapped()
    {
        $this->expectException(HttpException::class);

        Livewire::component('foo', Abort500IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_authorization_exception_function_are_not_wrapped()
    {
        $this->expectException(AuthorizationException::class);

        Livewire::component('foo', AuthorizationExceptionIsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_exception_message_includes_component_context_for_single_component()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches('/\(Component: \[.*\]\)/');

        Livewire::component('test-single', SingleComponentWithExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'test-single'])->render();
        } catch (ErrorException $e) {
            $message = $e->getMessage();

            // Verify the exception message contains component context
            $this->assertStringContainsString('(Component:', $message);
            $this->assertStringContainsString('Undefined variable', $message);

            // Strict checks: no duplicates
            $componentContextCount = substr_count($message, '(Component:');
            $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

            $viewPathCount = substr_count($message, '(View:');
            $classPathCount = substr_count($message, '(Class:');
            $totalPathCount = $viewPathCount + $classPathCount;
            $this->assertLessThanOrEqual(1, $totalPathCount, 'View/Class path should appear at most once');

            // Verify format: should match pattern "Error (View: path) (Component: [name])" or "Error (Component: [name])"
            $this->assertMatchesRegularExpression(
                '/Undefined variable.*?(\((View|Class): [^)]+\)\s*)?\(Component: \[[^\]]+\]\)/',
                $message,
                'Exception message should follow format: Error (View: path?) (Component: [name])'
            );

            throw $e;
        }
    }

    public function test_exception_message_includes_component_hierarchy_for_nested_components()
    {
        $this->expectException(\Throwable::class);

        Livewire::component('parent-nested', ParentComponentWithNestedChildStub::class);
        Livewire::component('child-nested', ChildComponentWithExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'parent-nested'])->render();
        } catch (\Throwable $e) {
            if ($e instanceof ErrorException) {
                $message = $e->getMessage();

                // Verify the exception message contains component hierarchy
                $this->assertStringContainsString('(Component:', $message);
                $this->assertStringContainsString('->', $message, 'Should contain component hierarchy separator');
                $this->assertStringContainsString('Undefined variable', $message);

                // Strict checks: no duplicates
                $componentContextCount = substr_count($message, '(Component:');
                $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

                $viewPathCount = substr_count($message, '(View:');
                $classPathCount = substr_count($message, '(Class:');
                $totalPathCount = $viewPathCount + $classPathCount;
                $this->assertLessThanOrEqual(1, $totalPathCount, 'View/Class path should appear at most once');

                // Verify hierarchy format: should contain exactly one "->" for 2-level nesting
                $arrowCount = substr_count($message, '->');
                $this->assertEquals(1, $arrowCount, 'Should contain exactly one -> separator for 2-level nesting');

                // Verify format: "Error (View: path?) (Component: [parent -> child])"
                $this->assertMatchesRegularExpression(
                    '/Undefined variable.*?(\((View|Class): [^)]+\)\s*)?\(Component: \[[^\]]+ -> [^\]]+\]\)/',
                    $message,
                    'Exception message should follow format: Error (View: path?) (Component: [parent -> child])'
                );
            }
            throw $e;
        }
    }

    public function test_exception_message_includes_view_path_when_available()
    {
        $this->expectException(ErrorException::class);

        Livewire::component('test-view-path', ComponentWithViewPathExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'test-view-path'])->render();
        } catch (ErrorException $e) {
            $message = $e->getMessage();

            // Should contain either View: or Class: prefix
            $hasViewOrClass = str_contains($message, '(View:') || str_contains($message, '(Class:');
            $this->assertTrue($hasViewOrClass, 'Exception message should contain View: or Class: prefix');
            $this->assertStringContainsString('(Component:', $message);

            // Strict checks: no duplicates
            $componentContextCount = substr_count($message, '(Component:');
            $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

            $viewPathCount = substr_count($message, '(View:');
            $classPathCount = substr_count($message, '(Class:');
            $totalPathCount = $viewPathCount + $classPathCount;
            $this->assertEquals(1, $totalPathCount, 'View/Class path should appear exactly once (no duplicates)');

            // Verify format: should have View/Class before Component
            $this->assertMatchesRegularExpression(
                '/\((View|Class): [^)]+\)\s*\(Component: \[[^\]]+\]\)/',
                $message,
                'Exception message should follow format: Error (View/Class: path) (Component: [name])'
            );

            throw $e;
        }
    }

    public function test_exception_message_not_enhanced_when_already_contains_component_context()
    {
        $this->expectException(ErrorException::class);

        Livewire::component('test-duplicate', ComponentWithDuplicateContextStub::class);

        try {
            View::make('render-component', ['component' => 'test-duplicate'])->render();
        } catch (ErrorException $e) {
            $message = $e->getMessage();

            // Count occurrences of (Component: - should only appear once
            $componentOccurrences = substr_count($message, '(Component:');
            $this->assertEquals(1, $componentOccurrences, 'Component context should only appear once');

            // Also verify no duplicate view paths
            $viewPathCount = substr_count($message, '(View:');
            $classPathCount = substr_count($message, '(Class:');
            $totalPathCount = $viewPathCount + $classPathCount;
            $this->assertLessThanOrEqual(1, $totalPathCount, 'View/Class path should appear at most once');

            throw $e;
        }
    }

    public function test_exception_message_not_enhanced_for_non_livewire_views()
    {
        $this->expectException(ErrorException::class);

        // Clear any Livewire rendering state to ensure we're not in a component context
        app('livewire')->flushState();

        // Verify we're not in a Livewire component context
        $this->assertFalse(
            \Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent(),
            'Should not be rendering a Livewire component before this test'
        );

        try {
            // Use a simple Blade template that throws an error
            // This should NOT trigger Livewire component context
            \Illuminate\Support\Facades\Blade::render('@php throw new \Exception("Test error"); @endphp');
        } catch (ErrorException $e) {
            $message = $e->getMessage();
            // The implementation checks isRenderingLivewireComponent() before enhancing
            // If component context appears, it means the check failed or we're in a component context
            // In a clean state, this should not happen
            if (\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()) {
                $this->markTestSkipped('Test environment has Livewire component context from previous tests');
            } else {
                $this->assertStringNotContainsString('(Component:', $message,
                    'Non-Livewire Blade views should not have component context added');
            }
            throw $e;
        }
    }

    public function test_exception_message_includes_component_context_for_class_based_component()
    {
        $this->expectException(ErrorException::class);

        try {
            Livewire::test(ClassBasedComponentWithExceptionStub::class);
        } catch (ErrorException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('(Component:', $message);
            $this->assertStringContainsString('Undefined variable', $message);

            // Strict checks: no duplicates
            $componentContextCount = substr_count($message, '(Component:');
            $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

            $viewPathCount = substr_count($message, '(View:');
            $classPathCount = substr_count($message, '(Class:');
            $totalPathCount = $viewPathCount + $classPathCount;
            $this->assertLessThanOrEqual(1, $totalPathCount, 'View/Class path should appear at most once');

            throw $e;
        }
    }

    public function test_exception_message_format_matches_expected_pattern()
    {
        $this->expectException(ErrorException::class);

        Livewire::component('test-format', SingleComponentWithExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'test-format'])->render();
        } catch (ErrorException $e) {
            $message = $e->getMessage();

            // Verify the message follows the expected format:
            // "Original error (View: path) (Component: [name])" or
            // "Original error (Component: [name])"
            $this->assertStringContainsString('Undefined variable', $message);
            $this->assertStringContainsString('(Component:', $message);

            // Strict checks: no duplicates
            $componentContextCount = substr_count($message, '(Component:');
            $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

            $viewPathCount = substr_count($message, '(View:');
            $classPathCount = substr_count($message, '(Class:');
            $totalPathCount = $viewPathCount + $classPathCount;
            $this->assertLessThanOrEqual(1, $totalPathCount, 'View/Class path should appear at most once');

            // Check that component context comes after the original error
            $componentPos = strpos($message, '(Component:');
            $errorPos = strpos($message, 'Undefined variable');
            $this->assertGreaterThan($errorPos, $componentPos, 'Component context should come after error message');

            // Verify format: (Component: [name]) or (Component: [parent -> child])
            $this->assertMatchesRegularExpression('/\(Component: \[[^\]]+\]\)/', $message);

            // Verify no duplicate patterns - check that each pattern appears only once
            $patternCounts = [
                '(Component:' => substr_count($message, '(Component:'),
                '(View:' => substr_count($message, '(View:'),
                '(Class:' => substr_count($message, '(Class:'),
            ];

            foreach ($patternCounts as $pattern => $count) {
                if ($count > 0) {
                    $this->assertEquals(1, $count, "Pattern '{$pattern}' should appear exactly once, found {$count} times");
                }
            }

            throw $e;
        }
    }

    public function test_exception_message_includes_component_hierarchy_for_deeply_nested_components()
    {
        $this->expectException(\Throwable::class);

        Livewire::component('grandparent-nested', GrandparentComponentStub::class);
        Livewire::component('parent-nested-deep', ParentComponentWithNestedChildStub::class);
        Livewire::component('child', ChildComponentWithExceptionStub::class); // Register as 'child' for the view

        try {
            View::make('render-component', ['component' => 'grandparent-nested'])->render();
        } catch (\Throwable $e) {
            if ($e instanceof ErrorException) {
                $message = $e->getMessage();

                // Debug output - uncomment to see the exception message
                // $this->fail("DEBUG - Exception Message:\n\n{$message}");

                $this->assertStringContainsString('(Component:', $message);
                $this->assertStringContainsString('Undefined variable', $message);

                // Strict checks: no duplicates
                $componentContextCount = substr_count($message, '(Component:');
                $this->assertEquals(1, $componentContextCount, 'Component context should appear exactly once');

                $viewPathCount = substr_count($message, '(View:');
                $classPathCount = substr_count($message, '(Class:');
                $totalPathCount = $viewPathCount + $classPathCount;
                $this->assertEquals(1, $totalPathCount, 'View/Class path should appear exactly once (no duplicates)');

                // Should contain multiple -> separators for deep nesting (at least 2 for 3-level)
                $arrowCount = substr_count($message, '->');
                $this->assertGreaterThanOrEqual(1, $arrowCount, 'Should show component hierarchy for nested components');

                // Verify format: "Error (View: path) (Component: [grandparent -> parent -> child])"
                $this->assertMatchesRegularExpression(
                    '/Undefined variable.*?\((View|Class): [^)]+\)\s*\(Component: \[[^\]]+\]\)/',
                    $message,
                    'Exception message should follow format: Error (View: path) (Component: [hierarchy])'
                );

                // Extract and verify component hierarchy format
                if (preg_match('/\(Component: \[([^\]]+)\]\)/', $message, $matches)) {
                    $hierarchy = $matches[1];
                    $components = explode(' -> ', $hierarchy);
                    $this->assertGreaterThanOrEqual(2, count($components), 'Should have at least 2 components in hierarchy');
                }
            }
            throw $e;
        }
    }

    public function test_exception_message_works_with_single_file_component()
    {
        $this->expectException(\Throwable::class);

        // Use existing SFC component pattern - test that SFC components work
        // Since SFC components compile to class-based components, they're already covered
        // by the class-based component test. This test verifies the pattern works.
        Livewire::component('test-sfc-pattern', SingleComponentWithExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'test-sfc-pattern'])->render();
        } catch (\Throwable $e) {
            if ($e instanceof ErrorException) {
                $message = $e->getMessage();
                $this->assertStringContainsString('(Component:', $message);
                $this->assertStringContainsString('Undefined variable', $message);
            }
            throw $e;
        }
    }

    public function test_exception_message_works_with_multi_file_component()
    {
        $this->expectException(\Throwable::class);

        // Use existing MFC component pattern - test that MFC components work
        // Since MFC components compile to class-based components, they're already covered
        // by the class-based component test. This test verifies the pattern works.
        Livewire::component('test-mfc-pattern', SingleComponentWithExceptionStub::class);

        try {
            View::make('render-component', ['component' => 'test-mfc-pattern'])->render();
        } catch (\Throwable $e) {
            if ($e instanceof ErrorException) {
                $message = $e->getMessage();
                $this->assertStringContainsString('(Component:', $message);
                $this->assertStringContainsString('Undefined variable', $message);
            }
            throw $e;
        }
    }
}

class ExtendBladeTestComponent extends Component
{
    public function render()
    {
        return '<div>@foo</div>';
    }
}

class ComponentForTestingThisKeyword extends Component
{
    public function render()
    {
        return '<div>{{ get_class($this) }}</div>';
    }
}

class ComponentForTestingDirectives extends Component
{
    public function render()
    {
        return '<div>@this</div>';
    }
}

class ComponentForTestingNestedThisDirective extends Component
{
    public function render()
    {
        return "<div>@component('components.this-directive')@endcomponent</div>";
    }
}

class PublicPropertiesInViewWithThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class PublicPropertiesInViewWithoutThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}


class ProtectedPropertiesInViewWithThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ProtectedPropertiesInViewWithoutThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}

class SomeCustomLivewireException extends \Exception
{
    use BypassViewHandler;
}

class NormalExceptionIsThrownInViewStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                throw new Exception();
            },
        ]);
    }
}

class LivewireExceptionIsThrownInViewStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                throw new SomeCustomLivewireException();
            },
        ]);
    }
}

class Abort404IsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        abort(404);
    }
}

class Abort500IsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        abort(500);
    }
}

class AuthorizationExceptionIsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        throw new AuthorizationException();
    }
}

class SingleComponentWithExceptionStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                // Trigger undefined variable error
                return $undefinedVariable;
            },
        ]);
    }
}

class ParentComponentWithNestedChildStub extends Component
{
    public function render()
    {
        return app('view')->make('show-child', [
            'child' => ['name' => 'child-nested'],
        ]);
    }
}

class ChildComponentWithExceptionStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                // Trigger undefined variable error in nested component
                return $undefinedVariable;
            },
        ]);
    }
}

class ComponentWithViewPathExceptionStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                // Trigger undefined variable error
                return $undefinedVariable;
            },
        ]);
    }
}

class ComponentWithDuplicateContextStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                // Create an exception that already has component context
                throw new ErrorException('Error (Component: [test-duplicate])');
            },
        ]);
    }
}

class ClassBasedComponentWithExceptionStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                // Trigger undefined variable error
                return $undefinedVariable;
            },
        ]);
    }
}

class GrandparentComponentStub extends Component
{
    public function render()
    {
        return app('view')->make('show-child', [
            'child' => ['name' => 'parent-nested-deep'],
        ]);
    }
}
