

## Feedback

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
		 * The --test flag
		 * The --inline flag
	* The render method
		* Returning Blade views
		* Returning template strings
	- Rendering a single component
		- Passing parameters
		- Receiving parameters
	- Rendering a component route
		* Configuring the layout
		* Route parameters
		* Route model binding
* Properties
	* Security concerns
	* Initializing properties
	* Bulk assigning properties (->fill)
	* Resetting properties (->reset)
	* Data binding (intro + link to page)
	* Model binding (intro + link to page)
	* Wireable properties
	* Computed properties
* Data Binding
	* Live binding
	* Lazy binding
	* Debounced binding
	* Throttled binding
	* Binding nested data
	* Binding to eloquent models
* Actions
	* Security concerns
	* Parameters
	* Event modifiers
		* Keydown modifiers
	* Magic actions
* Nesting components
* Events
	* Basic example
	* Security concerns
	* Firing events
	* Listerns
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
Laravel Echo
Reference

V3:
* Lazy loading
* SPA Mode
