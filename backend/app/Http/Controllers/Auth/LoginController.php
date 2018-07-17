<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Session;
use App\Http\Service\UserService;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

//    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
//    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        $this->middleware('guest')->except('logout');
//    }

    public function login(Request $request){
        $username = $request->input('username', '');
        $password = $request->input('password', '');
        $user = $this->validateUser($username, $password);

        if(empty($user)){
            return response()->json(['code' => 500, 'msg' => 'fail', 'user' => '']);
        }
        Session::put('user', collect($user));
        return response()->json(['code' => 200, 'msg' => 'success', 'user' => $user]);
    }

    public function logout(Request $request){
        $request->session()->flush();
    }

    public function validateUser($username, $password){
        $userService = new UserService();
        $users = $userService->users;
        if(! empty($users[$username]) && $users[$username]['password'] == $password){
            return $users[$username];
        }
        return false;
    }
}
