<div class="bg-amber-200">
 <div>
     <x-select

         label="Select Status"

         placeholder="Select one status"

         :options="['Active', 'Pending', 'Stuck', 'Done']"

         wire:model.defer="model"

     />
     <x-button positive label="Positive" />
 </div>
    <livewire:user-table tableName="userTable"  />
</div>
