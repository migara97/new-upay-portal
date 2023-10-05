<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class DualAuthApproved
{
    use  SerializesModels;

    public $dualAuth;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($dualAuth)
    {
        $this->dualAuth = $dualAuth;
    }


}
