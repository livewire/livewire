<?php

namespace Livewire\Features\SupportEncryptedProperties;

use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Form;
use Sushi\Sushi;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    function properties_can_be_encrypted()
    {
        $component = Livewire::test(new class extends Component {
            #[BaseEncrypted]
            public PostForEncryption $post;

            function mount()
            {
                $this->post = PostForEncryption::first();
            }

            public function render() {
                return '<div>title: {{ $post->title }}</div>';
            }
        });

        $component->assertSee('title: foo');

        $this->assertStringNotContainsString('PostForEncryption',
            json_encode($component->state()->getSnapshot())
        );
    }

    /** @test */
    function properties_are_not_encrypted_by_default()
    {
        $component = Livewire::test(new class extends Component {
            public PostForEncryption $post;

            function mount()
            {
                $this->post = PostForEncryption::first();
            }

            public function render() {
                return '<div>title: {{ $post->title }}</div>';
            }
        });

        $component->assertSee('title: foo');

        $this->assertStringContainsString('PostForEncryption',
            json_encode($component->state()->getSnapshot())
        );
    }

    /** @test */
    function properties_inside_form_objects_can_be_encrypted()
    {
        $component = Livewire::test(new class extends Component {
            public EncryptedPropertyForm $form;

            function mount()
            {
                $this->form = new EncryptedPropertyForm;
            }

            public function render() {
                return '<div>title: {{ $form->post->title }}</div>';
            }
        });

        $component->assertSee('title: foo');

        $this->assertStringNotContainsString('PostForEncryption',
            json_encode($component->state()->getSnapshot())
        );
    }
}

class EncryptedPropertyForm extends Form
{
    #[BaseEncrypted]
    public PostForEncryption $post;

    function __construct()
    {
        $this->post = PostForEncryption::first();
    }
}

class PostForEncryption extends Model {
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'foo'],
    ];
}
