<?php

namespace App\Repository\Eloquent;

use App\Enums\ActionList;
use App\Enums\PortalModules;
use App\Events\DualAuthApproved;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Models\Role;
use App\Repository\RoleRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }


    public function store(FormDualAuth $dualAuth)
    {
        $data = json_decode($dualAuth->summary_data, true);
        switch ($dualAuth->method) {
            case FormDualAuth::METHOD_CREATE:
                activity_log(ActionList::APPROVE_DUAL_AUTH_ACTION->value, empty($data["new"]) ? $data["common"] : $data["new"], PortalModules::DualAuthAction, empty($data["pre"]) ? null : $data["pre"], $dualAuth->id);
                return $this->createRole($dualAuth);
            case FormDualAuth::METHOD_UPDATE:
                activity_log(ActionList::APPROVE_DUAL_AUTH_ACTION->value, empty($data["new"]) ? $data["common"] : $data["new"], PortalModules::DualAuthAction, empty($data["pre"]) ? null : $data["pre"], $dualAuth->id);
                return $this->updateRole($dualAuth);
            case FormDualAuth::METHOD_DELETE:
                activity_log(ActionList::APPROVE_DUAL_AUTH_ACTION->value, empty($data["new"]) ? $data["common"] : $data["new"], PortalModules::DualAuthAction, empty($data["pre"]) ? null : $data["pre"], $dualAuth->id);
                return $this->deleteRole($dualAuth);
            default:
                Log::warning('Store pending update ' . $dualAuth->id . '. Failed: Unimplemented method handle.');

        }

    }

    private function createRole(FormDualAuth $dualAuth)
    {
        $payload = json_decode($dualAuth->new_payload);
        try {
            $details = json_decode(json_encode($payload->role), true);
            $permissions = $payload->permissions;
            $role = $this->storeRoleCreate($details, $permissions);
            event(new DualAuthApproved($dualAuth));
            return $dualAuth;
        } catch (\Exception $exception) {
            Log::warning('Store pending update ' . $dualAuth->id . '. Exception: ' . $exception->getMessage() . ' - ' . $exception->getLine());
        }
    }

    public function updateRole(FormDualAuth $dualAuth)
    {
        $oldData = json_decode($dualAuth->old_payload, true);
        $newData = json_decode($dualAuth->new_payload, true);
        try {
            $details = json_decode(json_encode($newData["data"]["role"]), true);
            $permissions = json_decode(json_encode($newData["data"]["permissions"]), true);
            $roleUpdated = $this->storeRoleUpdate($oldData["id"], $details, $permissions);
            event(new DualAuthApproved($dualAuth));
            return $dualAuth;
        } catch (\Exception $exception) {
            Log::warning('Store pending update ' . $dualAuth->id . '. Exception: ' . $exception->getMessage() . ' - ' . $exception->getLine());
        }
    }

    public function storeRoleCreate(array $details, array $permissions)
    {
        $role = Role::create($details);
        $role->givePermissionTo($permissions);
        return $role;
    }

    public function storeRoleUpdate($id, array $details, array $permissions)
    {
        $role = Role::find($id);
        $roleUpdated = $role->update($details);
        $role->syncPermissions($permissions);
        $users = $role->users;

        foreach ($users as $user) {
            $user->syncPermissions($permissions);
        }

        return $roleUpdated;
    }

    public function deleteRole(FormDualAuth $dualAuth)
    {
        $payload = json_decode($dualAuth->new_payload);
        try {
            $role = $this->deleteRoleFromDb($payload->id);
            event(new DualAuthApproved($dualAuth));
            return $dualAuth;
        } catch (\Exception $exception) {
            Log::warning('Store pending update ' . $dualAuth->id . '. Exception: ' . $exception->getMessage() . " - " . $exception->getLine());
        }
    }

    public function deleteRoleFromDb($id)
    {
        return Role::find($id)->delete();
    }
}
