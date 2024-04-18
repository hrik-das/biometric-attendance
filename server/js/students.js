let addStudentForm = document.getElementById("add-student-form");
const currentDate = new Date().toISOString().split("T")[0];
document.getElementById("default-date").value = currentDate;

addStudentForm.addEventListener("submit", (event) => {
    event.preventDefault();
    addStudent();
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
    xhr.send("get-students");
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

window.onload = function(){
    getStudents();
}