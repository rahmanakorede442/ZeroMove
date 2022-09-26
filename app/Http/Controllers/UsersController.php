<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class UsersController extends Controller
{
    // function loadView(){
    //     $data = ['mujeeb', 'adeniyi', 'abdul', 'rahman', 'lamina', 'opeyemi', 'quadri'];
    //     return view('users', ['name'=>$data]);
    // }

    // function products(){
    //     $data = 23;
    //     return Blade::render('<h2> {{$data}} Blade template string</h2>', ['data' => $data]);
    // }

    function process(Request $request){
        $request->validate([
            'first_name' => 'required|max:5',
            'last_name' => 'required|max:5',
            'email' => 'required|email',
            'password' => 'required|min:5'
       ]);
       return $request->input();

    }
}
