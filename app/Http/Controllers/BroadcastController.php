<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Broadcast;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BroadcastController extends Controller
{
    public $requestData;
    public $order;
    public $binarydata;

    public $folderName;

    public $destinationDirectory;

    public $liveFilePath;

    public $hlsChunkFilesPath;

    public $hlsMasterPlaylist;

    public $pid;

    public $videosStorageDir = "broadcast-videos";

    public function broadcastPage()
    {
        return view('start-broadcast');

    }

    public function saveStreamToAFile(Request $request){

        $this->requestData = json_decode(file_get_contents("php://input"), true);
        $this->chunk = $this->requestData['chunk'];
        $this->order = $this->requestData['order'];
        $this->binarydata = pack("C*", ...$this->chunk);

        $this->folderName = $this->order == 0 ? $this->getBroadcastId() : $this->requestData['folder'];

        $this->destinationDirectory = "{$this->videosStorageDir}/{$this->folderName}";
        $this->liveFilePath = "{$this->destinationDirectory}/{$this->folderName}.webm";
        $this->hlsChunkFilesPath = "{$this->destinationDirectory}/hls";
        $this->hlsMasterPlaylist = "{$this->hlsChunkFilesPath}/{$this->folderName}.m3u8";


        if(!File::exists( storage_path().'/'. $this->destinationDirectory)){
            Storage::disk('local')->makeDirectory( $this->destinationDirectory );

        }

        $out = fopen( storage_path($this->liveFilePath) , $this->order == 0 ? "wb" : "ab");

        if (!$out) {
            $this->sendResponse();
        }

        fwrite($out, $this->binarydata);
        fclose($out);

        $this->sendResponse(true, "Chunk appended", [
            "folder"  => $this->folderName
        ]);
    }

    public function getBroadcastId(){
        $broadcast = new Broadcast;
        $broadcast->user_id = Auth::id();
        $broadcast->save();

        return $broadcast->broadcast_id;
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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
