class Broadcast {
  constructor() {
    this.broadcastToggler = document.getElementById("broadcastToggle");
    this.broadcastInfo = document.querySelector("div.broadcastInfo");
    this.broadcastLink = document.getElementById("broadcastLink");
    this.video = document.getElementById("broadcastVideo");
    this.recorder = null;
    this.order = 0;
    this.folder = null;


  }

  init() {
    this.broadcastToggler.onclick = (e) => {
      if (e.target.classList.contains("play")) {
        e.target.classList.remove("play");
        e.target.classList.add("stop");
        e.target.innerHTML = "STOP BROADCAST";
        this.captureCamera();
        return;
      }

      e.target.classList.add("play");
      e.target.classList.remove("stop");
      e.target.innerHTML = "START BROADCAST";

      this.broadcastInfo.style.display = "none";

      this.recorder.stop();
      this.video.src = this.video.srcObject = null;

    };
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

            this.folder = data["data"]["folder"];
            this.broadcastLink.setAttribute("href","./view-broadcast.php?folder="+this.folder);


          });
          this.order += 1;
        };
    };

    // this.recorder = RecordRTC(camera, {
    //   recorderType: MediaStreamRecorder,
    //   mimeType: "video/webm",
    //   timeSlice: 1000,

    //   ondataavailable: (blob) => {
    //     var reader = new FileReader();
    //     reader.readAsArrayBuffer(blob);
    //     reader.onloadend = async (event) => {
    //       let arrayBuffer = reader.result;
    //       let uint8View = new Uint8Array(arrayBuffer);
    //       this.makeApiRequest(uint8View).then(data =>{

    //         this.folder = data["data"]["folder"];
    //         this.broadcastLink.setAttribute("href","./view-broadcast.php?folder="+this.folder);


    //       });
    //       this.order += 1;
    //     };
    //   },


    // });

    // this.recorder.startRecording();
    this.recorder.start(1000);

    // this.recorder.camera = camera;
  }

  async makeApiRequest(uint8View) {


    console.log({
        chunk: uint8View,
        order: this.order,
        folder: this.folder
      });


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



}

var broadcast = new Broadcast();
broadcast.init();
