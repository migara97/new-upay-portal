<?php

namespace App\Enums;

enum DataTables: string
{
    case DUAL_AUTH = "DUAL_AUTH";
    case ADMIN_USER_MANAGEMENT = "UserManagement";
    case USER_ROLE_MANAGEMENT = "RoleManagement";

    case ACTIVITY_MANAGEMENT = "ActivityRoleManagement";


    public function reload(): string
    {
        return match ($this) {
            self::DUAL_AUTH => "reload-dual-auth-table",
            self::ADMIN_USER_MANAGEMENT => "reload-user-table",
            self::USER_ROLE_MANAGEMENT => "reload-role-table",
            self::ACTIVITY_MANAGEMENT => "reload-activity-logs-table",
        };
    }

}
