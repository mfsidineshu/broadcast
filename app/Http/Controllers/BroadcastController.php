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

    public function watchBroadcastPage(){


        return view('watch-broadcast');

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
            $this->decryptAndGetFolder();
        }

        $this->destinationDirectory = "{$this->videosStorageDir}/{$this->folderName}";
        $this->liveFilePath = "{$this->destinationDirectory}/{$this->folderName}.webm";
        $this->hlsChunkFilesPath = "{$this->destinationDirectory}/hls";
        $this->hlsMasterPlaylist = "{$this->hlsChunkFilesPath}/{$this->folderName}.m3u8";



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

        $this->sendResponse(true, "Chunk appended", [
            "folder"  => Crypt::encrypt($this->folderName)
        ]);
    }


    public function decryptAndCheckFolder(){


            try {
                $this->folderName = Crypt::decrypt($this->requestData["folder"]);

                if(!File::exists( storage_path().'/'. $this->liveFilePath)){
                    $this->sendResponse(false,"Invalid request");
                }

                $broadcast = Broadcast::where([
                    [ "broadcast_id", $this->folderName ],
                    ["user_id" , Auth::id() ]
                ])->whereNotNull('ended_on')->first();

                if(!$broadcast || is_null($broadcast)){
                    $this->sendResponse(false,"Invalid request");

                }


            } catch (Exception $e) {
                $this->sendResponse(false,"Invalid request");
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

        $this->sendResponse(true, "Created broadcast", [
            // "processId" => $this->pid,
            // "command"  => $cmd,
            "folder"  => Crypt::encrypt($this->folderName)
        ]);



        $this->sendResponse();
    }

    private function runCom($cmd)
    {
        $command = 'nohup ' . $cmd . ' </dev/null >/dev/null 2>/var/www/html/ffmpeg.log & echo $!';
        exec($command, $op);
        $this->pid = (int)$op[0];
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
