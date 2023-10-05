<?php

namespace App\Livewire\Backend\UserManagement;

use Adldap\Laravel\Facades\Adldap;
use App\Enums\ActionList;
use App\Enums\DataTables;
use App\Enums\DualAuthSettingEnum;
use App\Enums\PermissionsEnum;
use App\Enums\PortalModules;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Models\Role;
use App\Models\User;
use App\Repository\AdminUserRepositoryInterface;
use App\Repository\DualAuthRepositoryInterface;
use App\Repository\DualAuthSettingsRepositoryInterface;
use App\Repository\Eloquent\AdminUserRepository;
use App\Repository\Eloquent\DualAuthSettingsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use WireUi\Traits\Actions;

class UserManagement extends Component
{
    use Actions;

    public string $formName = 'UserManagement';
    public bool $isUserModel = false;
    public string $operationMethod ="submit";
    public string $modelTitle;
    public string $modelBtnTitle = "Save";
    public bool $isRoleError = false;
    public bool $isEdit = false;

    public $permissionList;
    public $roleList;
    public array $permissions = [];
    public array $roles = [];

    public $name = "";
    public $email = "";
    public $password;
    public $password_confirmation;
    public bool $dualAuthRequired = false;
    public bool $pendingSummaryModel = false;
    public $summaryData;

    public bool $withOutLdap = false;
    public bool $allowLdap = false;


    protected $listeners = [
        'add-new-user' => 'showAddUserModel',
        'edit-user-details' => 'showUpdateUserModel',
        'open-summary-model' => 'openSummaryModel'
    ];

    protected $rules = [
        'email' => 'required|email|unique:users,email',
        'name' => 'required|min:3|max:50',
        'password' => 'required|min:5|max:20|same:password_confirmation',
        'password_confirmation' => 'required|min:5'
    ];

    protected $messages = [
        'name.required' => 'Name field is required!',
        'name.min' => 'Name must be greater than 3 characters!',
        'name.max' => 'Name should be less than  50 characters!',
        'roles.required' => 'Please select a role!',
        'email.required' => 'Email field is required!',
        'email.email' => 'Email field is invalid!',
        'email.unique' => 'This Email has already been taken!',
        'password.required' => 'Password field is required!',
        'password.min' => 'Password must be greater than 5 characters!',
        'password.max' => 'Password should be less than  20 characters!',
        'password.same' => 'Passwords do not match!',
        'password_confirmation.min' => 'Password Confirmation must be greater than 5 characters!',
        'password_confirmation.required' => 'Password Confirmation field is required!',
    ];

    public function mount(DualAuthSettingsRepository $dualAuthSettings)
    {

        $this->dualAuthRequired = $dualAuthSettings->needDualAuth([DualAuthSettingEnum::CREATE_ADMIN_USER, DualAuthSettingEnum::UPDATE_ADMIN_USER]);
        $this->roleList = Role::select(['id', 'name'])->get();
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
        return view('livewire.backend.user-management.user-management');
    }

    public function showAddUserModel()
    {

        $this->isEdit = false;
        $this->modelTitle = 'Add New User';
        $this->modelBtnTitle = 'Save';
        $this->operationMethod = 'store';
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->withOutLdap = false;
        $this->permissions = [];
        $this->allowLdap = false;
        $this->resetErrorBag();
        $this->isRoleError = false;
        $this->isUserModel = true;

    }


