<div>

    <x-backend.content-header title="Users Management"/>
    <x-card class="relative">
        <livewire:backend.user-management.data-tables.users-table tableName="userTable"/>
    </x-card>

    <x-modal.card title="{{ $modelTitle }}" wire:model.defer="isUserModel" max-width="4xl" :hide-close="false">
        @if(config('auth.settings.auth_type') == 'db-only')
            <x-backend.includes.user-management-forms.db-only-form />
        @elseif(config('auth.settings.auth_type') == 'ad-only')
            <x-backend.includes.user-management-forms.ad-only-form />
        @else
            <x-backend.includes.user-management-forms.ad-and-db-form />
        @endif

    </x-modal.card>


    @if ($dualAuthRequired)
        <div class="pt-4 relative">
            <x-card title="Pending Items">
                <livewire:backend.dual-auth.data-table.dual-auth-table tableName="dualAuthTable"
                                                                       form_name="{{ $formName }}"/>
            </x-card>
        </div>

        <x-backend.pending-summary/>
    @endif


</div>
