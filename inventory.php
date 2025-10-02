<?php
session_start();
if(!isset($_SESSION["loggedin"]) || ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "staff")){
    header("Location: loginpage.php");
    exit();
}

$conn = new mysqli("localhost","root","","bloodbank");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);


if(isset($_POST['add_blood'])){
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $units = intval($_POST['units']);
    $conn->query("INSERT INTO inventory (blood_group, units) VALUES ('$blood_group', $units)");
    header("Location: inventory.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM inventory WHERE id=$id");
    header("Location: inventory.php");
    exit();
}

if(isset($_POST['update_blood'])){
    $id = intval($_POST['id']);
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $units = intval($_POST['units']);
    $conn->query("UPDATE inventory SET blood_group='$blood_group', units=$units, date_added=NOW() WHERE id=$id");
    header("Location: inventory.php");
    exit();
}

$result = $conn->query("SELECT * FROM inventory ORDER BY date_added DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Inventory Management</h2>

<!-- Add Blood Form -->
<form method="post" class="mb-4 row g-2">
    <div class="col-md-3">
        <input type="text" name="blood_group" class="form-control" placeholder="Blood Group" required>
    </div>
    <div class="col-md-3">
        <input type="number" name="units" class="form-control" placeholder="Units" required>
    </div>
    <div class="col-md-3">
        <button type="submit" name="add_blood" class="btn btn-success">Add Blood</button>
    </div>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Blood Group</th>
            <th>Units</th>
            <th>Date Added</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()){ ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['blood_group']) ?></td>
            <td><?= intval($row['units']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($row['date_added'])) ?></td>
            <td>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this blood?')">Delete</a>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="post" class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Update Blood</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <div class="mb-3">
                            <label>Blood Group</label>
                            <input type="text" name="blood_group" class="form-control" value="<?= htmlspecialchars($row['blood_group']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Units</label>
                            <input type="number" name="units" class="form-control" value="<?= intval($row['units']) ?>" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" name="update_blood" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </form>
                  </div>
                </div>

            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
