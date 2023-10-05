<?php

namespace App\Repository;

use App\Models\Backend\DualAuth\FormDualAuth;
use Illuminate\Support\Collection;

interface DualAuthRepositoryInterface
{
    public function approve(FormDualAuth $dualAuth);

    public function rejectDualAuth(FormDualAuth $dualAuth);

    public function find($dualAuthId);

    public function hasPendingUpdatesForPayload($payload, $formName = null);

    public function getPendingCount();

}
