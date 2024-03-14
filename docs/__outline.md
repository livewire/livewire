
Should I ditch the terms "hydrate" and "dehydrate"? Hydrate is normally used for hydrating frontend behavior from a frozen state. Well, we're just applying it to the backend. Serialize and unserialize are alternatives as are "sleep" and "wakeup".

* more examples
* best practices
	* https://github.com/michael-rubel/livewire-best-practices
* performance best practices
* security dangers
	* https://forum.archte.ch/livewire/t/advanced-livewire-a-better-way-of-working-with-models
* More readable font
* "how livewire works"
* Laravel bootcamp style tutorial
* small example app
* 

## Outline

Quickstart
* Installing Livewire
* Creating your first component (create post, not counter)
* Adding properties
* Adding behavior
* Rendering the component in the browser
* Testing it out

Upgrade Guide

Fundamentals:
* Installation
	* Composer command
	* Publishing config
	* Disabling asset auto-injection
	* Configuring Livewire's update endpoint
	* Configuring Livewire's JavaScript endpoint
	* Publishing and hosting Livewire's JavaScript
* Components
	* Creating a component
	* The render method
		* Returning Blade views
		* Returning template strings
	       * artisan make --inline
	- Rendering a single component
		- Passing parameters
		- Receiving parameters
	- Rendering a component route
		* Configuring the layout
		* Route parameters
		* Route model binding
* Properties
	* Introduction
	* Initializing properties in the mount method
	* Bulk assigning properties ($this->fill())
	* Resetting properties ($this->reset())
	* Data binding (Basic introduction with link to other documentation page)
	* Supported property types
		* (Brief explanation of hydration/dehydration and why every possible type isn't supported)
		* Primitive types (strings, int, boolean, etc...)
		* Common PHP types (Collection, DateTime, etc...)
		* Supporting custom types (explain how users can add support for types specific to their application)
			* Using Wireables
			* Using Synthesizers
	* Using $wire 
	    * Accessing properties with $wire in Alpine inside your component
	    * Manipulating properties
	    * Using $wire.get and $wire.set
	* Security concerns
		* Don't trust properties
			* Authorizing properties
			* Using "locked" properties
		* Be aware that Livewire exposes property metadata like eloquent model class names
	* "Computed" properties (using ->getPostProperty() syntax)

* Actions
	* Security concerns
	* Parameters
	* Event modifiers
		* Keydown modifiers
	* Magic actions
	 * Wireable actions
 * Data Binding
	* Live binding
	* Lazy binding
	* Debounced binding
	* Throttled binding
	* Binding nested data
	* Binding to eloquent models
* Nesting components
* Events
	* Basic example
	* Security concerns
	* Firing events
	* Listeners
	* Passing parameters
	* Scoping events
		* parent / name / self
	* JavaScript listeners
	* Dispatching browser events
* Lifecycle hooks
	* Class hooks
		* mount
		* hydrate
		* boot
		* dehydrate
		* update
* Testing
	* Basic test
        * `artisan make: --test`
	* Making a test
	* Testing presence
	* Passing component data
	* Passing query string params
	* Available commands
	* Available assertions
* AlpineJS
	* ...
* Eloquent Models
	* Setting as properties
	* Performance implications
	* Binding to attributes
	* Collections of models

Forms:
* Form submission
* Form inputs
* Input validation
* File uploads

Features:
* Loading states
* Pagination
* Inline scripts
* Flash messages
* Query string
* Redirecting
* Polling
* Authorization
* Dirty states
* File Downloads
* Offline states
* Computed properties

Deep knowledge:
* How Livewire works
* Synthesizers

JavaScript Global
	* Lifecycle hooks
Component abstractions
Artisan Commands
Troubleshooting
Security (both internal and userland)
Extending
- Custom wireables
Package development
Deployment
Publishing stubs
Laravel Echo
Reference

V3:
* Lazy loading
* SPA Mode


* Is this even the place I want to order from?
* What kind of flowers does mom like?
* How much money should I spend on them?
* When should I have them delivered?
* Do I need to call dad and make sure she will like them?


Why is writing documentation so hard?

### Words are hard

Yeah, but it's more than that. Agreed that words are hard, but I seem to be able to write a blog post no problem.

It's boring speak catered to the lowest common denominator of reader

Yeah like I feel like I don't have a voice at all. Like the jokes and meanderings and bold statements I usually like to write with don't belong here. Clarity at all costs belongs here.

### Organizing content is hard
There's the "chicken" and "egg" problem: some content depends on another piece but that piece relies back on the original piece. Which one comes first?

There's the granularity problem: how much do we want to break this up?

There's the ordering problem: "do I go simple to complex? or most real-world to least real-world?"

The repetition problem: do you repeat yourself in multiple pages? or isolate one feature to a single file? This one is answered: repeat yourself.

### Code examples are hard
How much non-crucial context to include? (include < ?php include class? namespace? use? render?)

How to make it real-world but cater to exactly the right mechanisms? "hello world" and "counter" components are helpful but not real-life. But real-life isn't often simple enough.

Do you stick with the same domain? "CreatePost" the whole time? Is it too jarring to hop around a ton, or is it too predictable, constrained and monotonous to stick with one example the entire time?

### There's a ton of it
There's just so much of it, it feels overwhelming






Humor:
* bathtubs
* silly bands
* penguins of madagascar
* Ed Bassmaster
* Spongebob
