<?php

new class extends Livewire\Component {
    public \Livewire\Features\SupportRouting\RoutingPost $post;
};
?>

<div>
    <span>Post: {{ $post->title }}</span>
</div>
