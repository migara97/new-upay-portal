<?php

namespace App\Models\Backend\DualAuth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DualAuthSettings extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'status'
    ];
}
