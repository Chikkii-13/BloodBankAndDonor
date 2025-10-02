<?php
$conn = new mysqli("localhost","root","","bloodbank");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Handle Approve/Reject
if(isset($_GET['action'], $_GET['id'])){
    $id = (int)$_GET['id'];
    $status = ($_GET['action'] === 'approve') ? 'accepted' : 'rejected';

    $stmt = $conn->prepare("UPDATE donor_requests SET status_donor=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: donation_status.php");
    exit();
}

// Fetch all donor requests
$results = $conn->query("SELECT * FROM donor_requests ORDER BY schedule_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Donation Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/donation_status.css">
</head>
<body>
<div class="page-container">
<?php include '_nav.php'; ?>

<div class="container mt-4">
    <h2>Donation Requests</h2>
    <div class="table-responsive table-wrapper mt-3">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Donor Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>DOB</th>
                    <th>Blood Group</th>
                    <th>Last Donation</th>
                    <th>Schedule Date</th>
                    <th>Units</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()): 
                    $status_clean = strtolower(trim($row['status_donor'] ?? 'pending')); ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['donorName'] ?? $row['name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= $row['dob'] ?></td>
                    <td><?= htmlspecialchars($row['blood_group']) ?></td>
                    <td><?= $row['last_donation'] ?: 'N/A' ?></td>
                    <td><?= $row['schedule_date'] ?></td>
                    <td><?= intval($row['units']) ?></td>
                    <td class="status-text <?= 'status-'.$status_clean ?>"><?= ucfirst($status_clean) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
