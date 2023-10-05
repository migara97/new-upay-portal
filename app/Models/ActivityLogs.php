<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'affected_module',
        'action',
        'affected_app_user',
        'previous_value',
        'new_value',
        'link_id'
    ];

}
