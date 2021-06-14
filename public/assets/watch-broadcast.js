
class WatchBroadcast{
    constructor(){

        this.player = videojs('broadcastedVideo');

        // this.player.on('ready', ()=>{
        //     if(this.player.muted()){
        //         this.player.muted(false);
        //     }
        // });

        this.player.hlsQualitySelector({
            displayCurrentQuality: true,
        });

        this.folder = document.querySelector('input[name="folder"]');

        this.timeSLice = 3000;

        this.interval = null;

        this.lastCurrentTime = 0;


    }

    init(){

        this.interval = setInterval(()=>{

            if(this.player.currentTime == 0 || parseInt(this.player.currentTime) == this.lastCurrentTime ){
                return;
            }
            this.updateProgress();

        }, this.timeSLice);
    }

    updateProgress(){
        this.makeApiRequest().then(data =>{

        });
    }

    async makeApiRequest() {

        let formData = new FormData();
        formData.append('folder', this.folder.value );


        const response = await fetch("/viewing-status", {
          method: "POST",
          headers : {
            'X-CSRF-TOKEN' : document.querySelector("input[name='_token']").value
          },
          body: formData,
        });

        const data = await response.json();
        return data;


      }




}

var watchBroadcast = new WatchBroadcast();
watchBroadcast.init();
