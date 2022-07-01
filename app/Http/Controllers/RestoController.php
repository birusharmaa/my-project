<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class RestoController extends Controller
{
    function index(){
        if(Session::get('user')){
            return view('admin.layout');
        }
        return redirect('login');
    }

    function registerUser(Request $req)
    {
        $validateData = $req->validate([
            'name' => 'required|regex:/^[a-z A-Z]+$/u',
            'email' => 'required|email',
            'password' => 'required|min:6|max:12',
            'confirm_password' => 'required|same:password',
            'mobile' => 'numeric|required|digits:10'
        ]);
        $result = DB::table('users')
            ->where('email', $req->input('email'))
            ->get();

        $res = json_decode($result, true);

        if (sizeof($res) == 0) {
            $data = $req->input();
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $encrypted_password = crypt::encrypt($data['password']);
            $user->password = $encrypted_password;
            $user->phone = $data['mobile'];
            $user->save();
            $req->session()->flash('register_status', 'User has been registered successfully');
            return redirect('/login');
        } else {
            $req->session()->flash('register_status', 'This Email already exists.');
            return redirect('/login');
        }
    }


    function login(Request $req)
    {
        $validatedData = $req->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = DB::table('users')
            ->where('email', $req->input('email'))
            ->get();

        $res = json_decode($result, true);

        if (sizeof($res) == 0) {
            $req->session()->flash('error', 'Email Id does not exist. Please register yourself first');
            echo "Email Id Does not Exist.";
            return redirect('/login');
        } else {
            $encrypted_password = $result[0]->password;
            $decrypted_password = crypt::decrypt($encrypted_password);
            if ($decrypted_password == $req->input('password')) {
                echo "You are logged in Successfully";
                $req->session()->put('user', $result[0]->name);
                return redirect('/dashboard');
            } else {
                $req->session()->flash('error', 'Password Incorrect!!!');
                echo "Email Id Does not Exist.";
                return redirect('/login');
            }
        }
    }

    function logout(Request $req){
        Session::flush();;
        return redirect('login');
    }
}
