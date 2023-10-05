<?php

namespace App\Repository\Eloquent;

use App\Enums\ActionList;
use App\Enums\PortalModules;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Repository\DualAuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DualAuthRepository extends BaseRepository implements DualAuthRepositoryInterface
{
    public function __construct(FormDualAuth $model)
    {
        parent::__construct($model);
    }

    public function rejectDualAuth(FormDualAuth $dualAuth)
    {
        $dualAuth->approved_by = Auth::user()->email;
        $dualAuth->approved_at = Carbon::now();
        $dualAuth->status = FormDualAuth::STATUS_REJECT;
        $dualAuth->save();
        $data = json_decode($dualAuth->summary_data, true);
        activity_log(ActionList::REJECT_DUAL_AUTH_ACTION->value, empty($data["new"]) ? $data["common"] : $data["new"], PortalModules::DualAuthAction, empty($data["pre"]) ? null : $data["pre"], $dualAuth->id);
    }

    public function approve(FormDualAuth $dualAuth)
    {
        return resolve($dualAuth->repository_type)->store($dualAuth);
    }

    public function hasPendingUpdatesForPayload($payload, $formName = null): int
    {
        $dualAuth = FormDualAuth::query()
            ->where('new_payload', $payload)
            ->where('status', FormDualAuth::STATUS_PENDING);
        if ($formName != null) {
            $dualAuth->where("form_name", $formName);
        }
        return $dualAuth->count();
    }

    public function findOneById($dualAuthId)
    {
        return FormDualAuth::where('id', $dualAuthId)->first();
    }

    public function getPendingCount()
    {
        return $this->model->newQuery()->where([
            ['status', FormDualAuth::STATUS_PENDING]
        ])->count();
    }
}
