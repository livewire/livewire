# Laravel Livewire

## Get set up for local development and contribution

Note: I'm assuming you have a folder for all your projects and are serving that with Valet.

2. Create or `cd` into a Laravel project
3. Run `composer config repositories.livewire vcs git@github.com:calebporzio/livewire.git`
4. Now `composer require calebporzio/livewire:dev-master`
5. View documentation here: https://livewire-framework.com/docs/quickstart
6. Contribute to documentation here: https://github.com/calebporzio/livewire-docs

## Thank You's

- Thanks to @davidpiesse for helping a ton with the event emission idea and implementation.

## TODOs
- loading minimum indicator (hard because the "unset" is handled by the dom diffing)
- Add livewire:destroy {component}
- look into preventing data manipulation
- finish the session protected prop storage garbage collection
- throw error if someone tries to attach a non string / array as a public property
- re-generate the manifest file if it can't find a component
- the last keytype thing for todos
- disabling form buttons when clicked
- launch and check it in production
- don't show loading indicator if below 30ms or something
- allow an option for a min. loading inicator time
