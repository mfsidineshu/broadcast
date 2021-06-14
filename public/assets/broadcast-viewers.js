class BroadcastViewers {
    constructor() {
        this.folder = document.querySelector("input[name='folder']");

        this.timeSLice = 3000;

        this.interval = null;

        this.broadviewersContainer = document.querySelector(
            "div.broadcast-viewers"
        );

        this.openBroadcastViewer = document.querySelectorAll(
            ".open-broadcast-viewers"
        );

        this.closeBroadcastViewer = document.querySelectorAll(
            ".close-broadcast-viewers"
        );

        this.viewersCountIndicator = document.querySelectorAll(
            ".viewers-count-indicator"
        );

        this.broadcastViewersListContainer = document.querySelector(
            ".broadcast-viewers-list"
        );



    }

    init() {
        this.openBroadcastViewer.forEach((ele) => {
            ele.onclick = () => {
                this.broadviewersContainer.classList.add("active");
            };
        });

        this.closeBroadcastViewer.forEach((ele) => {
            ele.onclick = () => {
                this.broadviewersContainer.classList.remove("active");
            };
        });

        this.interval = setInterval(()=>{

            if(this.folder.value == null || this.folder.value == undefined || this.folder.value.length < 1 ){
                return;
            }
            this.fetchViewers();

        }, this.timeSLice);
    }

    fetchViewers(){

        this.makeApiRequest().then(data =>{

            // console.log(data);
            this.viewersCountIndicator.forEach((ele)=>{
                ele.innerHTML = data["data"]["numberOfUsers"];
            })

            var listOfUsers = "";

            data["data"]["broadcastViewers"].forEach((user)=>{
                listOfUsers = `${listOfUsers}
            <li class="list-group-item">
                <a class="btn btn-primary"  role="button"> <i class="fa fa-user" aria-hidden="true"></i> ${user['name']} </a>
            </li>
                `;
            });

            this.broadcastViewersListContainer.innerHTML = listOfUsers;
        });

    }



    async makeApiRequest() {

        const response = await fetch(
            "/broadcast-viewers?folder=" + this.folder.value,
            {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        "input[name='_token']"
                    ).value,
                },
            }
        );
        const data = await response.json();
        return data;
    }
}

var broadcastViewers = new BroadcastViewers();
broadcastViewers.init();
