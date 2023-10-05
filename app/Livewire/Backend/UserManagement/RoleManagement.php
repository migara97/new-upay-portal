<?php

namespace App\Livewire\Backend\UserManagement;

use App\Enums\ActionList;
use App\Enums\DataTables;
use App\Enums\DualAuthSettingEnum;
use App\Enums\PermissionsEnum;
use App\Enums\PortalModules;
use App\Models\Backend\DualAuth\DualAuthSettings;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Models\Role;
use App\Models\User;
use App\Repository\DualAuthRepositoryInterface;
use App\Repository\DualAuthSettingsRepositoryInterface;
use App\Repository\Eloquent\DualAuthSettingsRepository;
use App\Repository\Eloquent\RoleRepository;
use App\Repository\RoleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use WireUi\Traits\Actions;

class RoleManagement extends Component
{
    use Actions;

    public string $formName = 'RoleManagement';
    public bool $dualAuthRequired;
    public $permissionList;
    public string $operationMethod;
    public string $modelTitle;
    public string $modelBtnTitle;
    public string $roleName = "";
    public array $permissions = [];
    public bool $isShowRoleModel;
    public bool $isPermissionError = false;
    public bool $pendingSummaryModel = false;
    public $summaryData;


    protected $listeners = [
        'add-new-user-role' => 'showAddUserRoleModel',
        'edit-user-role' => 'showUpdateUserRoleModel',
        'delete-user-role' => 'showRoleDeleteConfirmation',
        'open-summary-model' => 'openSummaryModel'
    ];

    protected $rules = [
        'roleName' => 'required|min:3|max:20|regex:/^[a-zA-Z][a-zA-Z  ]+[a-zA-Z]+$/|unique:roles,name'
    ];

    protected $messages = [
        'roleName.required' => 'Role Name field is required!',
        'roleName.min' => 'Role Name must be greater than 3 characters!',
        'roleName.max' => 'Role Name should be less than  20 characters!',
        'roleName.regex' => 'Role Name is invalid!',
        'roleName.unique' => 'This Role Name already exists!',
    ];

    public function mount(DualAuthSettingsRepository $dualAuthSettings)
    {
        $this->dualAuthRequired = $dualAuthSettings->needDualAuth([DualAuthSettingEnum::CREATE_USER_ROLE, DualAuthSettingEnum::UPDATE_USER_ROLE, DualAuthSettingEnum::REMOVE_USER_ROLE]);
        $allPermissionList = Permission::select(['id', 'name', 'category_id'])->get();
        $this->permissionList = get_formatted_permission_list($allPermissionList);
    }

    public function openSummaryModel($id)
    {
        $dualAuth = FormDualAuth::select('summary_data')->where('id', $id)->first();
        if ($dualAuth->summary_data != null) {
            $this->summaryData = json_decode($dualAuth->summary_data, true);
        } else {
            $this->summaryData = ['pre' => [], 'new' => []];
        }
        $this->pendingSummaryModel = true;
    }

    public function render()
    {
        return view('livewire.backend.user-management.role-management');
    }


    public function showAddUserRoleModel()
    {


        $this->modelTitle = 'Add New User';
        $this->modelBtnTitle = 'Save';
        $this->operationMethod = 'store';
        $this->reset(['roleName', 'permissions']);
        $this->resetErrorBag();
        $this->isPermissionError = false;
        $this->isShowRoleModel = true;

    }

    public function close()
    {
        $this->reset(['roleName', 'permissions']);
        $this->resetErrorBag();
        $this->isPermissionError = false;
        $this->isShowRoleModel = false;

    }

