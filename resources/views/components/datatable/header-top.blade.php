@if(auth()->user()->can($actionPermission))
<div class="mb-4">
    <x-button label="{{$actionName}}" positive md icon="plus-circle" wire:click="{{$actionFunction}}" />
</div>
@endif
