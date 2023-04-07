<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role',0)->get();
      return view('admin.users.index',compact('users'));
    }
    //Show Register/Create Form
    public function create()
    {
        return view('users.register');
    }
    //show login form
    public function login()
    {
        return view('users.login');
    }

    //user registration
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'email'=>['required','email',Rule::unique('users','email')],
            'password'=>'required|confirmed|min:6',
            'password_confirmation'=>'required'
        ]);
        //To make Hash Password
        $formFields['password'] = Hash::make($request->password);
        //create user
        $user = User::create($formFields);
        auth()->login($user);
        return redirect('/')->with('message','User created successfully');
    }
    //Logout user
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('message','You have been logged out!');
    }
    //Authenticate user
    public function authenticate(Request $request)
    {
        $formFields = $request->validate([
            'email'=>['required','email'],
            'password'=>'required'
        ]);
        if(auth()->attempt($formFields))
        {
            $request->session()->regenerate();
            //check user role (if admin)
            if(auth()->user()->role=='1')
            {
                return redirect('/admin/index');
            }
            //if user then redirect to home
            else{
                return redirect('/')->with('message','You are logged in');
            }
        }
        return back()->withErrors(['email'=>'Invalid credentials'])->onlyInput('email');

    }
}
