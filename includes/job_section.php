<?php
// Include the database connection
include 'db_connection.php';

// Fetch available jobs
$sql = "SELECT * FROM job";
$result = mysqli_query($conn, $sql);

// Check if we have jobs available
if (mysqli_num_rows($result) > 0) {
    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
} else {
    $jobs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Available Jobs</h2>

        <?php if (count($jobs) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Job Title</th>
                        <th>Description</th>
                        <th>Required Skills</th>
                        <th>Salary</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job['job_id']); ?></td>
                            <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($job['description']); ?></td>
                            <td><?php echo htmlspecialchars($job['req_skills']); ?></td>
                            <td><?php echo number_format($job['salary'], 2); ?></td>
                            <td><?php echo htmlspecialchars($job['location']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No jobs available at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
