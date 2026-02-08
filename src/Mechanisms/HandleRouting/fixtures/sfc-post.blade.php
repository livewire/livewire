<?php

new class extends Livewire\Component {
    public \Livewire\Mechanisms\HandleRouting\RoutingPost $post;
};
?>

<div>
    <span>Post: {{ $post->title }}</span>
</div>
