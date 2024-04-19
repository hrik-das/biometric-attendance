function alert(type, message){
    let bs_class = (type == "success") ? "alert-success" : "alert-danger";
    let element = document.createElement("div");
    element.innerHTML = `
        <div class="alert ${bs_class} alert-dismissible fade show custom-alert" role="alert">
            <p>${message}</p>
        </div>`;
    document.body.append(element);
    setTimeout(alertRemove, 3000);
}

function alertRemove(){
    document.getElementsByClassName("alert")[0].remove();
}

function setActive(){
    let navbar = document.getElementById("navbar");
    let anchorTag = navbar.getElementsByTagName("a");
    let currentUrl = window.location.href.split("/").pop();
    for(let i=0; i<anchorTag.length; i++){
        let href = anchorTag[i].href.split("/").pop();
        if(currentUrl === href){
            anchorTag[i].classList.add("active");
        }
    }
}
setActive();