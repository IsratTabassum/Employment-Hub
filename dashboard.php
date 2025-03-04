<?php
include 'includes/db_connection.php'; // Ensure the correct path to db_connection.php

// Get user type and ID from query parameters
$user_type = $_GET['user_type'] ?? null;
$id = $_GET[$user_type . '_id'] ?? null;

// Validate input
if (!$user_type || !$id) {
    echo "<h1>Invalid request: Missing user type or ID.</h1>";
    exit;
}

// Fetch user information based on type
if ($user_type === 'seeker') {
    $sql = "SELECT * FROM job_seeker WHERE seeker_id = ?";
} elseif ($user_type === 'employer') {
    $sql = "SELECT * FROM employer WHERE employer_id = ?";
} else {
    echo "<h1>Invalid User Type.</h1>";
    exit;
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);

// Check if the profile exists
if ($profile) {
    echo "<h1>Welcome, " . htmlspecialchars($profile['name'] ?? $profile['email']) . "!</h1>";
} else {
    echo "<h1>Invalid User ID or Type.</h1>";
    exit;
}
?>

<!-- Navigation Buttons -->
<?php if ($user_type === 'seeker'): ?>
    <button onclick="location.href='includes/profile_section.php?user_type=<?php echo $user_type; ?>&id=<?php echo $id; ?>'">Profile</button>
    <button onclick="location.href='includes/application_section.php?user_type=seeker&seeker_id=<?php echo $id; ?>'">Applications</button>
    <button onclick="location.href='includes/job_section.php?user_type=seeker&seeker_id=<?php echo $id; ?>'">Jobs</button>
<?php elseif ($user_type === 'employer'): ?>
    <button onclick="location.href='includes/organization_section.php?user_type=employer&employer_id=<?php echo $id; ?>'">Organization</button>
    <button onclick="location.href='update_emp_profile.php?id=<?php echo $id; ?>'">Edit Profile</button>
    <button onclick="location.href='includes/job_post_section.php?user_type=employer&employer_id=<?php echo $id; ?>'">Jobs</button>
    <button onclick="location.href='includes/seeker_list.php?employer_id=<?php echo $id; ?>'">Seekers and Applications</button>
<?php endif; ?>

<!-- Delete Profile Button -->
<h3>Delete Profile</h3>
<form method="POST" action="">
    <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">
    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($id); ?>">
    <button type="submit" name="delete_profile" onclick="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');">
        Delete Profile
    </button>
</form>

<?php
// Handle profile deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    $user_type = $_POST['user_type'];
    $user_id = $_POST['user_id'];

    if ($user_type === 'seeker') {
        $delete_query = "DELETE FROM job_seeker WHERE seeker_id = ?";
    } elseif ($user_type === 'employer') {
        $delete_query = "DELETE FROM employer WHERE employer_id = ?";
    } else {
        echo "<h1>Invalid User Type.</h1>";
        exit;
    }

    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green;'>Profile deleted successfully!</p>";
        // Redirect to homepage or login page
        header("Location: index.php?message=ProfileDeleted");
        exit;
    } else {
        echo "<p style='color:red;'>Error deleting profile: " . mysqli_error($conn) . "</p>";
    }
}
?>
