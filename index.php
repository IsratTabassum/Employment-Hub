<?php
session_start(); // Start session to enable session variables
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["login"])) {
        $user_id = $_POST["seeker_id"];
        $user_type = $_POST["user_type"];

        // Validate user and set session variable based on user type
        if ($user_type == 'seeker') {
            $_SESSION['seeker_id'] = $user_id; // Set session for job seeker
            header("Location: dashboard.php?user_type=seeker&seeker_id=" . $user_id);
            exit;
        } else if ($user_type == 'employer') {
            $_SESSION['employer_id'] = $user_id; // Set session for employer
            header("Location: dashboard.php?user_type=employer&employer_id=" . $user_id);
            exit;
        }
    } elseif (isset($_POST["register_seeker"])) {
        // Generate a new Seeker ID
        $last_id_query = "SELECT seeker_id FROM job_seeker ORDER BY seeker_id DESC LIMIT 1";
        $result = mysqli_query($conn, $last_id_query);
        $last_id = mysqli_fetch_assoc($result);

        if ($last_id) {
            $last_number = (int)substr($last_id["seeker_id"], 1); // Extract number part
            $new_id_number = $last_number + 1;
        } else {
            $new_id_number = 1; // Start from S001 if no records exist
        }

        $seeker_id = "S" . str_pad($new_id_number, 3, "0", STR_PAD_LEFT); // Format as S001

        // Collect other form data
        $name = $_POST["name"];
        $email = $_POST["email"];
        $phone = $_POST["phone"];
        $skills = $_POST["skills"];
        $experience = $_POST["experience"];
        $resume = $_POST["resume"];

        // Insert the new seeker
        $sql = "INSERT INTO job_seeker (seeker_id, name, email, ph_no, skills, experience, resume) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $seeker_id, $name, $email, $phone, $skills, $experience, $resume);

        if (mysqli_stmt_execute($stmt)) {
            echo "Job seeker profile created successfully! Your ID is: <strong>$seeker_id</strong>";
        } else {
            echo "Error creating profile: " . mysqli_error($conn);
        }
    } elseif (isset($_POST["register_employer"])) {
        // Generate a new Employer ID
        $last_id_query = "SELECT employer_id FROM employer ORDER BY employer_id DESC LIMIT 1";
        $result = mysqli_query($conn, $last_id_query);
        $last_id = mysqli_fetch_assoc($result);

        if ($last_id) {
            $last_number = (int)substr($last_id["employer_id"], 3); // Extract number part
            $new_id_number = $last_number + 1;
        } else {
            $new_id_number = 1; // Start from EMP001 if no records exist
        }

        $employer_id = "EMP" . str_pad($new_id_number, 3, "0", STR_PAD_LEFT); // Format as EMP001

        // Collect other form data
        $org_name = $_POST["org_name"];
        $date_of_establish = $_POST["date_of_establish"];
        $ceo = $_POST["ceo"];
        $dept = $_POST["dept"];
        $email = $_POST["email"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];

        // Insert the organization
        $org_sql = "INSERT INTO organization (org_name, date_of_establish, ceo) VALUES (?, ?, ?)";
        $org_stmt = mysqli_prepare($conn, $org_sql);
        mysqli_stmt_bind_param($org_stmt, "sss", $org_name, $date_of_establish, $ceo);

        // Insert the employer
        $emp_sql = "INSERT INTO employer (employer_id, dept, email, ph_no, address, org_name) VALUES (?, ?, ?, ?, ?, ?)";
        $emp_stmt = mysqli_prepare($conn, $emp_sql);
        mysqli_stmt_bind_param($emp_stmt, "ssssss", $employer_id, $dept, $email, $phone, $address, $org_name);

        if (mysqli_stmt_execute($org_stmt) && mysqli_stmt_execute($emp_stmt)) {
            echo "Employer profile and organization created successfully! Your ID is: <strong>$employer_id</strong>";
        } else {
            echo "Error creating profile: " . mysqli_error($conn);
        }
    }
}
?>

<!-- Login Form -->
<h2>Login</h2>
<form method="POST" action="">
    <input type="text" name="seeker_id" required placeholder="Enter ID"><br>
    <select name="user_type">
        <option value="seeker">Job Seeker</option>
        <option value="employer">Employer</option>
    </select><br>
    <button type="submit" name="login">Login</button>
</form>

<!-- Registration Options -->
<h2>Create Profile</h2>
<button onclick="document.getElementById('seekerForm').style.display='block'">Create Job Seeker Profile</button>
<button onclick="document.getElementById('employerForm').style.display='block'">Create Employer Profile</button>

<!-- Job Seeker Registration Form -->
<div id="seekerForm" style="display:none;">
    <h3>Register as Job Seeker</h3>
    <form method="POST" action="">
        <input type="text" name="name" required placeholder="Name"><br>
        <input type="email" name="email" required placeholder="Email"><br>
        <input type="text" name="phone" required placeholder="Phone"><br>
        <textarea name="skills" required placeholder="Skills"></textarea><br>
        <textarea name="experience" required placeholder="Experience"></textarea><br>
        <input type="text" name="resume" required placeholder="Resume Link"><br>
        <button type="submit" name="register_seeker">Register</button>
    </form>
</div>

<!-- Employer and Organization Registration Form -->
<div id="employerForm" style="display:none;">
    <h3>Register as Employer</h3>
    <form method="POST" action="">
        <input type="text" name="org_name" required placeholder="Organization Name"><br>
        <input type="date" name="date_of_establish" required><br>
        <input type="text" name="ceo" required placeholder="CEO Name"><br>
        <input type="text" name="dept" required placeholder="Department"><br>
        <input type="email" name="email" required placeholder="Email"><br>
        <input type="text" name="phone" required placeholder="Phone"><br>
        <input type="text" name="address" required placeholder="Address"><br>
        <button type="submit" name="register_employer">Register</button>
    </form>
</div>
