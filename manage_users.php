<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "admin"){
    header("Location: loginpage.php");
    exit();
}

$conn = new mysqli("localhost","root","","users");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$message = "";

if(isset($_POST['add_user'])){
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email or username already exists
    $check = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
    if($check->num_rows > 0){
        $message = "<div class='alert alert-warning'>Username or Email already exists.</div>";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username','$email','$password')";
        if($conn->query($sql)){
            header("Location: manage_users.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: manage_users.php");
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
#container { max-width: 1200px; margin: 30px auto; }
h2 { color: #b71c1c; text-align: center; margin-bottom: 30px; }
.table th, .table td { vertical-align: middle; }
.btn-group { display: flex; gap: 5px; }
</style>
</head>
<body>
<div id="container">
<h2>Manage Users</h2>

<?php if($message) echo $message; ?>

<form method="post" class="mb-4">
    <div class="row g-2">
        <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
        <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <div class="col-md-3"><button type="submit" name="add_user" class="btn btn-success w-100">Add User</button></div>
    </div>
</form>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td class="btn-group">
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</body>
</html>
