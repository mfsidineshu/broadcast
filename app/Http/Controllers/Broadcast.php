<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class Broadcast extends Controller
{


    public function broadcastPage()
    {
        if(Auth::check()){
            return view('start-broadcast');
        }

        return redirect("login")->withSuccess('Opps! You do not have access');
    }

}
