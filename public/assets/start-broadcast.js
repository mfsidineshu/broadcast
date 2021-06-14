class Broadcast {
  constructor() {


    this.broadcastStart = document.getElementById("start");
    this.broadcastStop = document.getElementById("stop");

    this.broadcastInfo = document.querySelector("section.broadcastInfo");
    this.broadcastLink = document.getElementById("broadcastLink");

    this.video = document.getElementById("broadcastVideo");
    this.recorder = null;
    this.order = 0;
    this.folder = null;


    this.shareBroadcast = document.getElementById("shareBroadcast");

    this.shareOverlay =  document.querySelector("div.shareOverlay");
    this.closeShareOverlay =  document.querySelector("div.closeShare");

    this.streamReference = null;

    this.folderInput = document.querySelector("input[name='folder']");

  }

  init() {

    this.broadcastStart.onclick = (e) => {
        this.broadcastStart.style.display = "none";
        this.broadcastInfo.style.display = "inline";
        this.captureCamera();
        return;
    };

    this.broadcastStop.onclick = (e) => {
        this.recorder.stop();
        this.broadcastInfo.style.display = "none";
        this.broadcastStart.style.display = "inline";
        this.video.srcObject = null;

        this.stopBroadcast().then(data =>{
            this.folder = null;
            this.folderInput.value="";
        });

        if(!this.streamReference) return;

        this.streamReference.getAudioTracks().forEach(function(track) {
            track.stop();
        });

        this.streamReference.getVideoTracks().forEach(function(track) {
            track.stop();
        });

        this.streamReference = null;

    }

    this.shareBroadcast.onclick = (e) => {
        this.shareOverlay.classList.add("active");

    }

    this.closeShareOverlay.onclick = (e) =>{
        this.shareOverlay.classList.remove("active");
    }

    document.querySelectorAll(".copy").forEach((ele) => {
        ele.onclick = (e) => {
            e.preventDefault();
            this.copyToClipboard(this.broadcastLink.getAttribute("data-server-addr") + '/' +this.folder);

        }
    });

  }
  captureCamera() {
    this.order = 0;

    this.broadcastInfo.style.display = "block";
    // this.video.src = this.video.srcObject = null;
    navigator.mediaDevices
      .getUserMedia({
        audio: true,
        video: true,
      })
      .then((camera) => {
        this.streamReference = camera;
        this.startRecording(camera);
      })
      .catch(function (error) {
        alert("Unable to capture your camera. Please check console logs.");
        console.error(error);
      });
  }

  startRecording(camera) {
    this.video.srcObject = camera;

    this.recorder = new MediaRecorder(camera,{
        mimeType: 'video/webm'
    });

    this.recorder.ondataavailable = (blobData) =>{
        if(!blobData.data.size > 0){
            return;
        }
        var reader = new FileReader();
        reader.readAsArrayBuffer(blobData.data);
        reader.onloadend = async (event) => {
          let arrayBuffer = reader.result;
          let uint8View = new Uint8Array(arrayBuffer);
          this.makeApiRequest(uint8View).then(data =>{

            if(this.folder == null ){
                this.folder = data["data"]["folder"];
                this.folderInput.value = data["data"]["folder"] && data["data"]["folder"]!=undefined ? this.folder : '';
                this.broadcastLink.value =  this.broadcastLink.getAttribute("data-server-addr") + '/' +this.folder;

            }


            // if(this.order == 0){

            // }
            // this.broadcastLink.setAttribute("href","./view-broadcast.php?folder="+this.folder);

          });
          this.order += 1;
        };
    };


    // this.recorder.startRecording();
    this.recorder.start(1000);

    // this.recorder.camera = camera;
  }

  async makeApiRequest(uint8View) {

    const response = await fetch("/save-chunks", {
      method: "POST",
      headers : {
        'X-CSRF-TOKEN' : document.querySelector("input[name='_token']").value
      },
      body: JSON.stringify({
        chunk: uint8View,
        order: this.order,
        folder: this.folder
      }),
    });

    const data = await response.json();
    return data;


  }

  async stopBroadcast() {
    const response = await fetch("/delete-broadcast/" + this.folder, {
      method: "DELETE",
      headers : {
        'X-CSRF-TOKEN' : document.querySelector("input[name='_token']").value
      }
    });
    const data = await response.json();
    return data;
  }

  copyToClipboard(str) {
    const el = document.createElement('textarea');
    el.value = str;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    const selected =
        document.getSelection().rangeCount > 0 ?
        document.getSelection().getRangeAt(0) :
        false;
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    if (selected) {
        document.getSelection().removeAllRanges();
        document.getSelection().addRange(selected);
    }
}



}

var broadcast = new Broadcast();
broadcast.init();
