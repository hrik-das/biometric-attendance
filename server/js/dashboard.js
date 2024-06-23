function getAnalytics() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/dashboard.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            // `this.responseText`'s formatting:
            // PxxxxxxxxxxAxxxxxxxxxMARxxxxxxxxxxxxxxxxxxxxxDARxxxxxxxxxxxxxxxxxxx
            //  presentNum absentNum   MonthlyAttendanceRate   DailyAttendanceRate
            const idxP = this.responseText.indexOf("P");
            const idxA = this.responseText.indexOf("A");
            const idxMAR = this.responseText.indexOf("M"); // MAR
            const idxDAR = this.responseText.indexOf("D"); // DAR

            document.getElementById("present").innerText = this.responseText.substring(1 + idxP, idxA);
            document.getElementById("absent").innerText = this.responseText.substring(1 + idxA, idxMAR);
            document.getElementById("m-a-r").innerText = this.responseText.substring(1 + idxMAR, idxDAR).concat("%");
            document.getElementById("d-a-r").innerText = this.responseText.substring(1 + idxDAR).concat("%");
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("get-analytics");
}

setInterval(getAnalytics, 2000);
