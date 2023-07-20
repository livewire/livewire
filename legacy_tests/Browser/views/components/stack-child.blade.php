@once
    @push('scripts')
        <script>window.stack_output.push('child-blade-scripts')</script> 
    @endpush
@endonce

@push('scripts')
    <script>window.stack_output.push('child-blade-scripts-no-once')</script> 
@endpush
