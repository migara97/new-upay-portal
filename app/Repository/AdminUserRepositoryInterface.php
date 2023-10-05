<?php

namespace App\Repository;

interface AdminUserRepositoryInterface
{
    public function getAdminCount();

    public function getAttemptsCount($email);

    public function setAttemptsCount($email,$count);

    public function isOldPassword($email,$password);

    public function changePassword($email,$password);

    public function updateAdminUser($data);

    public function createUser($data);

    public function updateUser($data,$id);
}
