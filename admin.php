<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "admin") {
    header("Location: loginpage.php");
    exit();
}

$bloodConn = new mysqli("localhost", "root", "", "bloodbank");
if ($bloodConn->connect_error)
    die("Connection failed: " . $bloodConn->connect_error);

$userConn = new mysqli("localhost", "root", "", "users");
if ($userConn->connect_error)
    die("Connection failed: " . $userConn->connect_error);

$userCount = $userConn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$donorReqCount = $bloodConn->query("SELECT COUNT(*) as total FROM donor_requests WHERE status='pending'")->fetch_assoc()['total'];
$bloodReqCount = $bloodConn->query("SELECT COUNT(*) as total FROM blood_requests WHERE status='pending'")->fetch_assoc()['total'];
$pendingRequests = $donorReqCount + $bloodReqCount;
$inventoryCount = $bloodConn->query("SELECT COALESCE(SUM(units),0) as total FROM inventory")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca&display=swap" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">Blood Bank Admin</div>
        <a href="loginpage.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div id="dashboard-grid">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h5 class="sidebar-title">Menu</h5>
            <ul id="menu-list">
                <li><a href="admin.php" class="menu-link active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a href="manage_users.php" class="menu-link"><i class="fas fa-users"></i>Manage Users</a></li>
                <li><a href="donor_requests.php" class="menu-link"><i class="fas fa-hand-holding-heart"></i>Donor
                        Requests</a></li>
                <li><a href="blood_requests.php" class="menu-link"><i class="fas fa-tint"></i>Blood Requests</a></li>
                <li><a href="inventory.php" class="menu-link"><i class="fas fa-boxes"></i>Inventory</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main id="main-content">
            <h2>Welcome, Admin</h2>

            <div id="metric-cards">
                <div class="card-feature">
                    <h5>Total Users</h5>
                    <p class="count"><?= $userCount ?></p>
                    <a href="manage_users.php" class="btn-red">View Users</a>
                </div>

                <div class="card-feature">
                    <h5>Donor Requests</h5>
                    <p class="count"><?= $donorReqCount ?></p>
                    <a href="donor_requests.php" class="btn-red">Check Donor Requests</a>
                </div>

                <div class="card-feature">
                    <h5>Blood Requests</h5>
                    <p class="count"><?= $bloodReqCount ?></p>
                    <a href="blood_requests.php" class="btn-red">Check Blood Requests</a>
                </div>

                <div class="card-feature">
                    <h5>Inventory Items</h5>
                    <p class="count"><?= $inventoryCount ?></p>
                    <a href="inventory.php" class="btn-red">View Inventory</a>
                </div>
            </div>

            <div id="charts">
                <div class="chart-card">
                    <h5>Requests Overview</h5>
                    <canvas id="requestsChart"></canvas>
                </div>

                <div class="chart-card">
                    <h5>Inventory Status</h5>
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    </main>
    </div>
    </div>

    <script>
        const requestsCtx = document.getElementById('requestsChart').getContext('2d');
        const requestsChart = new Chart(requestsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Donor Requests', 'Blood Requests', 'Pending'],
                datasets: [{
                    label: 'Requests',
                    data: [<?= $donorReqCount ?>, <?= $bloodReqCount ?>, <?= $pendingRequests ?>],
                    backgroundColor: ['#994d51', '#ff6666', '#ffb366'],
                    borderWidth: 1
                }]
            },
        });

        const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(inventoryCtx, {
            type: 'bar',
            data: {
                labels: ['Inventory'],
                datasets: [{
                    label: 'Total Units',
                    data: [<?= $inventoryCount ?>],
                    backgroundColor: ['#994d51']
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>

</html>