    public function store(DualAuthRepositoryInterface $dualAuthRepository, RoleRepositoryInterface $roleRepository, DualAuthSettingsRepositoryInterface $dualAuthSettingsRepository)
    {

        empty($this->permissions) ? $this->isPermissionError = true : $this->isPermissionError = false;
        $this->validate();
        $actionName = "Add New User Role";
        try {

            $permNameList = [];
            foreach ($this->permissions as $permission) {
                array_push($permNameList, $this->getPermissionName($permission));
            }


            array_push($this->permissions, PermissionsEnum::VIEW_BACKEND);
            $payload = json_encode([
                'role' => [
                    'name' => $this->roleName,
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                'permissions' => $this->permissions
            ]);

            $summaryData = [
                'pre' => [],
                'new' => [
                    'Role' => $this->roleName,
                    'Permissions' => ($permNameList)
                ]
            ];


            if ($dualAuthSettingsRepository->needDualAuth(DualAuthSettingEnum::CREATE_USER_ROLE->value)) {
                if ($dualAuthRepository->hasPendingUpdatesForPayload($payload, $this->formName)) {
                    $this->notification()->info(
                        'Information!',
                        'A pending new role already exists with provided details and waiting for approval!',
                    );
                    return false;
                }

                $dualAuthAction = $dualAuthRepository->create([
                    'form_name' => $this->formName,
                    'method' => FormDualAuth::METHOD_CREATE,
                    'model_type' => Role::class,
                    'new_payload' => $payload,
                    'permission' => PermissionsEnum::APPROVE_CREATE_ADMIN_USER->value,
                    'created_by' => auth()->user()->email,
                    'created_at' => Carbon::now(),
                    'repository_type' => RoleRepository::class,
                    'summary_data' => json_encode($summaryData),
                    'summary' => "Create [ $this->roleName ]"
                ]);

                activity_log(ActionList::CREATE_USER_ROLE->value . " pending for approval", $summaryData["new"], PortalModules::RoleManagement, null, $dualAuthAction->id);
                $this->close();
                $this->dispatch(DataTables::DUAL_AUTH->reload());

                $this->notification()->success(
                    'Role Creation Recorded!',
                    'New role creation recorded successfully and waiting for approval.',
                );
            } else {
                $thisRoleName = $this->roleName;
                $this->close();
                activity_log(ActionList::CREATE_USER_ROLE->value, $summaryData["new"], PortalModules::RoleManagement);
                $details = json_decode($payload, true)['role'];
                $permissions = json_decode($payload, true)['permissions'];
                $roleRepository->storeRoleCreate($details, $permissions);

                $this->dispatch(DataTables::USER_ROLE_MANAGEMENT->reload());

                $this->notification()->success(
                    'Role Created!',
                    'New role created successfully.',
                );
            }

            return true;
        } catch (\Exception $exception) {
            $this->close();
            Log::error("Role create failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
            $this->notification()->error(
                'Error !!!',
                'New role creation failed, please try again!'
            );
        }
        return false;
    }

    public function validateInput($name)
    {
        if ($name == 'roleName' && $this->modelBtnTitle == 'Update') {
            $this->validate(['roleName' => 'required|min:3|max:20|regex:/^[a-zA-Z][a-zA-Z  ]+[a-zA-Z]+$/']);
        } else {
            $this->validate([$name => $this->rules[$name]]);
        }

    }

    public function showUpdateUserRoleModel($data, RoleRepository $roleRepository)
    {

        $this->reset(['roleName', 'permissions']);
        $this->resetErrorBag();
        $role = $roleRepository->find($data['id']);
        $this->modelTitle = 'Update Role - ' . $data["name"];
        $this->modelBtnTitle = 'Update';
        $this->operationMethod = 'update(' . $role . ')';
        $this->roleName = $data["name"];

        $this->permissions = [];

        foreach ($role->permissions as $permission) {
            array_push($this->permissions, $permission->id);
        }
        $this->isShowRoleModel = true;
    }

    public function update(DualAuthRepositoryInterface $dualAuthRepository, DualAuthSettingsRepositoryInterface $dualAuthSettingsRepository, RoleRepositoryInterface $roleRepository, Role $role): bool
    {
        $actionName = "Update Role";
        empty($this->permissions) ? $this->isPermissionError = true : $this->isPermissionError = false;
        $this->validate(['roleName' => 'required|min:3|max:20|regex:/^[a-zA-Z][a-zA-Z  ]+[a-zA-Z]+$/']);

        try {

            if ($role->name !== $this->roleName) {
                $this->validate(['roleName' => 'unique:roles,name']);
            }

            $updatedRoleName = $this->roleName;

            $payload = json_encode([
                "data" => [
                    'role' => [
                        'name' => $updatedRoleName
                    ],
                    'permissions' => $this->permissions
                ],
                "id" => $role->id
            ]);

            $oldPermissionNames = [];
            $newPermissionNames = [];

            foreach ($role->permissions as $permission) {
                array_push($oldPermissionNames, $permission->name);
            }

            foreach ($this->permissions as $permission) {
                array_push($newPermissionNames, $this->getPermissionName($permission));
            }

            $summaryData = [
                'pre' => [
                    'Role' => $role->name,
                    'Permissions' => ($oldPermissionNames)
                ],
                'new' => [
                    'Role' => $updatedRoleName,
                    'Permissions' => ($newPermissionNames)
                ]
            ];

            if ($dualAuthSettingsRepository->needDualAuth(DualAuthSettingEnum::UPDATE_USER_ROLE->value)) {

                if ($dualAuthRepository->hasPendingUpdatesForPayload($payload, $this->formName)) {
                    $this->notification()->info(
                        'Information!',
                        'A pending role update already exists with provided details and waiting for approval!',
                    );
                    return false;
                }

                $dualAuthAction = $dualAuthRepository->create([
                    'form_name' => $this->formName,
                    'method' => FormDualAuth::METHOD_UPDATE,
                    'model_type' => Role::class,
                    'new_payload' => $payload,
                    'permission' => PermissionsEnum::APPROVE_UPDATE_USER_ROLE,
                    'created_by' => auth()->user()->email,
                    'created_at' => Carbon::now(),
                    'old_payload' => json_encode($role),
                    'repository_type' => RoleRepository::class,
                    'summary_data' => json_encode($summaryData),
                    'summary' => "Update Role: $role->name"
                ]);

                $this->close();
                $this->dispatch(DataTables::DUAL_AUTH->reload());

                activity_log(ActionList::UPDATE_USER_ROLE->value . " pending for approval", $summaryData["new"], PortalModules::RoleManagement, $summaryData["pre"], $dualAuthAction->id);

                $this->notification()->success(
                    'Role Updated!',
                    'Role update successfully recorded as pending.',
                );
            } else {
                $thisRoleName = $this->roleName;

                $this->close();

                $decodedPayload = json_decode($payload, true);
                $decodedPayloadData = $decodedPayload["data"];

                $details = $decodedPayloadData['role'];
                $permissions = $decodedPayloadData['permissions'];

                $roleRepository->storeRoleUpdate($decodedPayload['id'], $details, $permissions);
                activity_log(ActionList::UPDATE_USER_ROLE->value, $summaryData["new"], PortalModules::RoleManagement, $summaryData["pre"]);

                $this->notification()->success(
                    'Role Updated!',
                    'Role updated successfully.',
                );
            }

            $this->dispatch(DataTables::USER_ROLE_MANAGEMENT->reload());
            return true;
        } catch (\Exception $exception) {
            $this->close();
            Log::error("Role update failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
            $this->notification()->error(
                'Error !!!',
                'Role update failed, please try again!'
            );
        }

        return false;
    }

    public function showRoleDeleteConfirmation($data)
    {
        $roleName = $data['name'];
        $roleID = $data['id'];

        $this->dialog()->confirm([
            'title' => 'Are you Sure?',
            'description' => "Delete this  $roleName Role?",
            'icon' => 'question',
            'accept' => [
                'label' => 'Yes, delete it',
                'method' => 'delete',
                'params' => $roleID,
                'color' => 'negative'
            ],
            'reject' => [
                'label' => 'No, cancel'
            ],
        ]);
    }

    public function delete($id, DualAuthRepositoryInterface $dualAuthRepository, DualAuthSettingsRepositoryInterface $dualAuthSettingsRepository, RoleRepositoryInterface $roleRepository)
    {

        $actionName = "Remove Role";
        $role = $roleRepository->find($id);
        try {

            $payload = json_encode([
                'id' => $role->id
            ]);

            $summaryData = [
                'common' => "Delete Role [ $role->name ]."
            ];

            if ($dualAuthSettingsRepository->needDualAuth(DualAuthSettingEnum::REMOVE_USER_ROLE->value)) {
                if ($dualAuthRepository->hasPendingUpdatesForPayload($payload, $this->formName)) {
                    $this->notification()->info(
                        'Information!',
                        'A pending remove for this role already exists and waiting for approval!',
                    );
                    return;
                }


                $dualAuthAction = $dualAuthRepository->create([
                    'form_name' => $this->formName,
                    'method' => FormDualAuth::METHOD_DELETE,
                    'model_type' => Role::class,
                    'new_payload' => $payload,
                    'permission' => PermissionsEnum::APPROVE_REMOVE_USER_ROLE,
                    'created_by' => auth()->user()->email,
                    'created_at' => Carbon::now(),
                    'old_payload' => json_encode($role),
                    'repository_type' => RoleRepository::class,
                    'summary_data' => json_encode($summaryData),
                    'summary' => "Delete Role [ $role->name ]."
                ]);


                activity_log(ActionList::REMOVE_USER_ROLE->value . " pending for approval", $summaryData["common"], PortalModules::RoleManagement, null, $dualAuthAction->id);
                $this->dispatch(DataTables::DUAL_AUTH->reload());

                $this->notification()->success(
                    'Role Deleted!',
                    'Role remove recorded. Waiting for approval.',
                );
            } else {
                $roleRepository->deleteRoleFromDb($role->id);
                activity_log(ActionList::REMOVE_USER_ROLE->value, $summaryData["common"], PortalModules::RoleManagement);
                $this->notification()->success(
                    'Role Deleted!',
                    'Role remove successfully.',
                );
            }
            $this->dispatch(DataTables::USER_ROLE_MANAGEMENT->reload());
        } catch (\Exception $exception) {
            $this->close();
            Log::error("Role remove failed: Exception -> " . $exception->getMessage());
            $this->notification()->error(
                'Error !!!',
                'Role remove failed, please try again!'
            );
        }
    }


    private function getPermissionName($id)
    {
        return Permission::find($id)->name;
    }
}
