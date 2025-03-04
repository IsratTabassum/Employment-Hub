<?php
include './includes/db_connection.php'; // Ensure this path is correct.

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid request: Employer ID is required.");
}

// Fetch employer profile details
$sql = "SELECT * FROM employer WHERE employer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);

if (!$profile) {
    die("Employer profile not found.");
}

// Handle form submission to update the profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dept = $_POST['dept'];
    $email = $_POST['email'];
    $ph_no = $_POST['ph_no'];
    $address = $_POST['address'];
    $org_name = $_POST['org_name'];

    $update_sql = "UPDATE employer SET dept = ?, email = ?, ph_no = ?, address = ?, org_name = ? WHERE employer_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssssss", $dept, $email, $ph_no, $address, $org_name, $id);

    if (mysqli_stmt_execute($update_stmt)) {
        echo "Profile updated successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
        exit;
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employer Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 500px;
            margin: auto;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Update Employer Profile</h2>
    <form method="POST">
        <label>Department:</label>
        <input type="text" name="dept" value="<?php echo htmlspecialchars($profile['dept']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>

        <label>Phone Number:</label>
        <input type="text" name="ph_no" value="<?php echo htmlspecialchars($profile['ph_no']); ?>" required>

        <label>Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>" required>

        <label>Organization:</label>
        <input type="text" name="org_name" value="<?php echo htmlspecialchars($profile['org_name']); ?>" required>

        <button type="submit">Update Profile</button>
    </form>
</body>
</html>
