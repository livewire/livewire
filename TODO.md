## Big Laracon goals:
- [ ] New site ready
- [ ] Livewire 3 beta "good enough"
- [ ] Talk as good and polished as possible
- [x] Have cool swag

## Todo:
- [x] Get testimonials
- [x] Write advanced documentation
- [x] Polish config file
- [x] Resolve "PSR-4" not compliant artisan messages
- [x] Support `->assertStatus()`
- [x] Write morphing documentation
- [x] Rethink Alpine bundling and JS extension/upgrade path
- [x] Detect multiple root elements
- [x] Finish upgrade guide
- [x] Review advanced documentation
- [ ] Write and practice talk
- [ ] Finish landing page copy
- [x] Fix bug where submit form disabling isn't re-enabled on failure
- [ ] Build v3 launch command/experience
- [ ] Put old site on new URL: 'v2.laravel-livewire.com'
- [ ] Add proper old site redirects
- [ ] Add "livewire 3 beta is available" banner to v2 site
- [ ] Add relevant docblocks
- [ ] Work through `@todo` comments
- [ ] Work through `->markTestSkipped()` statements
- [ ] Brainstorm providing key-value attribute methods (protected getListeners() kinda thing)

## Pre-release notes

* Tagged version will be: v3.0.0-beta.1
* Composer commands:
    * `composer require livewire/livewire:3.0.0-beta.1`
    * `composer require livewire/livewire:^3.0@beta`
* Launch strategy
    * Landing page
        * Using SSH unrestrict the docs site
    * V3 on composer
        * Backup `master` branch in case I eff something up (actually do this so that we have a branch to track future v2 patches...)
        * Make livewire/next public? (might not need to)
        * `git checkout master`
        * `git remote add next git@github.com:livewire/next.git`
        * `git pull next master --allow-unrelated-histories`
        * `git checkout --theirs .`
        * `git add .`
        * `git commit -m "Merge v3 from 'next' repo, resolved in favor of 'next'"`
        * `git push origin master`
        * `git tag v3.0.0-beta.1`
        * `git push origin v3.0.0-beta.1`
    * Make this all a single command? call it?
        * `artisan livewire:launch`
            * Display: doing git stuff...
            * Display: tagging beta...
            * Display: publishing to composer
            * Display: making docs site public...
            * Display: That's all folks!
        * Something more creative?
            * A web gui?
            * A crowd yelling trigger?
            * something else?
