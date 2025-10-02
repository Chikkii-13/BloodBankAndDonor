<?php
session_start();

$conn = new mysqli("localhost", "root", "", "bloodbank");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Fetch counts
$donorReqCount = $conn->query("SELECT COUNT(*) as total FROM donor_requests")->fetch_assoc()['total'];
$bloodReqCount = $conn->query("SELECT COUNT(*) as total FROM blood_requests")->fetch_assoc()['total'];
$inventoryCount = $conn->query("SELECT COUNT(*) as total FROM inventory")->fetch_assoc()['total'];
$pendingRequests = $donorReqCount + $bloodReqCount;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca&display=swap" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">Blood Bank Staff Panel</div>
        <div class="d-flex align-items-center">
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div id="dashboard-grid">
        <aside class="sidebar">
            <h5 class="sidebar-title">Menu</h5>
            <ul id="menu-list">
                <li><a href="staff_dashboard.php" class="menu-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="donor_requests.php" class="menu-link"><i class="fas fa-hand-holding-heart"></i> Donor Requests</a></li>
                <li><a href="blood_requests.php" class="menu-link"><i class="fas fa-tint"></i> Blood Requests</a></li>
                <li><a href="inventory.php" class="menu-link"><i class="fas fa-boxes"></i> Inventory</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main id="main-content">
            <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>

            <div id="metric-cards">
                <div class="card-feature">
                    <h5>Donor Requests</h5>
                    <p class="count"><?= $donorReqCount ?></p>
                    <a href="donor_requests.php" class="btn-red">View Donors</a>
                </div>

                <div class="card-feature">
                    <h5>Blood Requests</h5>
                    <p class="count"><?= $bloodReqCount ?></p>
                    <a href="blood_requests.php" class="btn-red">View Requests</a>
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

    <!-- Charts -->
    <script>
        const requestsCtx = document.getElementById('requestsChart').getContext('2d');
        const requestsChart = new Chart(requestsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Donor Requests', 'Blood Requests'],
                datasets: [{
                    label: 'Requests',
                    data: [<?= $donorReqCount ?>, <?= $bloodReqCount ?>],
                    backgroundColor: ['#994d51', '#ff6666'],
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
