<?php
include '../includes/db_connection.php';

// Get user type and ID
$user_type = $_GET['user_type'];
$id = $_GET['seeker_id']; // Ensure this file is accessed only for seekers

// Ensure the user type is seeker
if ($user_type !== 'seeker') {
    echo "<h1>Unauthorized Access</h1>";
    exit;
}

// Fetch applications for the logged-in seeker
$applications_query = "SELECT * FROM application WHERE seeker_id='$id'";
$applications_result = mysqli_query($conn, $applications_query);
$applications = mysqli_fetch_all($applications_result, MYSQLI_ASSOC);

// Fetch available jobs
$jobs_query = "SELECT job_id FROM job";
$jobs_result = mysqli_query($conn, $jobs_query);
$jobs = mysqli_fetch_all($jobs_result, MYSQLI_ASSOC);

// Handle form submission for adding a new application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_application'])) {
    $job_id = $_POST['job_id'];

    // Generate a unique application ID with the format APP001, APP002, etc.
    $latest_app_sql = "SELECT app_id FROM application ORDER BY app_id DESC LIMIT 1";
    $latest_stmt = mysqli_prepare($conn, $latest_app_sql);
    mysqli_stmt_execute($latest_stmt);
    $latest_result = mysqli_stmt_get_result($latest_stmt);
    $latest_app = mysqli_fetch_assoc($latest_result);

    if ($latest_app) {
        $last_id = (int)substr($latest_app['app_id'], 3); // Extract numeric part from APPXXX
        $new_id = $last_id + 1;
    } else {
        $new_id = 1; // Start from APP001 if no applications exist
    }

    $app_id = 'APP' . str_pad($new_id, 3, '0', STR_PAD_LEFT); // Pad with leading zeros

    $app_date = date('Y-m-d'); // Current date
    $app_status = 'Pending'; // Default status

    $insert_query = "INSERT INTO application (app_id, seeker_id, job_id, app_status, app_date) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssss", $app_id, $id, $job_id, $app_status, $app_date);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green;'>Application submitted successfully! Application ID: $app_id</p>";
    } else {
        echo "<p style='color:red;'>Error submitting application: " . mysqli_error($conn) . "</p>";
    }
}

// Handle deletion of an application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_application'])) {
    $app_id = $_POST['app_id'];

    $delete_query = "DELETE FROM application WHERE app_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "s", $app_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green;'>Application deleted successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error deleting application: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Applications</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        form {
            margin-top: 20px;
        }
        select, button {
            padding: 10px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <h2>Your Applications</h2>
    <table>
        <thead>
            <tr>
                <th>App ID</th>
                <th>Job ID</th>
                <th>Application Status</th>
                <th>Application Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="5">No applications found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application["app_id"]); ?></td>
                        <td><?php echo htmlspecialchars($application["job_id"]); ?></td>
                        <td><?php echo htmlspecialchars($application["app_status"]); ?></td>
                        <td><?php echo htmlspecialchars($application["app_date"]); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($application["app_id"]); ?>">
                                <button type="submit" name="delete_application" style="color:red;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Apply for a Job</h2>
    <form method="POST">
        <label for="job_id">Select Job:</label>
        <select name="job_id" id="job_id" required>
            <option value="">-- Select a Job ID --</option>
            <?php foreach ($jobs as $job): ?>
                <option value="<?php echo htmlspecialchars($job['job_id']); ?>">
                    <?php echo htmlspecialchars($job['job_id']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_application">Submit Application</button>
    </form>
</body>
</html>