    public function close()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->resetErrorBag();
        $this->withOutLdap = false;
        $this->permissions = [];
        $this->allowLdap = false;
        $this->isRoleError = false;
        $this->isUserModel = false;

    }

    public function checkAD()
    {
        $this->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        $this->allowLdap = true;
//        try {
//            $users = Adldap::search()->users()->where('mail', '=', trim($this->email))->get();
//            if (count($users) > 0) {
//                $user = $users[0];
//                $this->name = $user->cn[0];
//                $this->allowLdap = true;
//            } else {
//                $this->allowLdap = false;
//                $this->close();
//                $this->notification()->error(
//                    'Error !!!',
//                    'This Email cannot find in AD!'
//                );
//            }
//        } catch (\Exception $e) {
//            $this->allowLdap = false;
//            Log::error("Ldap check user email failed. Exception > " . $e->getMessage());
//            $this->close();
//            $this->notification()->error(
//                'Error !!!',
//                'AD Service Failed!'
//            );
//        }
    }


    public function store(DualAuthRepositoryInterface $dualAuthRepository, DualAuthSettingsRepositoryInterface $dualAuthSettingsRepository, AdminUserRepositoryInterface $adminUserRepository)
    {
        empty($this->roles) ? $this->isRoleError = true : $this->isRoleError = false;

        $this->validate();
        $actionName = "Add New User";

        $hasAdminRole = in_array("1", $this->roles);

        if ($hasAdminRole) {
            $adminPermissions = [];

            foreach ($this->permissionList as $permissionCategory) {
                foreach ($permissionCategory as $key => $permission) {
                    array_push($adminPermissions, $permission["id"]);
                }
            }

            $this->permissions = $adminPermissions;
        }


        try {

            $payload = [
                "data" => [
                    'roles' => $this->roles,
                    'permissions' => $this->permissions,
                    'user' => [
                        'email' => $this->email,
                        'name' => $this->name,
                        'password' => Hash::make($this->password),
                    ]
                ]
            ];


            $newPermissionNames = [];
            $newRoleNames = [];

            foreach ($this->permissions as $permission) {
                array_push($newPermissionNames, $this->getPermissionName($permission));
            }

            foreach ($this->roles as $role) {
                array_push($newRoleNames, $this->getRoleName($role));
            }


            $summaryData = [
                'pre' => [],
                'new' => [
                    'Name' => $this->name,
                    'Email' => $this->email,
                    'Permissions' => ($newPermissionNames),
                    'Roles' => ($newRoleNames)
                ]
            ];

            if ($dualAuthSettingsRepository->needDualAuth(DualAuthSettingEnum::CREATE_ADMIN_USER->value)) {

                if ($dualAuthRepository->hasPendingUpdatesForPayload($payload, $this->formName)) {
                    $this->close();
                    $this->notification()->info(
                        'Information!',
                        'A pending user create already exists with provided details and waiting for approval!',
                    );
                    return false;
                }

                $dualAuthAction = $dualAuthRepository->create([
                    'form_name' => $this->formName,
                    'method' => FormDualAuth::METHOD_CREATE,
                    'model_type' => User::class,
                    'new_payload' => json_encode($payload),
                    'permission' => PermissionsEnum::APPROVE_CREATE_ADMIN_USER,
                    'created_by' => auth()->user()->email,
                    'created_at' => Carbon::now(),
                    'repository_type' => AdminUserRepository::class,
                    'summary' => "Create Admin User - " . ($this->name),
                    'summary_data' => json_encode($summaryData)
                ]);

                activity_log(ActionList::CREATE_USER->value . " pending for approval", $summaryData["new"], PortalModules::UserManagement, null, $dualAuthAction->id);

                $this->close();
                $this->dispatch(DataTables::DUAL_AUTH->reload());

                $this->notification()->success(
                    'Update Recorded!',
                    'User creation request successfully recorded as pending.',
                );
            } else {

                $adminUserRepository->createUser($payload["data"]);
                activity_log(ActionList::CREATE_USER->value, $summaryData["new"], PortalModules::UserManagement);
                $this->close();
                $this->notification()->success(
                    'User Created!',
                    'New admin user created successfully.',
                );
            }

            $this->dispatch(DataTables::ADMIN_USER_MANAGEMENT->reload());

            return true;
        } catch (\Exception $exception) {
            $this->close();
            Log::error("User update failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
            $this->notification()->error(
                'Error !!!',
                'User create failed, please try again!'
            );
        }

        return false;

    }

    public function showUpdateUserModel($data, AdminUserRepository $adminUserRepository)
    {
        $this->reset(['name', 'email']);
        $this->resetErrorBag();
        $this->withOutLdap = false;
        $this->allowLdap = false;
        $user = $adminUserRepository->find($data['id']);
        $this->modelTitle = 'Update Role - ' . $data["name"];
        $this->modelBtnTitle = 'Update';
        $this->operationMethod = 'update(' . $user . ')';
        $this->name = $data["name"];
        $this->email = $user->email;
        $this->isRoleError = false;
        $this->isEdit = true;
        $this->roles = [];

        foreach ($user->roles as $role) {
            array_push($this->roles, $role->id);
        }
        $this->isUserModel = true;
    }

    public function update(User $user, DualAuthSettingsRepositoryInterface $dualAuthSettingsRepository, DualAuthRepositoryInterface $dualAuthRepository, AdminUserRepositoryInterface $adminUserRepository)
    {
        empty($this->roles) ? $this->isRoleError = true : $this->isRoleError = false;

        $this->validate([
            'email' => 'required|email',
            'name' => 'required|min:3|max:50']);

        if ($user->email !== $this->email) {
            $this->validate(['email' => 'unique:users,email']);
        }

        $actionName = "Update User";

        $hasAdminRole = in_array("1", $this->roles);

        if ($hasAdminRole) {
            $adminPermissions = [];

            foreach ($this->permissionList as $permissionCategory) {
                foreach ($permissionCategory as $key => $permission) {
                    array_push($adminPermissions, $permission["id"]);
                }
            }

            $this->permissions = $adminPermissions;
        }

        try {
            $payload = [
                "data" => [
                    'user' => [
                        'name' => $this->name,
                        'email' => $this->email
                    ],
                    'roles' => $this->roles,
                    'permissions' => $this->permissions,
                ],
                "id" => $user->id,
            ];

            $oldPermissions = [];
            $oldPermissionNames = [];
            $newPermissionNames = [];

            $oldRoles = [];
            $oldRoleNames = [];
            $newRoleNames = [];

            foreach ($user->permissions as $permission) {
                array_push($oldPermissions, $permission->id);
                array_push($oldPermissionNames, $permission->name);
            }

            foreach ($this->permissions as $permission) {
                array_push($newPermissionNames, $this->getPermissionName($permission));
            }

            foreach ($user->roles as $role) {
                array_push($oldRoles, $role->id);
                array_push($oldRoleNames, $role->name);
            }

            // Role Names - New
            foreach ($this->roles as $role) {
                array_push($newRoleNames, $this->getRoleName($role));
            }


            $oldPayload = [
                "data" => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $oldRoles,
                    'permissions' => $oldPermissions
                ],
                "id" => $user->id,
            ];

            $summaryRoleNames = json_encode($oldRoleNames) != json_encode($newRoleNames) ? "Roles from " . ($oldRoleNames ? json_encode($oldRoleNames) : "NONE") . " to " . json_encode($newRoleNames) . "," : "";
            $summaryPermissionNames = json_encode($oldPermissionNames) != json_encode($newPermissionNames) ? "Permissions from " . ($oldPermissionNames ? json_encode($oldPermissionNames) : "NONE") . " to " . ($hasAdminRole ? "ALL" : json_encode($newPermissionNames)) : "";

            $summaryData = [
                'pre' => [
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Permissions' => $oldPermissionNames,
                    'Roles' => $oldRoleNames
                ],
                'new' => [
                    'Name' => $this->name,
                    'Email' => $this->email,
                    'Permissions' => ($newPermissionNames),
                    'Roles' => ($newRoleNames)
                ]
            ];

            if ($dualAuthSettingsRepository->needDualAuth(DualAuthSettingEnum::UPDATE_ADMIN_USER->value)) {

                if ($dualAuthRepository->hasPendingUpdatesForPayload($payload, $this->formName)) {
                    $this->notification()->info(
                        'Information!',
                        'A pending user update already exists with provided details and waiting for approval!',
                    );
                    return false;
                }

                $dualAuthAction = $dualAuthRepository->create([
                    'form_name' => $this->formName,
                    'method' => FormDualAuth::METHOD_UPDATE,
                    'model_type' => User::class,
                    'new_payload' => json_encode($payload),
                    'permission' => PermissionsEnum::APPROVE_UPDATE_ADMIN_USER,
                    'created_by' => auth()->user()->email,
                    'created_at' => Carbon::now(),
                    'old_payload' => json_encode($oldPayload),
                    'repository_type' => AdminUserRepository::class,
                    'summary' => "Update user: $summaryRoleNames $summaryPermissionNames.",
                    'summary_data' => json_encode($summaryData)
                ]);

                activity_log(ActionList::UPDATE_USER->value . " pending for approval", $summaryData["new"], PortalModules::UserManagement, $summaryData["pre"], $dualAuthAction->id);
                $this->close();
                $this->dispatch(DataTables::DUAL_AUTH->reload());

                $this->notification()->success(
                    'Update Recorded!',
                    'User update request successfully recorded as pending!',
                );
            } else {


                $adminUserRepository->updateUser($payload['data'], $payload['id']);
                activity_log(ActionList::UPDATE_USER->value, $summaryData["new"], PortalModules::UserManagement, $summaryData["pre"]);

                $this->close();

                $this->notification()->success(
                    'User Updated!',
                    'User updated successfully!',
                );
            }

            $this->dispatch(DataTables::ADMIN_USER_MANAGEMENT->reload());
            return true;
        } catch (\Exception $exception) {
            $this->close();
            Log::error("Role update failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
            $this->notification()->error(
                'Error !!!',
                'User update failed, please try again!'
            );
        }

        return false;

    }


    public function validateInput($name)
    {
        if ($name == 'email' && $this->modelBtnTitle == 'Update') {
            $this->validate(['email' => 'required|email']);
        } else {
            $this->validate([$name => $this->rules[$name]]);
        }
    }

    public function checkWithoutLDAP(){
        $this->withOutLdap ? 1 : 0;
    }

    private function getPermissionName($id)
    {
        return Permission::find($id)->name;
    }

    private function getRoleName($id)
    {
        return Role::find($id)->name;
    }
}
