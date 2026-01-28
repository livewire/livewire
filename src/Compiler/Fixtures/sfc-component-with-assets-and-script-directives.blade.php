<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- An unexamined life is not worth living. - Socrates --}}
</div>

@assets
<script>
    console.log('This should NOT be extracted - it is inside @assets');
</script>
@endassets

@script
<script>
    console.log('This should NOT be extracted - it is inside @script');
</script>
@endscript
