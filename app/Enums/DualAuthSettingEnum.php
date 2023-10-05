<?php

namespace App\Enums;

enum DualAuthSettingEnum: int
{
    case UPDATE_DUAL_AUTH = 1;
    case CREATE_ADMIN_USER = 2;
    case UPDATE_ADMIN_USER = 3;
    case CREATE_USER_ROLE = 4;
    case UPDATE_USER_ROLE = 5;
    case REMOVE_USER_ROLE = 6;

    public function labels(): string
    {
        return match ($this) {
            self::UPDATE_DUAL_AUTH => "Update Dual Auth Settings",
            self::CREATE_ADMIN_USER => "Create Admin User",
            self::UPDATE_ADMIN_USER => "Update Admin User",
            self::CREATE_USER_ROLE => "Create User Role",
            self::UPDATE_USER_ROLE => "Update User Role",
            self::REMOVE_USER_ROLE => "Remove User Role",
        };
    }

    public function labelPowergridFilter(): string
    {
        return $this->labels();
    }
}
