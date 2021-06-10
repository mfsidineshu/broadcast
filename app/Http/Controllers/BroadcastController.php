<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Broadcast;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

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

    public function watchBroadcastPage(Request $request , $folder){

        try {


            $req = [
                "enc-folder" => $folder ,
                "dec"  => Crypt::decrypt($folder)

            ];

            $this->folderName = $req["dec"];

            $this->setUpDirStructure();

            // exit(Crypt::decrypt($folder));
            // exit( storage_path().'/app/'. $this->liveFilePath );


            if(!File::exists( storage_path().'/app/'. $this->liveFilePath)){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[1]");
            }

            $query = Broadcast::query();

            $query->with(['user']);

            $query->where([
                [ "broadcast_id", $this->folderName ]
            ])->first();

            // $query->whereNull('ended_on');

            $broadcast = $query->first();

            if(!$broadcast || is_null($broadcast)){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[2]");
            }

            return view('watch-broadcast',compact("folder","broadcast"));


        } catch (Exception $e) {
            // dd($e);
            return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[3]");
        }


    }

    public function setUpDirStructure(){
        $this->destinationDirectory = "{$this->videosStorageDir}/{$this->folderName}";
        $this->liveFilePath = "{$this->destinationDirectory}/{$this->folderName}.webm";
        $this->hlsChunkFilesPath = "{$this->destinationDirectory}/hls";
        $this->hlsMasterPlaylist = "{$this->hlsChunkFilesPath}/{$this->folderName}.m3u8";
    }

    public function saveStreamToAFile(Request $request){

        $this->requestData = json_decode(file_get_contents("php://input"), true);
        $this->chunk = $this->requestData['chunk'];
        $this->order = $this->requestData['order'];
        $this->binarydata = pack("C*", ...$this->chunk);

        if($this->order == 0){
            $this->folderName =  $this->getBroadcastId();
        }
        else{
            $this->decryptAndCheckFolder();
        }



        $this->setUpDirStructure();

        if ($this->order == "0") {

            if(!File::exists( storage_path().'/'. $this->destinationDirectory)){
                Storage::disk('local')->makeDirectory( $this->destinationDirectory );
                Storage::disk('local')->makeDirectory( $this->hlsChunkFilesPath );

            }


            // Storage::put($this->liveFilePath,'');
        }

        $this->addOrAppendLiveVideo();

        if ($this->order == "0") {
            $this->createMasterHlsPlaylist();
            $this->runFfmpegCommand();
        }

        $enc = Crypt::encrypt($this->folderName);
        $dec = Crypt::decrypt($enc);

        $this->sendResponse(true, "Chunk appended", [
            "folder"  =>  $enc,
            "dec"  =>  $dec
        ]);
    }


    public function decryptAndCheckFolder(){


            try {
                $this->folderName = Crypt::decrypt($this->requestData["folder"]);

                if(!File::exists( storage_path().'/'. $this->liveFilePath)){
                    $this->sendResponse(false,"Invalid request [12]");
                }


                $query = Broadcast::query();

                $query->where([
                    [ "broadcast_id", $this->folderName ],
                    ["user_id" , Auth::id() ]
                ])->first();

                $query->whereNull('ended_on');

                $broadcast = $query->first();

                if(!$broadcast || is_null($broadcast)){
                    $this->sendResponse(false,"Invalid request [13]");

                }


            } catch (Exception $e) {
                // var_dump($e);
                $this->sendResponse(false,"Invalid request [16]");
            }


    }


    public function addOrAppendLiveVideo(){
        $out = fopen( storage_path('app/'.$this->liveFilePath) , $this->order == 0 ? "wb" : "ab");
        if (!$out) {
            $this->sendResponse();
        }
        fwrite($out, $this->binarydata);
        fclose($out);
    }


    public function createMasterHlsPlaylist()
    {


                $masterPLaylistContent = "#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=800000,RESOLUTION=640x360
360p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=1400000,RESOLUTION=842x480
480p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=2800000,RESOLUTION=1280x720
720p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=5000000,RESOLUTION=1920x1080
1080p.m3u8";


        Storage::put( $this->hlsMasterPlaylist,$masterPLaylistContent);

    }

    public function getBroadcastId(){
        $broadcast = new Broadcast;
        $broadcast->user_id = Auth::id();
        $broadcast->save();

        return $broadcast->broadcast_id;
    }



    public function runFfmpegCommand()
    {

        $destinationFullPath = storage_path('app/'.$this->liveFilePath);
        $hlsDirectoryFullPath = storage_path('app/'.$this->hlsChunkFilesPath);

        $cmd = "ffmpeg -stream_loop -10 -hide_banner -nostdin -y -i {$destinationFullPath}  -vf scale=w=640:h=360:force_original_aspect_ratio=decrease -c:a aac -ar 48000 -c:v h264 -profile:v main -crf 20 -sc_threshold 0 -g 48 -keyint_min 48 -hls_time 4  -hls_playlist_type event -b:v 800k -maxrate 856k -bufsize 1200k -b:a 96k -hls_segment_filename {$hlsDirectoryFullPath}/360p_%03d.ts {$hlsDirectoryFullPath}/360p.m3u8  -vf scale=w=842:h=480:force_original_aspect_ratio=decrease -c:a aac -ar 48000 -c:v h264 -profile:v main -crf 20 -sc_threshold 0 -g 48 -keyint_min 48 -hls_time 4 -hls_playlist_type event -b:v 1400k -maxrate 1498k -bufsize 2100k -b:a 128k -hls_segment_filename {$hlsDirectoryFullPath}/480p_%03d.ts {$hlsDirectoryFullPath}/480p.m3u8  -vf scale=w=1280:h=720:force_original_aspect_ratio=decrease -c:a aac -ar 48000 -c:v h264 -profile:v main -crf 20 -sc_threshold 0 -g 48 -keyint_min 48 -hls_time 4 -hls_playlist_type event -b:v 2800k -maxrate 2996k -bufsize 4200k -b:a 128k -hls_segment_filename {$hlsDirectoryFullPath}/720p_%03d.ts {$hlsDirectoryFullPath}/720p.m3u8 -vf scale=w=1920:h=1080:force_original_aspect_ratio=decrease -c:a aac -ar 48000 -c:v h264 -profile:v main -crf 20 -sc_threshold 0 -g 48 -keyint_min 48 -hls_time 4 -hls_playlist_type event -b:v 5000k -maxrate 5350k -bufsize 7500k -b:a 192k -hls_segment_filename {$hlsDirectoryFullPath}/1080p_%03d.ts {$hlsDirectoryFullPath}/1080p.m3u8 ";


        $this->runCom($cmd);

        $enc = Crypt::encrypt($this->folderName);
        $dec = Crypt::decrypt($enc);

        $this->sendResponse(true, "Created broadcast", [
            // "processId" => $this->pid,
            // "command"  => $cmd,
            "folder"  =>  $enc,
            "dec"  =>  $dec
        ]);



        $this->sendResponse();
    }

    private function runCom($cmd)
    {
        $command = 'nohup ' . $cmd . ' </dev/null >/dev/null 2>/var/www/html/ffmpeg.log & echo $!';
        exec($command, $op);
        $this->pid = (int)$op[0];
    }


    public function fetchPlayLists(Request $request , $folder , $bitRate){

        try {


            // exit("a");
            $req = [
                "enc-folder" => $folder ,
                "dec"  => Crypt::decrypt($folder)

            ];

            $this->folderName = $req["dec"];

            $this->setUpDirStructure();

            $bitRate = $bitRate == 'master' ? $this->folderName : $bitRate ;

            if(!File::exists( storage_path().'/app/'. "{$this->hlsChunkFilesPath}/{$bitRate}.m3u8")){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[1]");
            }

            $query = Broadcast::query();
            $query->where([
                [ "broadcast_id", $this->folderName ]
            ])->first();

            // $query->whereNull('ended_on');

            $broadcast = $query->first();

            if(!$broadcast || is_null($broadcast)){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[2]");
            }

            return Storage::download("{$this->hlsChunkFilesPath}/{$bitRate}.m3u8");


        } catch (Exception $e) {
            dd($e);

            // return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[3]");
        }
    }

    public function fetchSegments(Request $request , $folder , $fragment){

        try {

            $req = [
                "enc-folder" => $folder ,
                "dec"  => Crypt::decrypt($folder)

            ];

            $this->folderName = $req["dec"];


            $this->setUpDirStructure();

            if(!File::exists( storage_path().'/app/'. "{$this->hlsChunkFilesPath}/{$fragment}.ts")){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[1]");
            }

            $query = Broadcast::query();
            $query->where([
                [ "broadcast_id", $this->folderName ]
            ])->first();

            // $query->whereNull('ended_on');

            $broadcast = $query->first();

            if(!$broadcast || is_null($broadcast)){
                return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[2]");
            }

            return Storage::download("{$this->hlsChunkFilesPath}/{$fragment}.ts");


        } catch (Exception $e) {
            // dd($e);
            return redirect("dashboard")->with('danger', "Requested Broadcast doesn't exist[3]");
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
