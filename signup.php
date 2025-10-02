<?php
$showAlert = false;
$showError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("partialphp/_dbconnect.php");

    $name = trim($_POST["name"]);
    $bloodtype = $_POST["bloodtype"];
    $phone = $_POST["phone"];
    $email = strtolower(trim($_POST["email"]));
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];

    if (!preg_match('/^[A-Za-z0-9\s]+$/', $name)) {
        $showError = "Name should contain only letters and numbers.";
    } elseif ($password !== $cpassword) {
        $showError = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $showError = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO users (name, bloodtype, phone, email, password, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $stmt->bind_param("sssss", $name, $bloodtype, $phone, $email, $hash);

            if ($stmt->execute()) {
                $showAlert = true;
            } else {
                $showError = "Database error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signup Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/signup.css" rel="stylesheet">
</head>

<body>
    <div class="alert">
        <?php if ($showAlert): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your account has been created. You can now <a href="/bloodbankphp/loginpage.php" class="alert-link">log in</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($showError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($showError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-wrapper">
        <div class="container">
            <h3>Create an Account</h3>
            <form method="post">
                <div class="form-group mb-3">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" placeholder="Enter your name" name="name" required>
                </div>

                <div class="form-group mb-3">
                    <label for="bloodtype">Blood Type</label>
                    <select class="form-control" name="bloodtype" required>
                        <option value="" class="placeholder">Select</option>
                        <option>A+</option><option>A-</option>
                        <option>B+</option><option>B-</option>
                        <option>O+</option><option>O-</option>
                        <option>AB+</option><option>AB-</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="phone">Phone Number</label>
                    <input type="tel" class="form-control" placeholder="Enter your phone number" name="phone" required>
                </div>
 
                <div class="form-group mb-3">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" placeholder="Enter your email" name="email" required>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" placeholder="Create a password" name="password" required>
                </div>

                <div class="form-group mb-3">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" class="form-control" placeholder="Confirm your password" name="cpassword" required>
                </div>

                <button type="submit" class="btn btn-primary mt-2">Sign Up</button>
            </form>

            <a class="login-div mt-3 d-block" href="/bloodbankphp/loginpage.php">Already have an account? Sign In</a>
        </div>
    </div>
</body>
</html>
