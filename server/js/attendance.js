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

function search(value){
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

document.getElementById("export-attendance").addEventListener("click", function() {
    var startDate = document.getElementById("start-date").value;
    var endDate = document.getElementById("end-date").value;

    // Check if start date and end date are valid
    if (!startDate || !endDate) {
        alert("danger", "Please select both start and end dates.");
        return;
    }

    // Convert startDate and endDate to Date objects for comparison
    var startDateObj = new Date(startDate);
    var endDateObj = new Date(endDate);

    // Check if startDate is greater than endDate
    if (startDateObj > endDateObj) {
        alert("danger", "Start date cannot be greater than end date.");
        return;
    }

    // Prepare and send AJAX request to the backend
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/export_excel.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            var myModal = document.getElementById("export-attendance-details");
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
            // Create a link element to trigger the download
            var blob = new Blob([xhr.response], { type: "text/csv" });
            var link = document.createElement("a");
            link.href = window.URL.createObjectURL(blob);
            link.download = startDate + "_to_" + endDate + "_attendance.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            alert("success", "Attendance Details Downloaded Successfully.");
        } else {
            alert("danger", "Failed to download file. Server responded with status " + xhr.status);
        }
    };
    xhr.send("start_date=" + encodeURIComponent(startDate) + "&end_date=" + encodeURIComponent(endDate));
});

window.onload = function(){
    initSessionVars();
    getAttendance();
}

setInterval(getAttendance, 2000);
