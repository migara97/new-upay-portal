<?php

namespace App\Listeners;

use App\Events\DualAuthApproved;
use App\Models\Backend\DualAuth\FormDualAuth;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class UpdateDualAuth
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function onApprove($event)
    {
        $dualAuth = FormDualAuth::find($event->dualAuth->id);
        $dualAuth->approved_by = Auth::user()->email;
        $dualAuth->approved_at = Carbon::now();
        $dualAuth->status = FormDualAuth::STATUS_APPROVE;
        $dualAuth->save();
    }

    public function onReject($event)
    {

    }

    public function subscribe($events)
    {
        $events->listen(
            DualAuthApproved::class,
            'App\Listeners\UpdateDualAuth@onApprove'
        );

//        $events->listen(
//            DualAuthApproved::class,
//            'App\Listeners\UpdateDualAuth@onReject'
//        );
    }

}
