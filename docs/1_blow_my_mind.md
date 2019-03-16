# What is Livewire?
The best way to explain Livewire, is to walk through a few code samples.

### All-backend
Here is how you would normally create a "todo" app in the classic MVC, full-page reload style.

*route*
```php
Route::get('/todos', 'TodoController@index');
Route::post('/todos', 'TodoController@store');
```

*controller*
```php
TodoController extends Controller
{
    public function index()
    {
        return view('todos', ['todos' => Todo::all()]);
    }

    public function store()
    {
        Todo::create([
            'title' => request('title'),
        ]);

        return back();
    }
}
```

*view*
```html
<form action="/todos" method="POST">
    <input name="title">
</form>

<ul>
    @foreach ($todos as $todo)
        <li>{{ $todo->title }}</li>
    @endforeach
</ul>
```

## All-frontend
Today, Javascript-heavy front-ends are becoming the standard. Let's take a look at what the TODO app looks like in this paradaigm. For this example, we're going to user VueJs.

*Vue routes*
```javscript
const routes = [
  { path: '/todos', component: TodoIndex },
]
```

*Vue component*
```vue
<template>
<div>
    <form @submit="addTodo">
        <input name="title" v-model="title">
    </form>

    <ul>
        <li v-for="(todo, index) in todos" :key="todo.id">{{ todo.title }}</li>
    </ul>
</div>
</template>

<script>
export default {
    data() {
        return {
            title: '',
            todos: [],
        }
    },

    created() {
        this.fetchTodos()
    },

    methods: {
        fetchTodos() {
            axios.get('/todos', response => {
                this.todos = response.data.todos
            })
        },

        addTodo() {
            axios.post('/todos', { title: this.title })
                .then(() => {
                    this.title = ''
                    this.fetchTodos()
                })
        }
    }
}
</script>
```

*Laravel routes*
```php
Route::get('/todos', 'TodoController@index');
Route::post('/todos', 'TodoController@store');
```

*Laravel controller*
```php
TodoController extends Controller
{
    public function index()
    {
        return ['todos' => Todo::all()];
    }

    public function store()
    {
        return Todo::create([
            'title' => request('title'),
        ]);
    }
}
```

## Frontent <3 Backend
The full-page reload approach is simple, clean, and easily testable. However, every user interaction requires a full-page reload. Alternatively, the JS-heavy front-end provides the user with a smooth UI, but at the cost of adding loads of complexity to the application and essentially maintaining two separate code-bases for one application.

Livewire attempts to solve the pain points of both paradaigms by allowing you to write & manage the UI in Laravel/PHP, but provide the user with a smooth interface. Take a look at what the TODO app looks like written using Livewire.

*routes file*
```php
Route::livewire('/todos', Todos::class);
```

*Livewire component*
```php
class Todos extends LivewireComponent
{
    public $title;

    public function addTodo()
    {
        Todo::create(['title' => $this->title]);

        $this->title = '';
    }

    public function render()
    {
        return view('todos, ['todos' => Todo::all()]);
    }
}
```

*Livewire view*
```html
<div>
    <form wire:submit="addTodo">
        <input name="title" wire:model="title">
    </form>

    <ul>
        @foreach ($todos as $todo)
            <li>{{ $todo->title }}</li>
        @endforeach
    </ul>
</div>
```

## Cool huh?
If you followed along, you'll see, Livewire is an interesting blend of paradaigms and syntax you're used to in both the frontend and the backend in the same tool. If you're happy with what you see and want to get started, read on. If you want to know how this wizardry is possible, check out "how does livewire work?". If it didn't click and you're confused, maybe try building a sample app and toying with it to see if the use cases become clear.
