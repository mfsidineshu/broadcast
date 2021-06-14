<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Broadcast;
use App\BroadcastViewer;
use DateTime;
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
            // dd($e);
            $this->sendResponse();
        }


    }

    public function broadcastViewers(Request $request){
        try{

            $req = [
                "enc-folder" => $request->input("folder") ,
                "dec"  => Crypt::decrypt(  $request->input("folder") )
            ];

            $folder = $req["dec"];

            $query = Broadcast::query();

            $query->where([
                [ "broadcast_id", $folder ],
            ])->first();

            $query->whereNull('ended_on');

            $broadcast = $query->first();

            if(!$broadcast || is_null($broadcast)){
                $this->sendResponse(false,"Not Applicable [13]");
            }

            $date = new DateTime();
            $date->modify('-2 minutes');
            $formatted_date = $date->format('Y-m-d H:i:s');

            $query = BroadcastViewer::query();
            $query->with(['user']);

            $query->where([
                [ "broadcast_id", $folder ],
                ["last_viewed_on",">=", $formatted_date  ]
            ]);

            $broadcastViewers = $query->get();

            $numberOfUsers =  $broadcastViewers->count();

            $viewers = [];

            if($broadcastViewers){
                foreach($broadcastViewers as $broadcastViewer){
                    $viewers[] = [
                       "name" => $broadcastViewer["user"]["name"] ,
                        "email" => $broadcastViewer["user"]["email"]
                    ];
                }
            }

            // dd($broadcastViewers);
            return $this->sendResponse( true ,"Broadcast viewers fetched", [
                "numberOfUsers" => $numberOfUsers ,
                "broadcastViewers" => $viewers
            ]);

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
