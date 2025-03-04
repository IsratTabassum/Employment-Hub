<?php
// Fetch employer and organization details
$emp_sql = "SELECT * FROM employer WHERE employer_id = ?";
$emp_stmt = mysqli_prepare($conn, $emp_sql);
mysqli_stmt_bind_param($emp_stmt, "s", $profile_id);
mysqli_stmt_execute($emp_stmt);
$emp_result = mysqli_stmt_get_result($emp_stmt);
$employer = mysqli_fetch_assoc($emp_result);

// Fetch organization details
$org_sql = "SELECT * FROM organization WHERE org_name = ?";
$org_stmt = mysqli_prepare($conn, $org_sql);
mysqli_stmt_bind_param($org_stmt, "s", $employer['org_name']);
mysqli_stmt_execute($org_stmt);
$org_result = mysqli_stmt_get_result($org_stmt);
$organization = mysqli_fetch_assoc($org_result);
?>

<h2>Employer Dashboard</h2>
<h3>Organization Info</h3>
<p>Organization Name: <?php echo $organization['org_name']; ?></p>
<p>Date of Establishment: <?php echo $organization['date_of_establish']; ?></p>
<p>CEO: <?php echo $organization['ceo']; ?></p>

<h3>Profile Info</h3>
<p>Name: <?php echo $employer['name']; ?></p>
<p>Email: <?php echo $employer['email']; ?></p>

<h3>Job Listings</h3>
<!-- Add job listing management logic here -->

<h3>Seeker List</h3>
<!-- Add seeker list display logic here -->
