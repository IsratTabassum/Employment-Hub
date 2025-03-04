<?php
// Include the database connection
include 'db_connection.php'; // Ensure this file contains the $conn variable

// Start session to retrieve employer ID if stored in session
session_start();

// Get employer_id from session or query parameter
$employer_id = $_SESSION['employer_id'] ?? $_GET['employer_id'] ?? null;

// If employer_id is not available, show an error
if (!$employer_id) {
    die("Error: No employer ID provided. Please log in again.");
}

// Fetch organization details for the logged-in employer
$sql = "
    SELECT o.org_name, o.date_of_establish, o.ceo 
    FROM organization o
    JOIN employer e ON o.org_name = e.org_name
    WHERE e.employer_id = ?
";
$stmt = mysqli_prepare($conn, $sql);

// Check if query preparation was successful
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

// Bind and execute
mysqli_stmt_bind_param($stmt, "s", $employer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if we have a result
if (mysqli_num_rows($result) > 0) {
    // Fetch the organization data
    $organization = mysqli_fetch_assoc($result);
} else {
    // No result returned, handle accordingly
    echo "No organization found for the employer with ID: " . htmlspecialchars($employer_id);
    exit; // Stop further execution if no data found
}

// Handle form submission to update organization details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name = $_POST['org_name'];
    $date_of_establish = $_POST['date_of_establish'];
    $ceo = $_POST['ceo'];

    $update_sql = "
        UPDATE organization 
        SET org_name = ?, date_of_establish = ?, ceo = ? 
        WHERE org_name = ?
    ";
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if ($update_stmt === false) {
        die("Error preparing update statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($update_stmt, "ssss", $org_name, $date_of_establish, $ceo, $organization['org_name']);

    if (mysqli_stmt_execute($update_stmt)) {
        echo "<p style='color:green;'>Organization details updated successfully!</p>";

        // Update the organization data for the form
        $organization['org_name'] = $org_name;
        $organization['date_of_establish'] = $date_of_establish;
        $organization['ceo'] = $ceo;
    } else {
        echo "<p style='color:red;'>Error updating organization details: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Organization Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
        h2 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modify Your Organization Info</h2>
        <form method="POST">
            <label for="org_name">Organization Name:</label>
            <input type="text" id="org_name" name="org_name" value="<?php echo htmlspecialchars($organization['org_name']); ?>" required>

            <label for="date_of_establish">Date of Establishment:</label>
            <input type="date" id="date_of_establish" name="date_of_establish" value="<?php echo htmlspecialchars($organization['date_of_establish']); ?>" required>

            <label for="ceo">CEO:</label>
            <input type="text" id="ceo" name="ceo" value="<?php echo htmlspecialchars($organization['ceo']); ?>" required>

            <button type="submit">Update Information</button>
        </form>
    </div>
</body>
</html>
