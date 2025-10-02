<?php
session_start();
if(!isset($_SESSION["loggedin"]) || ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "staff")){
    header("Location: loginpage.php");
    exit();
}

$conn = new mysqli("localhost","root","","bloodbank");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Handle manual Approve/Reject
if(isset($_GET['action'], $_GET['id'])){
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    $req = $conn->query("SELECT * FROM blood_requests WHERE id=$id")->fetch_assoc();
    if(!$req) { header("Location: blood_requests.php"); exit(); }

    $bloodGroup = $conn->real_escape_string($req['blood_group']);
    $unitsRequested = (int)$req['units'];

    if($action === 'approve'){
        $inv = $conn->query("SELECT * FROM inventory WHERE blood_group='$bloodGroup'")->fetch_assoc();
        $availableUnits = $inv ? (int)$inv['units'] : 0;

        if($availableUnits >= $unitsRequested){
            $conn->query("UPDATE inventory SET units=units-$unitsRequested WHERE blood_group='$bloodGroup'");
            $status = 'approved';
        } else {
            $status = 'pending';
            $alert = "Cannot approve request ID $id: not enough units in inventory.";
        }
    } elseif($action === 'reject'){
        $status = 'rejected';
    } else {
        $status = 'pending';
    }

    $stmt = $conn->prepare("UPDATE blood_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: blood_requests.php");
    exit();
}

// Fetch all requests
$results = $conn->query("SELECT * FROM blood_requests ORDER BY request_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Blood Requests</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.table th, .table td{text-align:center; vertical-align:middle;}
.status-approved{color:green; font-weight:bold;}
.status-rejected{color:red; font-weight:bold;}
.status-pending{color:orange; font-weight:bold;}
</style>
</head>
<body>
<div class="container mt-4">
<h2>Blood Requests</h2>
<?php if(isset($alert)) echo "<div class='alert alert-warning'>$alert</div>"; ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Name / Entity</th>
<th>Blood Group</th>
<th>Requested Units</th>
<th>Request Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($row = $results->fetch_assoc()): 
$status_clean = strtolower(trim($row['status'] ?? 'pending')); ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name'] ?: $row['entity_name']) ?></td>
<td><?= htmlspecialchars($row['blood_group']) ?></td>
<td><?= intval($row['units']) ?></td>
<td><?= date("d-m-Y H:i", strtotime($row['request_date'])) ?></td>
<td class="status-text <?= 'status-'.$status_clean ?>"><?= ucfirst($status_clean) ?></td>
<td>
<?php if($status_clean === 'pending'): ?>
<a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
<a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
<?php elseif($status_clean === 'approved'): ?>
<span class="btn btn-success btn-sm disabled">Approved</span>
<?php else: ?>
<span class="btn btn-danger btn-sm disabled">Rejected</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</body>
</html>
