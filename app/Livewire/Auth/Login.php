<?php

namespace App\Livewire\Auth;

use App\Enums\ActionList;
use App\Enums\PortalModules;
use App\Models\User;
use App\Repository\AdminUserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use WireUi\Traits\Actions;

class Login extends Component
{
    use Actions;

    public $password;
    public $email;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required'
    ];

    protected $messages = [
        'password.required' => 'Password field is required!',
        'email.required' => 'Email field is required!',
        'email.email' => 'Email field is invalid!',

    ];


    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }

    public function submit(AdminUserRepositoryInterface $adminUserRepositoryInterface)
    {
        $actionName = "User Login";
        $this->validate();

        if (Auth::attempt(array('email' => $this->email, 'password' => $this->password))) {
            if (\auth()->user()->status == User::USER_LOCKED) {
                \auth()->logout();
                activity_log(ActionList::TRY_TO_LOCKED_ADMIN_LOGIN->value, "Email : $this->email", PortalModules::AccessManagement);
                $this->notification()->error(
                    $title = 'Error !!!',
                    $description = 'Your account has been locked due to exceeding maximum invalid Sign In attempts.Please contact System Admin!'
                );
            } elseif (\auth()->user()->status == User::USER_INACTIVE) {
                \auth()->logout();
                activity_log(ActionList::TRY_TO_DISABLED_ADMIN_LOGIN->value, "Email : $this->email", PortalModules::AccessManagement);
                $this->notification()->error(
                    $title = 'Error !!!',
                    $description = 'Your Account has been Disabled. Please contact System Admin!'
                );
            } else {
                activity_log(ActionList::ADMIN_LOGIN_SUCCESS->value, "Email : $this->email", PortalModules::AccessManagement);
                $adminUserRepositoryInterface->setAttemptsCount($this->email, 0);
                return redirect()->intended(route('admin.dashboard'));
            }

        } else {
            $message = $adminUserRepositoryInterface->getAttemptsCount($this->email);
            $this->notification()->error(
                $title = 'Error !!!',
                $description = $message
            );
        }

    }

}
