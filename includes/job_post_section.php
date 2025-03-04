<?php
session_start();

// Debug session data
if (!isset($_SESSION['employer_id'])) {
    echo "Debug Info: Session variables:";
    var_dump($_SESSION);
    die("You must be logged in as an employer to access this page.");
}

$employer_id = $_SESSION['employer_id'];

include '../includes/db_connection.php'; // Ensure this path is correct

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Debug employer ID
//echo "Debug Info: employer_id = " . htmlspecialchars($employer_id) . "<br>";

// Handle job posting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = $_POST['job_title'];
    $description = $_POST['description'];
    $skills_required = $_POST['skills_required'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];

    // Generate a unique job ID with the format JOB001, JOB002, etc.
    $latest_job_sql = "SELECT job_id FROM job ORDER BY job_id DESC LIMIT 1";
    $latest_stmt = mysqli_prepare($conn, $latest_job_sql);
    mysqli_stmt_execute($latest_stmt);
    $latest_result = mysqli_stmt_get_result($latest_stmt);
    $latest_job = mysqli_fetch_assoc($latest_result);

    if ($latest_job) {
        $last_id = (int)substr($latest_job['job_id'], 3); // Extract numeric part from JOBXXX
        $new_id = $last_id + 1;
    } else {
        $new_id = 1; // Start from JOB001 if no jobs exist
    }

    $job_id = 'JOB' . str_pad($new_id, 3, '0', STR_PAD_LEFT); // Pad with leading zeros

    $sql = "INSERT INTO job (job_id, employer_id, job_title, description, req_skills, salary, location) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $job_id, $employer_id, $job_title, $description, $skills_required, $salary, $location);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p>Job posted successfully! Job ID: $job_id</p>";
    } else {
        echo "<p>Error posting job: " . mysqli_error($conn) . "</p>";
    }
}

// Handle job deletion
if (isset($_GET['delete_job_id'])) {
    $delete_job_id = $_GET['delete_job_id'];
    $delete_sql = "DELETE FROM job WHERE job_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "s", $delete_job_id);

    if (mysqli_stmt_execute($delete_stmt)) {
        echo "<p>Job deleted successfully!</p>";
    } else {
        echo "<p>Error deleting job: " . mysqli_error($conn) . "</p>";
    }
}

// Fetch jobs posted by the employer
$jobs_sql = "SELECT * FROM job WHERE employer_id = ?";
$jobs_stmt = mysqli_prepare($conn, $jobs_sql);
mysqli_stmt_bind_param($jobs_stmt, "s", $employer_id);
mysqli_stmt_execute($jobs_stmt);
$jobs_result = mysqli_stmt_get_result($jobs_stmt);

// Debug job results
if (!$jobs_result) {
    die("Error fetching jobs: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management</title>
</head>
<body>
    <!-- Job Posting Form -->
    <h2>Post a New Job</h2>
    <form method="POST" action="">
        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" required><br>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea><br>

        <label for="skills_required">Required Skills:</label>
        <input type="text" name="skills_required" required><br>

        <label for="salary">Salary:</label>
        <input type="text" name="salary" required><br>

        <label for="location">Location:</label>
        <input type="text" name="location" required><br>

        <button type="submit">Post Job</button>
    </form>

    <!-- List of Jobs Posted -->
    <h2>Your Posted Jobs</h2>
    <?php if (mysqli_num_rows($jobs_result) > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Skills Required</th>
                    <th>Salary</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($job['job_id']); ?></td>
                        <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                        <td><?php echo htmlspecialchars($job['description']); ?></td>
                        <td><?php echo htmlspecialchars($job['req_skills']); ?></td>
                        <td><?php echo htmlspecialchars($job['salary']); ?></td>
                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                        <td>
                            <a href="?delete_job_id=<?php echo htmlspecialchars($job['job_id']); ?>" onclick="return confirm('Are you sure you want to delete this job?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No jobs posted yet.</p>
    <?php endif; ?>
</body>
</html>
