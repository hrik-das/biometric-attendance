let addStudentForm = document.getElementById("add-student-form");

addStudentForm.addEventListener("submit", (event) => {
    event.preventDefault();
    addStudent();
});

const addStudent = () => {
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
                alert("success", "New Student Added Successfully!");
            }else{
                alert("error", "Cannot add New Student!");
            }
        }else{
            console.error("Request failed with status: ", xhr.status);
        }
    }
    xhr.onerror = function(){
        console.error("Network error occurred!");
    }
    xhr.send(data);
}