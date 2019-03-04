# Blow my mind
Let me take you on a quick journey through time to show you the need Livewire meets. If your mind is not blown by the end of this, I'll give you your money back.

Consider the age old "TODO" functionality an app might need.

![A user typing todos into an input field, hitting enter, and seeing a todo list update.](./todo_demo.gif)

### All-backend
At the dawn of time, our ancestors were building app in the traditional MVC style. The way they would have achieved dynamic functionality, was through form submissions and full-page reloads. They would have utilized a Route, a Controller, and a View everytime they needed to update a webpage. Let's look at some sample code that we believe looks something like what they would have wrote:

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
Fast-forward to 2019. Javascript is taking over the world. Gone are the dark days of full-page reloads, everything is done in a browser, in real-time, without reloading the page. Front-end frameworks like React, Angular, and Vue make listening for user events, tracking state, and making server requests in the background via Ajax a cinch. Here is how TODO apps are being written now-adays: (we'll use Vue for this example)

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
The all-backend approach was so simple, so clean, so easily testable. However, every user interaction required a full-page reload, and this just wasn't enough. We went all crazy with SPAs and completely separated the front-end from the backend. We thought this was better for some reason. Our front-ends were buttery smooth, but we were writing twice as much code as before and managing two codebases, not to mention testing both parts seperately and together was rediculously hard. The complexity was just overwhelming. Now, in the future, the line between front-end and backend will become fuzzy as we come up with frameworks that take care of the tedius front-end wiring we were doing before. Introducing: Livewire.

*routes file*
```php
Route::livewire('/todos', Todos::class);
```

*Livewire component*
```
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

## Mind blown?
If you followed along, you'll see, Livewire is an interesting blend of paradaigms and syntax you're used to in both the frontend and the backend in the same tool. If you're happy with what you see and want to get started, read on. If you want to know how this wizardry is possible, check out "how does livewire work?". If you it didn't click and you're confused, maybe try building a sample app and toying with it to see if the use cases become clear.
