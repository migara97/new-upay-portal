<?php

namespace App\Repository\Eloquent;



use App\Events\DualAuthApproved;
use App\Models\Backend\DualAuth\DualAuthSettings;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Repository\DualAuthSettingsRepositoryInterface;
use Illuminate\Support\Facades\Log;
class DualAuthSettingsRepository extends BaseRepository implements DualAuthSettingsRepositoryInterface
{
    public function __construct(DualAuthSettings $model)
    {
        parent::__construct($model);
    }

    public function findSettings($id)
    {
        return DualAuthSettings::find($id);
    }

    public function findWhereIds(array $ids)
    {
        return DualAuthSettings::query()->whereIn('id', $ids)->get();
    }

    public function needDualAuth(int|array $id): bool
    {
        if (is_array($id)) {
            $dAuth = $this->findWhereIds($id);
            foreach ($dAuth as $i) {
                if ($i->status) {
                    return true;
                }
            }
            return false;
        }
        return ($set = $this->findSettings($id)) && $set->status;
    }

    public function store(FormDualAuth $dualAuth)
    {
        switch ($dualAuth->method) {
            case FormDualAuth::METHOD_UPDATE:
                $this->update($dualAuth);
                return $dualAuth;
            default:
                Log::error('Store pending update ' . $dualAuth->id . '. Failed: Unimplemented method handle.');

        }
    }

    private function update(FormDualAuth $dualAuth)
    {
        $payload = json_decode($dualAuth->new_payload, true);
        $updateStatus = $this->updateDualAuthSettings($payload['id'], $payload['data']);

        if ($updateStatus) {
            event(new DualAuthApproved($dualAuth));
        }
        return $dualAuth;
    }

    public function updateDualAuthSettings($id, array $data): bool
    {
        $actionName = "Update dual auth settings";
        try {
            $updated = DualAuthSettings::find($id);
            $featureName = $updated->feature_name;
            $status = $updated->status;
            $updated = $updated->update($data);
            return $updated;
        } catch (\Exception $exception) {
            Log::error("Dual auth settings update failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
        }
        return false;
    }

}
