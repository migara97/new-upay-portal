<?php

namespace App\Enums;

enum PermissionCategory: int
{
    case Other = 0;
    case UserManagement = 1;
    case RoleManagement = 2;
    case PortalManagement = 3;

    public function labels(): string
    {
        return match ($this) {
            self::Other => "General",
            self::UserManagement => "User Management",
            self::RoleManagement => "Role Management",
            self::PortalManagement => "Portal Management"
        };
    }

    public function labelPowergridFilter(): string
    {
        return $this->labels();
    }
}
