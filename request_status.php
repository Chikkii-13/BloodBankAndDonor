<?php
$conn = new mysqli("localhost","root","","bloodbank");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Handle approve/reject
if(isset($_POST['action'], $_POST['id'])){
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    $req = $conn->query("SELECT * FROM blood_requests WHERE id=$id")->fetch_assoc();
    if($req && (is_null($req['status']) || strtolower(trim($req['status']))=='pending')){
        $bloodGroup = $conn->real_escape_string(trim($req['blood_group']));
        $unitsRequested = (int)$req['units'];

        $inv = $conn->query("SELECT id, units FROM inventory WHERE blood_group='$bloodGroup'")->fetch_assoc();
        $availableUnits = $inv ? (int)$inv['units'] : 0;
        $inventoryId = $inv ? (int)$inv['id'] : 0;

        if($action=='approve'){
            if($availableUnits >= $unitsRequested){
                $conn->query("UPDATE inventory SET units=units-$unitsRequested WHERE id=$inventoryId");
                $conn->query("UPDATE blood_requests SET status='approved' WHERE id=$id");
            } else {
                $alert = "Not enough units. Request remains pending.";
            }
        }

        if($action=='reject'){
            $conn->query("UPDATE blood_requests SET status='rejected' WHERE id=$id");
        }

        header("Location: request_status.php");
        exit();
    }
}

// Fetch requests
$results = $conn->query("SELECT * FROM blood_requests ORDER BY request_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blood Requests Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/donation_status.css"> <!-- reuse the same CSS -->
</head>
<body>
<div class="page-container">
<?php include '_nav.php'; ?>

<div class="container mt-4">
    <h2>Blood Requests Status</h2>
    <?php if(isset($alert)) echo "<div class='alert alert-warning'>$alert</div>"; ?>
    <div class="table-responsive table-wrapper mt-3">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Request Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($results->num_rows>0): ?>
                    <?php while($row=$results->fetch_assoc()):
                        $status_clean = strtolower(trim($row['status'] ?? 'pending'));
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['blood_group']) ?></td>
                        <td><?= intval($row['units']) ?></td>
                        <td><?= date("d-m-Y H:i",strtotime($row['request_date'])) ?></td>
                        <td class="status-text <?= 'status-'.$status_clean ?>"><?= ucfirst($status_clean) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
