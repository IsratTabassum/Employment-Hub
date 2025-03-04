<?php
include '../includes/db_connection.php';

// Get user type and ID from URL parameters
$user_type = $_GET['user_type'] ?? null;
$id = $_GET['id'] ?? null;

// Validate user type and ID
if (!$user_type || !$id || !in_array($user_type, ['seeker', 'employer'])) {
    die("Invalid request");
}

// Fetch profile details based on user type
$table = $user_type == 'seeker' ? 'job_seeker' : 'employer';
$id_column = $user_type == 'seeker' ? 'seeker_id' : 'employer_id';
$sql = "SELECT * FROM $table WHERE $id_column = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);

if (!$profile) {
    die("Profile not found");
}

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if ($user_type == 'seeker') {
        $skills = $_POST['skills'];
        $experience = $_POST['experience'];

        $update_sql = "UPDATE job_seeker SET name=?, email=?, ph_no=?, skills=?, experience=? WHERE seeker_id=?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssssss", $name, $email, $phone, $skills, $experience, $id);
    } else {
        $organization = $_POST['organization'];
        $update_sql = "UPDATE employer SET name=?, email=?, phone=?, organization=? WHERE employer_id=?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sssss", $name, $email, $phone, $organization, $id);
    }

    if (mysqli_stmt_execute($update_stmt)) {
        echo "Profile updated successfully!";
        header("Refresh:0");
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}

// Handle resume upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resume'])) {
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/resumes/";
        $file_name = basename($_FILES["resume"]["name"]);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Validate file type
        if (strtolower($file_extension) !== "pdf") {
            echo "Only PDF files are allowed.";
            exit;
        }

        $new_file_name = $id . "_" . time() . ".pdf";
        $target_file = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            $update_resume_sql = "UPDATE job_seeker SET resume = ? WHERE seeker_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_resume_sql);
            mysqli_stmt_bind_param($update_stmt, "ss", $target_file, $id);

            if (mysqli_stmt_execute($update_stmt)) {
                echo "Resume uploaded successfully!";
                header("Refresh:0");
            } else {
                echo "Error saving resume to the database: " . mysqli_error($conn);
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "Error uploading file.";
    }
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = "../uploads/profile_pics/";
    $file_name = basename($_FILES["profile_pic"]["name"]);
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
        echo "Only JPG and PNG files are allowed.";
        exit;
    }

    // Generate a unique file name
    $new_file_name = $id . "_profile_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $new_file_name;

    // Move the uploaded file to the server directory
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        // Save the file path in the database
        $update_pic_sql = "UPDATE job_seeker SET profile_pic = ? WHERE seeker_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_pic_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $target_file, $id);

        if (mysqli_stmt_execute($update_stmt)) {
            echo "Profile picture uploaded successfully!";
            header("Refresh:0");
        } else {
            echo "Error saving profile picture to the database: " . mysqli_error($conn);
        }
    } else {
        echo "Error moving uploaded file.";
    }
}
?>

<h2>Your Profile</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="update_profile" value="1">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required><br>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($profile['ph_no'] ?? $profile['phone']); ?>" required><br>

    <?php if ($user_type == 'seeker'): ?>
        <label>Skills:</label>
        <textarea name="skills"><?php echo htmlspecialchars($profile['skills']); ?></textarea><br>

        <label>Experience:</label>
        <textarea name="experience"><?php echo htmlspecialchars($profile['experience']); ?></textarea><br>

        <label for="profile_pic">Profile Picture (JPG/PNG only):</label>
        <input type="file" name="profile_pic" accept=".jpg, .jpeg, .png"><br>
    <?php else: ?>
        <label>Organization:</label>
        <input type="text" name="organization" value="<?php echo htmlspecialchars($profile['organization']); ?>"><br>
    <?php endif; ?>

    <button type="submit">Update Profile</button>
</form>

<?php if ($user_type == 'seeker'): ?>
    <hr>
    <h3>Profile Picture</h3>
    <?php
    $profile_pic_path = $profile['profile_pic'] ?? '';
    if (!empty($profile_pic_path)) {
        echo "<div style='width:150px; height:150px; overflow:hidden; border-radius:50%;'>
        <img src='$profile_pic_path' alt='Profile Picture' style='width:100%; height:100%; object-fit:cover;'>
      </div>";
    } else {
        echo "No profile picture uploaded.";
    }
    ?>
    <hr>
    <h3>Upload Resume</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="upload_resume" value="1">
        <label for="resume">Upload Resume (PDF only):</label>
        <input type="file" name="resume" accept=".pdf" required><br>
        <button type="submit">Upload Resume</button>
    </form>

    <hr>
    <h3>Uploaded Resume</h3>
    <?php
    $resume_path = $profile['resume'] ?? '';
    if (!empty($resume_path)) {
        echo "<a href='$resume_path' target='_blank'>View Resume</a>";
    } else {
        echo "No resume uploaded.";
    }
    ?>
<?php endif; ?>
