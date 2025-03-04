<?php
include 'db_connection.php'; // Ensure the database connection is included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seeker_id = $_POST["seeker_id"];

    // Check if a file is uploaded
    if (isset($_FILES["resume"]) && $_FILES["resume"]["error"] == UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/resumes/"; // Adjust path based on your project structure
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
            // Update the resume path in the database
            $update_query = "UPDATE job_seeker SET resume = ? WHERE seeker_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ss", $target_file, $seeker_id);

            if (mysqli_stmt_execute($stmt)) {
                // Redirect or provide success feedback
                echo "Resume uploaded successfully!";
                header("Location: ../dashboard.php?upload=success"); // Redirect to the dashboard
                exit;
            } else {
                echo "Error updating resume: " . mysqli_error($conn);
            }
        } else {
            echo "Failed to upload the resume. Please try again.";
        }
    } else {
        echo "No file uploaded or an error occurred.";
    }
} else {
    echo "Invalid request method.";
}
?>
