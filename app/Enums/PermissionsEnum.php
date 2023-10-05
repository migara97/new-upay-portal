<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case VIEW_BACKEND = "view-backend";
    case VIEW_USER_MANAGEMENT = "view-user-management";
    case CREATE_ADMIN_USER = "create-admin-user";
    case APPROVE_CREATE_ADMIN_USER = "approve-create-admin-user";
    case UPDATE_ADMIN_USER = "update-admin-user";
    case APPROVE_UPDATE_ADMIN_USER = "approve-update-admin-user";
    case VIEW_ROLE_MANAGEMENT = "view-role-management";
    case CREATE_USER_ROLE = "create-user-role";
    case APPROVE_CREATE_USER_ROLE = "approve-create-user-role";
    case UPDATE_USER_ROLE = "update-user-role";
    case APPROVE_UPDATE_USER_ROLE = "approve-update-user-role";
    case REMOVE_USER_ROLE = "remove-user-role";
    case APPROVE_REMOVE_USER_ROLE = "approve-remove-user-role";
    case VIEW_ACTIVITY_LOGS = "view-activity-logs";

    public function labels(): string
    {
        return match ($this) {
            self::VIEW_BACKEND => "View Backend",
            self::VIEW_USER_MANAGEMENT => "View User Management",
            self::CREATE_ADMIN_USER => "Create Admin User",
            self::APPROVE_CREATE_ADMIN_USER => "Approve Create Admin User",
            self::UPDATE_ADMIN_USER => "Update Admin User",
            self::APPROVE_UPDATE_ADMIN_USER => "Approve Update Admin User",
            self::VIEW_ROLE_MANAGEMENT => "View Role Management",
            self::CREATE_USER_ROLE => "Create User Role",
            self::APPROVE_CREATE_USER_ROLE => "Approve Create User Role",
            self::UPDATE_USER_ROLE => "Update User Role",
            self::APPROVE_UPDATE_USER_ROLE => "Approve Update User Role",
            self::REMOVE_USER_ROLE => "Remove User Role",
            self::APPROVE_REMOVE_USER_ROLE => "Approve Remove User Role",
            self::VIEW_ACTIVITY_LOGS => "View Activity Logs"
        };
    }

    public function category(): int
    {
        return match ($this) {
            self::VIEW_BACKEND => PermissionCategory::Other->value,
            self::VIEW_USER_MANAGEMENT,
            self::CREATE_ADMIN_USER,
            self::APPROVE_CREATE_ADMIN_USER,
            self::UPDATE_ADMIN_USER,
            self::APPROVE_UPDATE_ADMIN_USER => PermissionCategory::UserManagement->value,
            self::VIEW_ROLE_MANAGEMENT,
            self::CREATE_USER_ROLE,
            self::APPROVE_CREATE_USER_ROLE,
            self::UPDATE_USER_ROLE,
            self::APPROVE_UPDATE_USER_ROLE,
            self::REMOVE_USER_ROLE,
            self::APPROVE_REMOVE_USER_ROLE => PermissionCategory::RoleManagement->value,
            self::VIEW_ACTIVITY_LOGS => PermissionCategory::PortalManagement->value
        };
    }

    public function labelPowergridFilter(): string
    {
        return $this->labels();
    }
}
