<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class Broadcast extends Controller
{


    public function broadcastPage()
    {
        return view('start-broadcast');

    }

    public function saveStreamToAFile(Request $request){
        exit("a");
    }

}
