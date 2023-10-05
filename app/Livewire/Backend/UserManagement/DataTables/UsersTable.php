<?php

namespace App\Livewire\Backend\UserManagement\DataTables;

use App\Enums\DataTables;
use App\Enums\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class UsersTable extends PowerGridComponent
{
    use WithExport;
    public string $actionName = "Add New User";
    public string $actionFunction = "addUser";
    public string $actionPermission;

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->actionPermission = PermissionsEnum::CREATE_ADMIN_USER->value;

        return [
            Exportable::make('Admin_users_' . \Carbon\Carbon::now()->format('YmdHis'))
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('components.datatable.header-top'),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('name')
            ->addColumn('email')
            ->addColumn('created_at_formatted', fn (User $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('email')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }



    public function actions(\App\Models\User $row): array
    {
        return [
            Button::add('edit-user')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-full w-9 h-9  ring-blue-500 text-white bg-blue-500 hover:bg-blue-600 hover:ring-blue-600
                                 dark:ring-offset-slate-800 dark:bg-blue-700 dark:ring-blue-700 dark:hover:bg-blue-600 dark:hover:ring-blue-600')
                ->dispatch('edit-user-details', [['id' => $row->id, 'name' => $row->name]]),
        ];
    }

    public function addUser(){
       $this->dispatch('add-new-user');
    }

    public function actionRules(): array
    {
        return [
            Rule::button('edit-user')
                ->when(fn ($role) => ($role->id == 1) ||  (!Auth::user()->can(PermissionsEnum::REMOVE_USER_ROLE->value)))
                ->hide(),
        ];
    }

    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                DataTables::ADMIN_USER_MANAGEMENT->reload() => 'reload'
            ]
        );
    }



    public function reload()
    {
        $this->refresh();
    }


}
