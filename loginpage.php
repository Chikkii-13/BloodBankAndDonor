<?php
session_start();
$loginError = "";
$formSubmitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    include("partialphp/_dbconnect.php");

    $email = strtolower(trim($_POST["email"] ?? ''));
    $password = $_POST["password"] ?? '';

    if (!empty($email) && !empty($password)) {
        

        if ($email === "admin123@gmail.com" && $password === "admin@2006") {
            $_SESSION["loggedin"] = true;
            $_SESSION["role"] = "admin";
            $_SESSION["email"] = $email;
            $_SESSION["username"] = "Administrator";
            header("Location: admin.php");
            exit();
        }

        if ($email === "staff123@gmail.com" && $password === "staff@2006") {
            $_SESSION["loggedin"] = true;
            $_SESSION["role"] = "staff";
            $_SESSION["email"] = $email;
            $_SESSION["username"] = "Staff Member";
            header("Location: staff_dashboard.php");
            exit();
        }
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["loggedin"] = true;
                $_SESSION["email"] = $user["email"];
                $_SESSION["username"] = $user["username"];
                header("Location: index.php");
                exit();

            } else {
                $loginError = "Wrong password.";
            }
        } else {
            $loginError = "Email not found.";
        }

        $stmt->close();
    } else {
        $loginError = "Please enter both email and password.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>
    <link href="css/signup.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="alert">
        <?php if ($formSubmitted && !empty($loginError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($loginError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-wrapper" id="login">
        <div class="container">
            <h3>Login to Your Account</h3>
            <form method="post" action="">
                <div class="form-group mb-3">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" name="email" id="email"
                        placeholder="Enter email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password"
                        placeholder="Enter password" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <a class="login-link mt-3 d-block" href="/bloodbankphp/signup.php">Don't have an account? Sign up</a>
        </div>
    </div>
</body>
</html>
