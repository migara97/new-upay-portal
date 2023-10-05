<?php

namespace App\Models\Backend\DualAuth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDualAuth extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_name',
        'method',
        'model_type',
        'new_payload',
        'old_payload',
        'permission',
        'created_by',
        'approved_by',
        'repository_type',
        'summary',
        'status',
        'summary_data'
    ];

    const METHOD_CREATE = 'CREATE';
    const METHOD_UPDATE = 'UPDATE';
    const METHOD_DELETE = 'DELETE';

    const STATUS_PENDING = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT = 2;


    public static function statusList()
    {
        return collect(
            [
                ['status' => 0,  'label' => 'Pending'],
                ['status' => 1,  'label' => 'Approved'],
                ['status' => 2,  'label' => 'Rejected'],
            ]
        );
    }
}
