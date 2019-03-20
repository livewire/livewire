# Laravel Livewire

## [WARNING] Not ready for prod

This project isn't currently ready for the wild. I am currently only looking for people who are willing to contribute, test, and make suggestions at this point.

If you are one of those people, please read through this entire README so we're all on the same page.

## Get set up locally

To get set up, you will need to pull down 2 repositories: `livewire`, and `livewire-app` (A Laravel project that uses livewire). I recently ripped out all the Jest tests in favor of some basic Dusk tests in the `livewire-app`, so working off that project is important for now.

1. Pull down `livewire`: `git clone https://github.com/calebporzio/livewire.git`
2. Go into the directory (`cd livewire`) and `composer install` and `npm install`
3. Go out of the directory (`cd ..`) and pull down `livewire-app`: `git clone https://github.com/calebporzio/livewire-app.git`
4. Go into the directory (`cd livewire`) and `composer install` and `npm install`
5. Open the `livewire-app` in your browser (assuming you use laravel-valet), go to `http://livewire-app.test`
6. Open the docs in your browser: from `livewire-app`, run `php artisan docs` (or go to `/livewire/docs/installation`)

A couple notes:
* The "Livewire App" does not pull livewire in from packagist, it looks one directory up for a folder called `livewire` and creates a symlink to it in the vendor directory - this way, you can make changes in `livewire` and use them instanty in the `livewire-app`
* I've version controlled the `.env` file for `livewire-app` to make setup easy.

## How you can help

### Use it
It's probaby best at first to just start playing with it yourself. Read through the docs for guidance, if you find something that's broken, please submit an issue (or PR) for it. If you have ideas or suggestions, start an issue called: "[Your Name]'s ideas". That will keep things cleaner for me to manage.

### Tiny refactors
A very small, well-scoped PR that even just renames a variable is super valuable. Some areas of the project (especially JS) are in need of refactoring. The smaller, the better! Just pick a file, read throgh it, and I gaurauntee you'll find something that could be improved.

### Write more tests
Livewire is currently lacking in tests. I have a handful of Unit tests, but they are not well thought out and only cover a small portion of the code base.

As for JavaScript testing, I started out with some basic Jest snapshot tests with axios mocking, but it's a PITA to maintain them and getting the dom plugin to work well can be annoying as well. Therefore, I ripped them out and wrote a few simple Dusk tests. The problem with the Dusk approach, is they need to live in a separate "app" environment, so you have to run and maintain them from `livewire-app`. This is an obvios problem, so any thoughts/solutions are welcomed.

### The "feature testing" testing feature (lol)
Ideally, a user could write Livewire tests like they would write Dusk tests: `$this->assertSee()->click()`. I have created a basic implementation in `TestableLivewire.php`, but after other refactors, it is broken and in need of love. There are currently 2 problems to tackle:

1) Dealing with and utilizing Livewire directives with modifiers, like `wire:model.live`. Querying for, selecting, and interpretting these directives in PHP is not easy. You can see some of my attempts if you dig into it.

2) Currently, this strategy only supports one component deep testing. It would be great if we could support testing a component and all it's children with this. It will not be easy, and in many ways, we will be duplicating logic already existing in JavaScript.

At the end of the day, we may just want to instruct people to write Dusk tests (and provide them with some helpers), but it would be sooo powerful if we could get a good DomCrawler implementation working because the tests would run SUPER fast and not have to deal with chromedriver or any of that crap.

### Get WebSockets driver working well
I haven't paid much attention to the WebSocket portion of Livewire in a long time. It works locally, but I'm sure won't hold up well in production. It doesn't currently support https, and there is no documentation for scaling and keeping it running in a daemon.

Also, the `livewire:watch` (file-watcher) command eats up CPU, so that will need to be optimized.

I would love to get the websocket implementation really solid and straightforward to use, so any help here would be great.

### Switch to a more "lean" serialization strategy
Currently, I am just serializing and encrypting the entire Livewire component. We should only need to serialize public properties on the component, and re-hydrate the component, instead of un-serializing it each time. It would cut down payload sizes and allow us to keep more logic in "LivewireComponent" instead of the wrapper I created "LivewireComponentWrapper".

## Closing thoughts
Thanks for being willing to contribute. I think Livewire is going to change the game (at least it does for me), so I'm really excited to get it out there, I just want to make sure it's good and solid before releasing it in the wild. Thanks for being a part of that.
