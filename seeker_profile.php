<?php
include 'includes/db_connection.php'; // Ensure the correct path

// Get seeker_id from the URL
$seeker_id = isset($_GET['seeker_id']) ? $_GET['seeker_id'] : null;

if (!$seeker_id) {
    die("Seeker ID not provided.");
}

// Fetch the seeker's profile details
$sql = "SELECT * FROM job_seeker WHERE seeker_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $seeker_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$seeker_profile = mysqli_fetch_assoc($result);

if (!$seeker_profile) {
    die("Seeker not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seeker Profile</title>
</head>
<body>
    <h2>Seeker Profile</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($seeker_profile['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($seeker_profile['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($seeker_profile['ph_no']); ?></p>
    <p><strong>Skills:</strong> <?php echo htmlspecialchars($seeker_profile['skills']); ?></p>
    <p><strong>Experience:</strong> <?php echo htmlspecialchars($seeker_profile['experience']); ?></p>

    <?php if (!empty($seeker_profile['profile_pic'])): ?>
        <h3>Profile Picture:</h3>
        <!-- Ensure the path is correct -->
        <img src="<?php echo 'uploads/profile_pics/' . basename($seeker_profile['profile_pic']); ?>" alt="Profile Picture" style="max-width: 200px; max-height: 200px;">
    <?php else: ?>
        <p>No profile picture available.</p>
    <?php endif; ?>
</body>
</html>
