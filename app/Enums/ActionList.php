<?php

namespace App\Enums;

enum ActionList: string
{
    case CREATE_USER = "Create New User";
    case UPDATE_USER = "Update User";

    case CREATE_USER_ROLE = "Create New User Role";
    case UPDATE_USER_ROLE = "Update User Role";
    case REMOVE_USER_ROLE = "Remove User Role";

    case APPROVE_DUAL_AUTH_ACTION = "Approve Dual Auth Action";
    case REJECT_DUAL_AUTH_ACTION = "Reject Dual Auth Action";

    case ADMIN_LOGIN_SUCCESS = "Admin Login Success";
    case ADMIN_LOGIN_FAILED_ATTEMPT = "Admin Login Failed Attempt";
    case TRY_TO_DISABLED_ADMIN_LOGIN = "Try to Disabled Admin Login";
    case TRY_TO_LOCKED_ADMIN_LOGIN = "Try to locked Admin Login";
    case TRY_TO_LOGIN_USING_INVALID_EMAIL = "Try to Login using invalid email";

    case ACCOUNT_HAS_BEEN_LOCKED = "Account has been locked";

    case STORE_BILLER_PROVIDER = "Store biller provider";

//    public function labels(): string
//    {
//        return match ($this) {
//            self::CREATE_USER => "Create New User",
//            self::UPDATE_USER => "Update User",
//            self::CREATE_USER_ROLE => "Create New User Role",
//            self::UPDATE_USER_ROLE => "Update User Role",
//            self::APPROVE_DUAL_AUTH_ACTION => "Approve Dual Auth Action",
//            self::REJECT_DUAL_AUTH_ACTION => "Reject Dual Auth Action"
//        };
//    }
//
//    public function labelPowergridFilter(): string
//    {
//        return $this->labels();
//    }
}
