<?php

namespace App\Livewire\Backend\UserManagement\DataTables;

use App\Enums\DataTables;
use App\Enums\PermissionsEnum;
use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

final class RolesTable extends PowerGridComponent
{
    use WithExport;

    public string $actionName = "Add New Role";
    public string $actionFunction = "addRole";
    public string $actionPermission;

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->actionPermission = PermissionsEnum::CREATE_USER_ROLE->value;

        return [
            Exportable::make('User_Roles_' . \Carbon\Carbon::now()->format('YmdHis'))
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
        return Role::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('id')
            ->addColumn('permissions', function (Role $model) {
                if ($model->id === 1) {
                    return Blade::render('<span class="bg-green-100 text-purple-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-purple-200 dark:text-purple-900 text-xs">All</span>');
                }
                $permissionList = [];
                foreach ($model->permissions as $k => $permission) {
                    $permissionList[] = '<span class="bg-blue-100 text-purple-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-purple-200 dark:text-purple-900 text-xs">' . ucwords(strtolower(str_replace('-', " ", $permission->name))) . '</span>' . ($k % 2 == 1 ? '<br>' : '');
                }
                return Blade::render(implode('', $permissionList));
            })
            ->addColumn('permissions_export', function (Role $model) {
                if ($model->id === 1) {
                    return 'All';
                }
                $permissionList = '';
                foreach ($model->permissions as $k => $permission) {
                    $permissionList = $permissionList . ucwords(strtolower(str_replace('-', " ", $permission->name))) . ((count($model->permissions) != ($k + 1)) ? ', ' : '');
                }
                return $permissionList;
            })
            ->addColumn('created_at_formatted', fn(Role $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name'),
            Column::make('Permissions', 'permissions')->visibleInExport(false),
            Column::make('Permissions', 'permissions_export')->visibleInExport(true)->hidden(),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }


    public function actions(\App\Models\Role $row): array
    {
        return [
            Button::add('edit-user-role')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-full w-9 h-9  ring-blue-500 text-white bg-blue-500 hover:bg-blue-600 hover:ring-blue-600
                                 dark:ring-offset-slate-800 dark:bg-blue-700 dark:ring-blue-700 dark:hover:bg-blue-600 dark:hover:ring-blue-600')
                ->dispatch('edit-user-role', [['id' => $row->id, 'name' => $row->name]]),
            Button::add('delete-role')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-full w-9 h-9     ring-negative-500 text-white bg-negative-500 hover:bg-negative-600 hover:ring-negative-600
                        dark:ring-offset-slate-800 dark:bg-negative-700 dark:ring-negative-700 dark:hover:bg-negative-600 dark:hover:ring-negative-600')
                ->dispatch('delete-user-role', [['id' => $row->id, 'name' => $row->name]])
        ];
    }


    public function actionRules(): array
    {
        return [
            Rule::button('edit-user-role')
                ->when(fn ($role) => ($role->id == 1) || (!Auth::user()->can(PermissionsEnum::UPDATE_USER_ROLE->value)))
                ->hide(),
            Rule::button('delete-role')
                ->when(fn ($role) => ($role->id <= 1) || (!Auth::user()->can(PermissionsEnum::REMOVE_USER_ROLE->value)))
                ->hide()
        ];
    }



    public function addRole()
    {
        $this->dispatch('add-new-user-role');
    }


    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                DataTables::USER_ROLE_MANAGEMENT->reload() => 'reload'
            ]
        );
    }



    public function reload()
    {
        $this->refresh();
    }


}
