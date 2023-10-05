<?php

namespace App\Repository;

interface RoleRepositoryInterface
{
    public function storeRoleCreate(array $details, array $permissions);

    public function storeRoleUpdate($id, array $details, array $permissions);

    public function deleteRoleFromDb($id);
}
