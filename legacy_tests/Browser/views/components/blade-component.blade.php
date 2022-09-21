@props(['property'])

<div {{ $attributes }}>
    {{ $this->getPropertyValue($property) }}
</div>
