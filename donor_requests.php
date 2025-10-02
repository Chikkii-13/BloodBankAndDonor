<?php
$conn = new mysqli("localhost", "root", "", "bloodbank");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle AJAX Approve/Reject
if(isset($_POST['action'], $_POST['id'])){
    header('Content-Type: application/json');
    $id = (int)$_POST['id'];
    $status = ($_POST['action'] === 'approve') ? 'accepted' : 'rejected';

    $stmt = $conn->prepare("UPDATE donor_requests SET status_donor=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success"=>true, "status"=>$status]);
    exit();
}

// Fetch donor requests
$result = $conn->query("
    SELECT id, donorName, email, phone, dob, blood_group, last_donation, schedule_date, units,
           IFNULL(status_donor,'pending') as status
    FROM donor_requests
    ORDER BY schedule_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donor Requests Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
.badge { font-size: 0.9em; }
</style>
</head>
<body>
<div class="container mt-4">
    <h2>Donor Requests</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>DOB</th>
                    <th>Blood Group</th>
                    <th>Last Donation</th>
                    <th>Schedule Date</th>
                    <th>Units</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr id="row-<?= $row['id'] ?>">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['donorName']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= $row['dob'] ?></td>
                        <td><?= htmlspecialchars($row['blood_group']) ?></td>
                        <td><?= $row['last_donation'] ?: 'N/A' ?></td>
                        <td><?= $row['schedule_date'] ?></td>
                        <td><?= $row['units'] ?></td>
                        <td class="status-badge">
                            <?php
                                $badgeClass = match($row['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'accepted' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td class="action-cell">
                            <?php if($row['status']=='pending'): ?>
                                <button class="btn btn-success btn-sm action-btn" data-id="<?= $row['id'] ?>" data-action="approve">Accept</button>
                                <button class="btn btn-danger btn-sm action-btn" data-id="<?= $row['id'] ?>" data-action="reject">Reject</button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11" class="text-center">No donor requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function(){
    $(".action-btn").click(function(){
        var id = $(this).data("id");
        var action = $(this).data("action");
        var row = $("#row-"+id);

        $.ajax({
            url: "",
            type: "POST",
            data: {id:id, action:action},
            dataType: "json",
            success: function(resp){
                if(resp.success){
                    // Update status badge
                    var badgeClass = (resp.status === 'accepted') ? 'bg-success' : 'bg-danger';
                    row.find(".status-badge .badge").text(resp.status.charAt(0).toUpperCase() + resp.status.slice(1))
                        .removeClass('bg-warning bg-success bg-danger bg-secondary')
                        .addClass(badgeClass);

                    // Remove action buttons
                    row.find(".action-cell").html('-');
                } else {
                    alert("Error updating status");
                }
            },
            error: function(){ alert("AJAX Error"); }
        });
    });
});
</script>
</body>
</html>
