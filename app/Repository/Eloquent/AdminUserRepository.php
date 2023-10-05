<?php

namespace App\Repository\Eloquent;

use App\Enums\ActionList;
use App\Enums\PortalModules;
use App\Events\DualAuthApproved;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Models\User;
use App\Models\UserPassword;
use App\Repository\AdminUserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserRepository extends BaseRepository implements AdminUserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getAdminCount()
    {
        return $this->model->newQuery()
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.role_id', 1)
            ->count();
    }

    public function store(FormDualAuth $dualAuth)
    {
        $actionName = "Dual Auth Approve - Manage User ";
        $data = json_decode($dualAuth->new_payload, true);


        if ($dualAuth->method == FormDualAuth::METHOD_CREATE) {
            try {
                $this->createUser($data['data']);
            } catch (\Exception $e) {
                Log::info($actionName . "[Create] Failed!" . 'ID (' . $dualAuth->id . '). Exception: ' . $e->getMessage() . " - " . $e->getLine());
                return;
            }
        } else if ($dualAuth->method == FormDualAuth::METHOD_UPDATE) {
            try {
                $this->updateUser($data['data'], $data['id']);
            } catch (\Exception $e) {
                Log::info($actionName . "[Update] Failed!" . 'ID (' . $dualAuth->id . '). Exception: ' . $e->getMessage() . " - " . $e->getLine());
                return;
            }
        } else {
            return;
        }

        event(new DualAuthApproved($dualAuth));
        $summary = json_decode($dualAuth->summary_data, true);
        activity_log(ActionList::APPROVE_DUAL_AUTH_ACTION->value, empty($summary["new"]) ? $summary["common"] : $summary["new"], PortalModules::DualAuthAction, empty($summary["pre"]) ? null : $summary["pre"], $dualAuth->id);
        return $dualAuth;

    }

    public function createUser($data)
    {
        $user = User::create($data['user']);
        $user->syncRoles($data['roles']);
        $user->syncPermissions($data['permissions']);
    }

    public function updateUser($data, $id)
    {
        $user = User::find($id);
        $user->update($data['user']);
        $user->syncRoles($data['roles']);
        $user->syncPermissions($data['permissions']);
    }

    public function getAttemptsCount($email)
    {

        $user = $this->model->newQuery()->where('email', '=', $email);
        if ($user->exists()) {
            $newCount = $user->first()->attempts_count + 1;
            $this->setAttemptsCount($email, $newCount);

            $maxAttempts = 3;

            if ($maxAttempts <= $newCount) {
                $this->model->newQuery()->where('email', '=', $email)->update(['status' => User::USER_LOCKED]);
                activity_log(ActionList::ACCOUNT_HAS_BEEN_LOCKED->value, "Email : $email", PortalModules::AccessManagement);
                return "Your Account has been locked. Because you have reached the maximum number of invalid logon attempts. Please contact system admin!";
            } else {
                $remainingRetry = $maxAttempts - $newCount;
                $remainingRetry = $remainingRetry == 0 ? $remainingRetry = "Last" : $remainingRetry;
                activity_log(ActionList::ADMIN_LOGIN_FAILED_ATTEMPT->value, "Email : $email", PortalModules::AccessManagement);
                return "Email or Password invalid!. $remainingRetry attempt remaining.";
            }

        } else {
            activity_log(ActionList::TRY_TO_LOGIN_USING_INVALID_EMAIL->value, "Email : $email", PortalModules::AccessManagement);
            return "This email is not exists!";
        }


    }

    public function setAttemptsCount($email, $count)
    {
        $this->model->newQuery()->where('email', '=', $email)->update(['attempts_count' => $count]);
    }

    public function isOldPassword($email, $password)
    {
        $oldPasswordList = UserPassword::where('email', $email)->orderBy("id", "desc")->limit(10)->get();
        $res = false;
        foreach ($oldPasswordList as $hashed_password) {
            if (Hash::check($password, $hashed_password->password)) {
                $res = true;
                break;
            }
        }

        return $res;
    }

    public function changePassword($email, $password)
    {
        User::where('email', $email)->update(['password' => Hash::make($password), 'password_changed_at' => Carbon::now()]);
        UserPassword::create(['email' => $email, 'password' => Hash::make($password)]);
    }

    public function updateAdminUser($data)
    {
        try {

            $updateData = json_decode($data, true);
            $user = User::find($updateData["id"]);
            if (isset($updateData["password"])) {
                $updateData["password_changed_at"] = Carbon::now();
            }

            $this->model->newQuery()->where('email', '=', $user->email)->update($updateData);

            return true;
        } catch (\Exception $exception) {
            Log::info("Admin User update failed. Exception: " . $exception->getMessage() . " - " . $exception->getLine());
        }
        return false;
    }
}
