<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Users extends Controller
{

    public function index($id){
        return response()->json([
            'message'=> 'successful',
            'status' => true,
            'id' => $id
        ]);

        // the same as this
    //     return [
    //         'message'=> 'successful',
    //         'status' => true,
    //         'id' => $id
    //     ];

    
    }
}
