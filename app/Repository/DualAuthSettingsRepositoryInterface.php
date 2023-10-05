<?php

namespace App\Repository;

interface DualAuthSettingsRepositoryInterface
{
    public function findSettings($id);

    public function needDualAuth(int $id);

}
