<?php
include '../includes/db_connection.php'; // Ensure the correct path

// Assuming the employer ID is available via SESSION or GET
session_start();
$employer_id = isset($_SESSION['employer_id']) ? $_SESSION['employer_id'] : (isset($_GET['employer_id']) ? $_GET['employer_id'] : null);

if (!$employer_id) {
    die("Unauthorized access. Employer ID not provided.");
}

// Fetch seekers
$seekers_sql = "SELECT * FROM job_seeker";
$seekers_result = mysqli_query($conn, $seekers_sql);
$seekers = [];
while ($row = mysqli_fetch_assoc($seekers_result)) {
    $seekers[] = $row;
}

// Fetch applications filtered by the logged-in employer
$applications_sql = "
    SELECT 
        a.app_id,
        a.app_status,
        a.app_date,
        a.seeker_id,
        a.job_id,
        j.job_title,
        j.employer_id
    FROM application a
    JOIN job j ON a.job_id = j.job_id
    WHERE j.employer_id = ?
    ORDER BY a.app_date DESC
";
$stmt = mysqli_prepare($conn, $applications_sql);
mysqli_stmt_bind_param($stmt, "s", $employer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = $row;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = $_POST['app_id'];
    $new_status = $_POST['app_status'];

    $update_sql = "UPDATE application SET app_status = ? WHERE app_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ss", $new_status, $app_id);

    if (mysqli_stmt_execute($update_stmt)) {
        echo "<p style='color: green;'>Application status updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating status: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seeker and Application List</title>
</head>
<body>
<h2>List of Seekers</h2>
<?php if (empty($seekers)): ?>
    <p>No seekers found.</p>
<?php else: ?>
    <table border="1">
        <thead>
            <tr>
                <th>Seeker ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Skills</th>
                <th>Experience</th>
                <th>Resume</th>
                <th>Profile</th> <!-- New column for profile link -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seekers as $seeker): ?>
                <tr>
                    <td><?php echo htmlspecialchars($seeker['seeker_id']); ?></td>
                    <td><?php echo htmlspecialchars($seeker['name']); ?></td>
                    <td><?php echo htmlspecialchars($seeker['email']); ?></td>
                    <td><?php echo htmlspecialchars($seeker['ph_no']); ?></td>
                    <td><?php echo htmlspecialchars($seeker['skills']); ?></td>
                    <td><?php echo htmlspecialchars($seeker['experience']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($seeker['resume']); ?>" target="_blank">View Resume</a></td>
                    <td><a href="../seeker_profile.php?seeker_id=<?php echo urlencode($seeker['seeker_id']); ?>">View Profile</a></td> <!-- Profile link -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


    <h2>List of Applications</h2>
    <?php if (empty($applications)): ?>
        <p>No applications found.</p>
    <?php else: ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Application ID</th>
                    <th>Seeker ID</th>
                    <th>Job ID</th>
                    <th>Job Title</th>
                    <th>Application Status</th>
                    <th>Application Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['app_id']); ?></td>
                        <td><?php echo htmlspecialchars($application['seeker_id']); ?></td>
                        <td><?php echo htmlspecialchars($application['job_id']); ?></td>
                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                        <td><?php echo htmlspecialchars($application['app_status']); ?></td>
                        <td><?php echo htmlspecialchars($application['app_date']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($application['app_id']); ?>">
                                <select name="app_status">
                                    <option value="Pending" <?php if ($application['app_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Accepted" <?php if ($application['app_status'] === 'Accepted') echo 'selected'; ?>>Accepted</option>
                                    <option value="Rejected" <?php if ($application['app_status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
