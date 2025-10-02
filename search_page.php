<?php
$conn = new mysqli("localhost", "root", "", "bloodbank");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// Fetch total units per blood group
$inventory = [];
$stmt = $conn->prepare("SELECT blood_group, SUM(units) as total_units FROM inventory GROUP BY blood_group");
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $inventory[$row['blood_group']] = intval($row['total_units']);
}
$stmt->close();

// If user searches, filter to that blood group only
$searchGroup = $_GET['blood_group'] ?? '';
$results = [];
if($searchGroup !== ''){
    if(isset($inventory[$searchGroup])){
        $results[] = ['blood_group'=>$searchGroup, 'units'=>$inventory[$searchGroup]];
    }
} else {
    // Show all blood groups by default
    foreach($groups as $group){
        $results[] = ['blood_group'=>$group, 'units'=>$inventory[$group] ?? 0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Search Blood</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}
.page-container {
    flex: 1;
}
.badge-available { background-color: #28a745; }
.badge-unavailable { background-color: #dc3545; }
footer {
    background-color: #343a40;
    color: white;
    padding: 15px 0;
    text-align: center;
}
</style>
</head>
<body>

<div class="page-container">
    <?php include '_nav.php'; ?>

    <div class="container my-5">
        <h2 class="text-center mb-4">🩸 Blood Availability</h2>

        <form class="row g-3 justify-content-center mb-4" method="get">
            <div class="col-auto">
                <select name="blood_group" class="form-select">
                    <option value="">All Blood Groups</option>
                    <?php foreach($groups as $group): ?>
                        <option value="<?= $group ?>" <?= ($searchGroup === $group ? 'selected' : '') ?>><?= $group ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-danger">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Blood Group</th>
                        <th>Available Units</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $row):
                        $available = intval($row['units']) > 0;
                        $statusClass = $available ? 'badge-available' : 'badge-unavailable';
                        $statusText = $available ? 'Available' : 'Not Available';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['blood_group']) ?></td>
                        <td><?= intval($row['units']) ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td>
                            <?php if($available): ?>
                                <a href="request_form.php?blood_group=<?= urlencode($row['blood_group']) ?>" class="btn btn-success btn-sm">Request Blood</a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Request</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
