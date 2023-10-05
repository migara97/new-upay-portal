<div>

    <x-backend.content-header title="Roles Management"/>
    <x-card class="relative">
        <livewire:backend.user-management.data-tables.roles-table tableName="roleTable"/>
    </x-card>
    <x-modal.card title="{{ $modelTitle }}" wire:model.defer="isShowRoleModel" max-width="4xl" :hide-close="false">

        <div class="grid grid-cols-1 gap-2">
            <x-input label="Name" placeholder="Role Name" wire:model="roleName"
                     wire:change="validateInput('roleName')"/>

            <label class="font-bold">
                Associated Permissions
            </label>
            @if($this->isPermissionError)
                <p class="mt-2 text-sm text-negative-600">
                    Please select a permission!
                </p>
            @endif
            <div class="grid grid-cols-3 md:grid-cols-3 gap-2">
                @foreach ($permissionList as $category => $permissions)
                    @if($category != \App\Enums\PermissionCategory::Other->labels())
                        <div class="shadow rounded p-2">
                            <label class="font-semibold">{{ $category }}</label>

                            @foreach($permissions as $permission)
                                <div class="mt-2">
                                    <x-checkbox id="{{ $permission['id'] }}" md wire:model="permissions"
                                              value="{{ $permission['id'] }}"
                                              label="{{ ucwords(str_replace('-', ' ', $permission['name'])) }}"/>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

        </div>

        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <x-button flat label="Cancel" wire:click="close"/>
                <x-button primary spiner label="{{ ucwords($modelBtnTitle) }}" wire:click="{{ $operationMethod }}"/>
            </div>
        </x-slot>
    </x-modal.card>


    @if ($dualAuthRequired)
        <div class="pt-4 relative">
            <x-card title="Pending Items">
                <livewire:backend.dual-auth.data-table.dual-auth-table tableName="dualAuthTable"
                                                                       form_name="{{ $formName }}"/>
            </x-card>
        </div>
        <!-- summary model -->
        <x-backend.pending-summary/>
    @endif


</div>
