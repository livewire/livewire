
When Livewire properties are [_dehydrated_](/docs/hydration), potentially sensitive information about them is sent as plain strings to the browser between requests.

For example, given the following component with a `$post` public property storing a Post model:

```php
class ShowPost extends Component
{
    public Post $post;

    // ...
}
```

When this component is _dehydrated_ (serialized) and sent to the browser, the `$post` property will be sent as the following array of data:

```json
{
    "type": "model",
    "class": "App\Models\Post",
    "key": 1,
    "relationships": [].
}
```

As you can see, any user of the application can inspect Livewire's payloads and gain access to potentially sensitive knowledge like: the class name of the model as well as the primary key of the model.

Livewire assumes most applications don't consider this information particularly sensitive and chooses to leave it unencrypted by default.

However, if you'd like Livewire to encrypt metadata pertaining to a property, you can use Livewire's `#[Encrypted]` attribute like so:

```php
use Livewire\Attributes\Encrypted;

class ShowPost extends Component
{
    #[Encrypted] // [tl! highlight]
    public Post $post;

    // ...
}
```

Now, instead of sending the JSON object of metadata as a plain string, Livewire will send an encrypted string of that data and decrypt it on the next request.

### Why not encrypt everything by default?

Encrypting data securely has two distinct performance implications:

1) Encrypted data is up to three times as large as unencrypted data. This means Livewire payloads would be much bigger and consequently slower across the network.
2) The process of encrypting data using a secure algorithm takes a measurable amount of time.

In isolated cases, these tradeoffs are inconsequential and encrypting sensitive data makes perfect sense.
