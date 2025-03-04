<?php
include 'includes/db_connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seeker_id = $_POST["seeker_id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $ph_no = $_POST["ph_no"];
    $skills = $_POST["skills"];
    $experience = $_POST["experience"];
    
    $resume_path = ""; // Default empty value for resume

    // Check if a file is uploaded
    if (isset($_FILES["resume"]) && $_FILES["resume"]["error"] == UPLOAD_ERR_OK) {
        $upload_dir = "uploads/resumes/";
        $file_name = basename($_FILES["resume"]["name"]);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Validate file type (only allow PDFs)
        if (strtolower($file_extension) !== "pdf") {
            echo "Only PDF files are allowed.";
            exit;
        }

        // Generate a unique file name to avoid overwriting
        $target_file = $upload_dir . $seeker_id . "_" . time() . ".pdf";

        // Move the uploaded file to the server directory
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $resume_path = $target_file; // Store the file path for database update
        } else {
            echo "Failed to upload the resume. Please try again.";
            exit;
        }
    } else {
        // If no file is uploaded, keep the existing resume path
        $query = "SELECT resume FROM job_seeker WHERE seeker_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $seeker_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $resume_path);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    // Update profile in the database
    $update_query = "UPDATE job_seeker SET name=?, email=?, ph_no=?, skills=?, experience=?, resume=? WHERE seeker_id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $ph_no, $skills, $experience, $resume_path, $seeker_id);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Redirect back to dashboard (index.php) with a success message
        header("Location: index.php?profile_update=success");
        exit;
    } else {
        // If there's an error, show an error message
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!-- HTML Form for Profile Update -->
<form action="update_profile.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="seeker_id" value="<?php echo htmlspecialchars($seeker_id); ?>">
    
    <label for="name">Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    
    <label for="ph_no">Phone Number:</label>
    <input type="text" name="ph_no" value="<?php echo htmlspecialchars($ph_no); ?>" required>
    
    <label for="skills">Skills:</label>
    <input type="text" name="skills" value="<?php echo htmlspecialchars($skills); ?>" required>
    
    <label for="experience">Experience:</label>
    <input type="text" name="experience" value="<?php echo htmlspecialchars($experience); ?>" required>
    
    <!-- Resume Upload Section (only for job seekers) -->
    <?php if (!empty($seeker_id)): ?>
        <label for="resume">Resume (PDF only):</label>
        <input type="file" name="resume" accept=".pdf">
    <?php endif; ?>

    <button type="submit">Update Profile</button>
</form>
