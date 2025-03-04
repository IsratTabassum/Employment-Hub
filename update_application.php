<?php
include 'includes/db_connection.php';

// Get the application ID from the query string
$app_id = $_GET['app_id'] ?? null;

if (!$app_id) {
    die("Invalid request: Application ID is required.");
}

// Fetch application status
$query = "SELECT app_status FROM application WHERE app_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $app_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $application = mysqli_fetch_assoc($result);
    $app_status = $application['app_status'];
} else {
    die("Application not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        .status {
            font-size: 18px;
            font-weight: bold;
            color: #555;
            margin-top: 20px;
        }
        a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Application Status</h2>
        <div class="status">
            Status: <span><?php echo htmlspecialchars($app_status); ?></span>
        </div>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
