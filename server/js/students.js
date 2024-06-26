let addStudentForm = document.getElementById("add-student-form");
let editStudentForm = document.getElementById("edit-student-form");

document.getElementById("add-btn").addEventListener("click", () => {
    const localDate = new Date();
    const formattedDate = `${localDate.getFullYear()}-${(localDate.getMonth() + 1).toString().padStart(2, "0")}-${localDate.getDate().toString().padStart(2, "0")}`;
    document.getElementById('default-date').value = formattedDate;
});

addStudentForm.addEventListener("submit", (event) => {
    event.preventDefault();
    addStudent();
});

editStudentForm.addEventListener("submit", (event) => {
    event.preventDefault();
    updateStudent();
});

function addStudent(){
    let data = new FormData();
    data.append("roll", addStudentForm.elements["roll"].value);
    data.append("name", addStudentForm.elements["name"].value);
    data.append("email", addStudentForm.elements["email"].value);
    data.append("phone", addStudentForm.elements["phone"].value);
    data.append("sem", addStudentForm.elements["sem"].value);
    data.append("date", addStudentForm.elements["date"].value);
    data.append("add-student", "");
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            var myModal = document.getElementById("add-student");
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
            if(this.responseText == 1){
                alert("success", "New Student Added Successfully.");
                addStudentForm.reset();
                getStudents();
            }else if (this.responseText == 2) {
                alert("error", "Student already enlisted.");
                addStudentForm.reset();
            }else{
                alert("error", "Operation Failed!");
            }
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send(data);
}

function getStudents(){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("student-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("get-all-enlisted");
}

function removeStudent(rollNumber){
    if(confirm(`Are You Sure You want to Delete this Student: (Roll Number - ${rollNumber})?`)){
        let data = new FormData();
        data.append("roll", rollNumber);
        data.append("remove-student", "");
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "./ajax/students.php", true);
        xhr.onload = function(){
            if(xhr.status >= 200 && xhr.status < 300){
                if(this.responseText == 1){
                    alert("success", "Student Removed Successfully.");
                    getStudents();
                }else{
                    alert("error", "Student Removal Failed!");
                }
            }else{
                console.error("Request failed with status : ", xhr.status);
            }
        }
        xhr.onerror = function(){
            console.error("Network error occurred!");
        }
        xhr.send(data);
    }
}

function editStudent(rollNumber){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            let data = JSON.parse(this.responseText);
            editStudentForm.elements["roll"].value = data.roll_no;
            editStudentForm.elements["name"].value = data.full_name;
            editStudentForm.elements["email"].value = data.email;
            editStudentForm.elements["phone"].value = data.contact;
            editStudentForm.elements["sem"].value = data.semester;
            editStudentForm.elements["date"].value = data.enlist_date;
            editStudentForm.elements["id"].value = data.fingerprint_id;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("get-student="+rollNumber);
}

function updateStudent(){
    let data = new FormData();
    data.append("roll", editStudentForm.elements["roll"].value);
    data.append("name", editStudentForm.elements["name"].value);
    data.append("email", editStudentForm.elements["email"].value);
    data.append("phone", editStudentForm.elements["phone"].value);
    data.append("sem", editStudentForm.elements["sem"].value);
    data.append("date", editStudentForm.elements["date"].value);
    data.append("id", editStudentForm.elements["id"].value);
    data.append("update-details", "");
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            var myModal = document.getElementById("edit-student");
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
            if(this.responseText == 1){
                alert("success", "Student Details Updated Successfully.");
                editStudentForm.reset();
                getStudents();
            }else{
                alert("error", "Detail Updation Failed!");
            }
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send(data);
}

function searchStudent(value){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("student-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("search-student&value="+value);
}

function selectSemester(sem){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("student-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("dropdown-sem="+sem);
}

function selectRegStatus(regStatus){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.status >= 200 && xhr.status < 300){
            document.getElementById("student-data").innerHTML = this.responseText;
        }else{
            console.error("Request failed with status : ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send("dropdown-reg-status="+regStatus);
}

document.getElementById("update-finger").addEventListener("click", updateFingerprint);

function updateFingerprint(){
    let rollNumber = document.getElementsByName("roll")[1].value;
    if(confirm(`Are You Sure, You want to update Fingerprint for Rollnumber - ${rollNumber}?`)){
        let data = new FormData();
        data.append("roll", rollNumber);
        data.append("update-fingerprint", "");
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "./ajax/students.php", true);
        xhr.onload = function(){
            if(xhr.status >= 200 && xhr.status < 300){
                if(this.responseText == 1){
                    alert("success", "Student Fingerprint Updated Successfully.");
                    // editStudentForm.reset();
                    // getStudents();
                }else{
                    alert("error", "Fingerprint Updation Failed!");
                }
            }else{
                console.error("Request failed with status : ", xhr.status);
            }
        }
        xhr.onerror = function(){
            console.error("Network error occurred!");
        }
        xhr.send(data);
    }
}

function toTitleCase(str) {
    return str.toLowerCase().replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });
}

document.getElementById("export").addEventListener("click", function() {
    var status = document.getElementById("export-status").value;
    // Send AJAX request to the backend
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./ajax/export_excel.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            var myModal = document.getElementById("export-excel");
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
            // Create a link element to trigger the download
            var blob = new Blob([xhr.response], { type: "text/csv" });
            var link = document.createElement("a");
            link.href = window.URL.createObjectURL(blob);
            link.download = status + "_student_details.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            alert("success", toTitleCase(status) +" Students Details Downloaded Successfully.");
        } else {
            alert("Failed to download file. Server responded with status " + xhr.status);
        }
    };
    xhr.send("status=" + encodeURIComponent(status));
});

window.onload = function(){
    getStudents();
}