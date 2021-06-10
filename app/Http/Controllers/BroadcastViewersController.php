<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Broadcast;
use App\BroadcastViewer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class BroadcastViewersController extends Controller
{
    public function addOrUpdateBroadViewingStatus(Request $request){

        try{

            $req = [
                "enc-folder" => $request->input("folder") ,
                "dec"  => Crypt::decrypt(  $request->input("folder") )
            ];

            $folder = $req["dec"];

            $query = Broadcast::query();

            $query->where([
                [ "broadcast_id", $folder ],
                ["user_id" , "<>", Auth::id() ]
            ])->first();

            $query->whereNull('ended_on');

            $broadcast = $query->first();

            if(!$broadcast || is_null($broadcast)){
                $this->sendResponse(false,"Not Applicable [13]");
            }

            $existingBroadcastViewer = BroadcastViewer::where([
                [ "broadcast_id", $folder ],
                ["user_id" , Auth::id() ]
            ])->first();

            if($existingBroadcastViewer){
                $existingBroadcastViewer->last_viewed_on = date("Y-m-d H:i:s");
                $existingBroadcastViewer->save();
                return $this->sendResponse( true ,"Viewing Status updated");
            }

            $newBroadcastViewer = new BroadcastViewer;
            $newBroadcastViewer->broadcast_id = $folder;
            $newBroadcastViewer->user_id = Auth::id();
            $newBroadcastViewer->joined_on = date('Y-m-d H:i:s');
            $newBroadcastViewer->last_viewed_on = date('Y-m-d H:i:s');
            $newBroadcastViewer->save();
            return $this->sendResponse( true ,"Viewing Status added");

        }
        catch(Exception $e){
            dd($e);
        }


    }

    public function sendResponse($status = false, $message = "There was an error", $data = [])
    {

        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ], true);

        exit;
    }
}
