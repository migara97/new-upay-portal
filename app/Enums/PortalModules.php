<?php

namespace App\Enums;

enum PortalModules: int
{
    case DualAuthAction = 0;
    case AccessManagement = 1;
    case UserManagement = 2;
    case RoleManagement = 3;
    case BillerManagement = 4;



    public function labels(): string
    {
        return match ($this) {
            self::DualAuthAction => "Dual Auth Action",
            self::AccessManagement => "Portal Access",
            self::UserManagement => "User Management",
            self::RoleManagement => "Role Management",
            self::BillerManagement => "Biller Management",
        };
    }

    public function labelPowergridFilter(): string
    {
        return $this->labels();
    }
}
