<?php

use App\Enums\PermissionCategory;
use App\Models\ActivityLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

if (!function_exists('password_change_notification_days')) {
    function password_change_notification_days()
    {
        $password_changed_at = new Carbon(auth()->user()->password_changed_at ?: auth()->user()->created_at);
        $passwordExpiresDays = 45;

        if ($passwordExpiresDays - Carbon::now()->diffInDays($password_changed_at) <= 3) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('password_change_remaining_days')) {
    function password_change_remaining_days()
    {
        $password_changed_at = new Carbon(auth()->user()->password_changed_at ?: auth()->user()->created_at);
        $passwordExpiresDays = 45;

        return $passwordExpiresDays - Carbon::now()->diffInDays($password_changed_at);
    }
}


if (!function_exists('include_route_files')) {

    /**
     * Loops through a folder and requires all PHP files
     * Searches sub-directories as well.
     *
     * @param $folder
     */
    function include_route_files($folder)
    {
        try {
            $rdi = new recursiveDirectoryIterator($folder);
            $it = new recursiveIteratorIterator($rdi);

            while ($it->valid()) {
                if (!$it->isDot() && $it->isFile() && $it->isReadable() && $it->current()->getExtension() === 'php') {
                    require $it->key();
                }

                $it->next();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

if (!function_exists('get_enum_value')) {
    function get_enum_value($value, $enumClass, $matchValue = false)
    {
        $cases = $enumClass::cases();
        $index = array_search($value, array_column($cases, $matchValue ? "value" : "name"));
        if ($index !== false) {
            return $cases[$index]->labels();
        }

        return null;
    }
}

if (!function_exists('get_formatted_permission_list')) {
    function get_formatted_permission_list($permissionList): array
    {
        $formattedPermissionList = [];

        foreach ($permissionList as $p) {
            $permissionValue['id'] = $p->id;
            $permissionValue['name'] = $p->name;
            $key = get_enum_value($p->category_id, PermissionCategory::class, true);
            $val = $permissionValue;
            $exists = array_key_exists($key, $formattedPermissionList);

            if ($exists) {
                array_push($formattedPermissionList[$key], $val);
            } else {
                $formattedPermissionList[$key] = array($val);
            }
        }

        asort($formattedPermissionList);
        return $formattedPermissionList;
    }
}

if (!function_exists('filter_arrays')) {
    function filter_arrays(array $first, array $second): array
    {
        $out = filter_dual_auth_summary([
            "pre" => $first,
            "new" => $second
        ]);

        return [$out["pre"], $out["new"]];
    }
}


if (!function_exists('filter_dual_auth_summary')) {
    function filter_dual_auth_summary($summaryData, $ignoreArray = [])
    {
        $data = [
            'pre' => [],
            'new' => []
        ];

        if (isset($summaryData['common']) && ($summaryData['common'] != null)) {
            $data['common'] = $summaryData['common'];
        }

        //filter key value summary pair
        if (isset($summaryData['pre']) && count($summaryData['pre']) > 0 && isset($summaryData['new']) && count($summaryData['new']) > 0) {
            foreach ($summaryData['new'] as $key => $value) {
                if ($summaryData['pre'][$key] != $value) {
                    $data['pre'][$key] = $summaryData['pre'][$key];
                    $data['new'][$key] = $summaryData['new'][$key];
                } else {
                    //show ignore filter keys
                    if (in_array($key, $ignoreArray)) {
                        $data['pre'][$key] = $summaryData['pre'][$key];
                        $data['new'][$key] = $summaryData['new'][$key];
                    }
                }
            }
        } else {
            if (isset($summaryData['pre'])) {
                $data['pre'] = $summaryData['pre'];
            }
            if (isset($summaryData['new'])) {
                $data['new'] = $summaryData['new'];
            }
        }

        //filter images summary
        if (isset($summaryData['image']['pre']) && count($summaryData['image']['pre']) > 0 && isset($summaryData['image']['new']) && count($summaryData['image']['new']) > 0) {
            foreach ($summaryData['image']['new'] as $key => $value) {
                if ($summaryData['image']['pre'][$key] != $value) {
                    $data['image']['pre'][$key] = $summaryData['image']['pre'][$key];
                    $data['image']['new'][$key] = $summaryData['image']['new'][$key];
                } else {
                    //show ignore filter keys
                    if (in_array($key, $ignoreArray)) {
                        $data['pre'][$key] = $summaryData['pre'][$key];
                        $data['new'][$key] = $summaryData['new'][$key];
                    }
                }
            }
        } else {
            if (isset($summaryData['image']['pre'])) {
                $data['image']['pre'] = $summaryData['image']['pre'];
            }
            if (isset($summaryData['image']['new'])) {
                $data['image']['new'] = $summaryData['image']['new'];
            }
        }

        //filter fill url images summary
        if (isset($summaryData['fullUrlImages']['pre']) && count($summaryData['fullUrlImages']['pre']) > 0 && isset($summaryData['fullUrlImages']['new']) && count($summaryData['fullUrlImages']['new']) > 0) {
            foreach ($summaryData['fullUrlImages']['new'] as $key => $value) {
                if ($summaryData['fullUrlImages']['pre'][$key] != $value) {
                    $data['fullUrlImages']['pre'][$key] = $summaryData['fullUrlImages']['pre'][$key];
                    $data['fullUrlImages']['new'][$key] = $summaryData['fullUrlImages']['new'][$key];
                } else {
                    //show ignore filter keys
                    if (in_array($key, $ignoreArray)) {
                        $data['pre'][$key] = $summaryData['pre'][$key];
                        $data['new'][$key] = $summaryData['new'][$key];
                    }
                }
            }
        } else {
            if (isset($summaryData['fullUrlImages']['pre'])) {
                $data['fullUrlImages']['pre'] = $summaryData['fullUrlImages']['pre'];
            }
            if (isset($summaryData['fullUrlImages']['new'])) {
                $data['fullUrlImages']['new'] = $summaryData['fullUrlImages']['new'];
            }
        }

        return $data;
    }
}

if (!function_exists('activity_log')) {
    function activity_log($action, $newValue, $module,$preValue = null, $linkID = null,$appUser = null)
    {
        $user = auth()->user() != null ? auth()->user()->email : "";

        $activityLog = [
            "user_name" => $user,
            "affected_app_user" => $appUser,
            "action" => $action,
            "affected_module" => $module,
            "previous_value" => json_encode($preValue),
            "new_value" => json_encode($newValue),
            "link_id" => $linkID
        ];
        try {
            ActivityLogs::create($activityLog);
            Log::info("----- Start Action >>>>> " . $action . " -----");
            Log::info("Action Done By: " . $user);
            Log::info("Previous Values: " . json_encode($preValue,128));
            Log::info("New Values: " .json_encode($newValue,128));
            Log::info("----- End Action >>>>> " . $action . " -----\n");
        } catch (\Exception $exception) {
            Log::error("Activity Log failed: Exception -> " . $exception->getMessage() . " - " . $exception->getLine());
        }


    }
}

if (!function_exists('upload_image')) {

    function upload_image($image, Closure $failedCallback, $path = null, $overwrite = false): ?string
    {
        $log = "[UPLOAD_IMAGE] | ";
        $originalName = $image->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = preg_replace("/[^a-zA-Z0-9]+/", "_", $filename) . ".$extension";
        
        Log::info($log . "Name: " . $name);
        
        
        // Skip file existence check if overwrite is enabled
        if (!$overwrite) {
            $finalPath = $path ? "$path/$name" : $name;
            $fileExists = Storage::disk(env('STORAGE_DISK'))->exists($finalPath);
            
            if ($fileExists) {
                return $failedCallback("An image already exists with this name.");
            }
        }
        
        $image->storeAs($path, $name, env('STORAGE_DISK'));

        return $name;
    }
}

