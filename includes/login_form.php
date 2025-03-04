<form method="POST" action="index.php">
    <label for="seeker_id">ID:</label>
    <input type="text" name="seeker_id" required><br>

    <label for="user_type">User Type:</label>
    <select name="user_type">
        <option value="seeker">Job Seeker</option>
        <option value="employer">Employer</option>
    </select><br>

    <button type="submit">Login</button>
</form>
