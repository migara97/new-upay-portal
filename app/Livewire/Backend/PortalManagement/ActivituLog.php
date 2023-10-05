<?php

namespace App\Livewire\Backend\PortalManagement;

use App\Enums\DataTables;
use Livewire\Component;

class ActivituLog extends Component
{
    public function render()
    {
        return view('livewire.backend.portal-management.activitu-log');
    }

    public function reload(){
        $this->dispatch(DataTables::ACTIVITY_MANAGEMENT->reload());
    }
}
