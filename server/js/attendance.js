const localDate = new Date();
const formattedDate = `${localDate.getFullYear()}-${(localDate.getMonth() + 1).toString().padStart(2, "0")}-${localDate.getDate().toString().padStart(2, "0")}`;
document.getElementById("date").value = formattedDate;

function initSessionVars() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            console.log("Session variables set for ajax/attendance.php")
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("init-session-vars");
}

function getAttendance() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("attendance-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("get-attendance");
}

function selectDate(date){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            getAttendance();
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("dropdown-date="+date);
    
}

function selectSemester(sem){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            getAttendance();
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("dropdown-sem="+sem);
}

function searchStudent(value){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("attendance-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("search-student&value="+value);
}

window.onload = function(){
    initSessionVars();
    getAttendance();
}

setInterval(getAttendance, 2000);
