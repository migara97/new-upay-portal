@if(isset($link_id))
<div class="mt-4 mb-4">
    <x-button label="Clear Session"  wire:click="reload" flat gray sm right-icon="x" />
</div>
@endif
