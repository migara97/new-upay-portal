<div class="grid grid-cols-1 gap-2">
    <x-input label="Email" placeholder="Email" wire:model.defer="email" wire:change="validateInput('email')" />

    @if(($this->allowLdap))
        <x-input label="Name" placeholder="Name" wire:model.defer="name" wire:change="validateInput('name')"
                 :readonly="true"/>
        <label class="font-bold">
            Associated Roles
        </label>
        @if($this->isRoleError)
            <p class="mt-2 text-sm text-negative-600">
                Please select a role!
            </p>
        @endif
        <div class="grid grid-cols-4 md:grid-cols-5 gap-2">
            @foreach ($this->roleList as $k => $role)
                <div class="p-1 rounded">
                    <x-checkbox id="role-{{ $role->id }}" value="{{ $role->id }}"
                                label="{{ $role->name }}" wire:model.defer="roles"/>
                    <div class="container mx-auto mt-2">
                        <div class="max-w-2xl"
                             x-data="{ faqOne: false, activeClass: 'bg-indigo-800 text-white focus:outline-none', }"
                             class="">
                            <div class="mb-4">
                                <div @click="faqOne = ! faqOne" :class="faqOne === true ? activeClass : ''"
                                     class="flex items-center justify-between w-full px-2 bg-blue-200  rounded cursor-pointer text-xs">
                                    View Permissions
                                    <span class="flex items-center justify-center">
                                                <svg :class="faqOne === true ? 'block' : 'hidden'"
                                                     class="w-3 h-3 fill-current" viewBox="0 -192 469.33333 469"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="m437.332031.167969h-405.332031c-17.664062 0-32 14.335937-32 32v21.332031c0 17.664062 14.335938 32 32 32h405.332031c17.664063 0 32-14.335938 32-32v-21.332031c0-17.664063-14.335937-32-32-32zm0 0"/>
                                                </svg>
                                                <svg :class="faqOne === false ? 'block' : 'hidden'"
                                                     class="w-3 h-3 fill-current" viewBox="0 0 469.33333 469.33333"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="m437.332031 192h-160v-160c0-17.664062-14.335937-32-32-32h-21.332031c-17.664062 0-32 14.335938-32 32v160h-160c-17.664062 0-32 14.335938-32 32v21.332031c0 17.664063 14.335938 32 32 32h160v160c0 17.664063 14.335938 32 32 32h21.332031c17.664063 0 32-14.335937 32-32v-160h160c17.664063 0 32-14.335937 32-32v-21.332031c0-17.664062-14.335937-32-32-32zm0 0"/>
                                                </svg>
                                            </span>
                                </div>
                                <div x-show="faqOne" x-collapse class="border border-gray-300 p-1 rounded">

                                    @if (count($role->permissions))
                                        @if ($role->name === config('access.users.admin_role'))
                                            <x-button outline label="All" primary 2xs icon="check"/>
                                        @else
                                            @foreach ($role->permissions as $key => $permissions)
                                                <x-button outline
                                                          label="{{ ucwords(str_replace('-', ' ', $permissions->name)) }}"
                                                          positive 2xs icon="check"/>
                                            @endforeach
                                        @endif
                                    @else
                                        <p class="text-2xs text-gray-500 mx-auto mt-2">No permissions</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    @endif
</div>

<x-slot name="footer">
    <div class="flex justify-between gap-x-4">
        <x-button flat label="Cancel" wire:click="close"/>
        @if(($this->allowLdap))
            <x-button primary label="{{ ucwords($this->modelBtnTitle) }}" wire:click="{{ $this->operationMethod }}"/>
        @else
            <x-button primary label="Proceed" wire:click="checkAD"/>
        @endif
    </div>
</x-slot>
