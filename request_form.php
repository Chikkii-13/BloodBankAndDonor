<?php
$conn = new mysqli("localhost", "root", "", "bloodbank");

// Preselected blood group from search page
$preselectedGroup = $_GET['blood_group'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $dob = $_POST['dob'] ?? NULL;
    $entity_name = $_POST['entity_name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $units = $_POST['units'] ?? 1;

    $stmt = $conn->prepare("INSERT INTO blood_requests (name, dob, entity_name, contact, address, blood_group, units) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $name, $dob, $entity_name, $contact, $address, $blood_group, $units);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Blood Request Submitted Successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blood Request Form</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/donation_form.css" rel="stylesheet">
</head>
<body>
<div class="container donation-container">
    <div class="card donation-card shadow">
        <div class="card-body">
            <h2 class="text-center mb-4 form-title">🩸 Blood Request Form</h2>
            <form method="POST" class="donation-form" onsubmit="return validateForm()">
                <div class="form-group mb-3">
                    <label>Full Name</label>
                    <input type="text" class="form-control donation-input" name="name" id="name" required>
                </div>
                <div class="form-group mb-3">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control donation-input" name="dob" id="dob" required>
                </div>
                <div class="form-group mb-3">
                    <label>Hospital / Blood Bank Name</label>
                    <input type="text" class="form-control donation-input" name="entity_name" id="entity_name" required>
                </div>
                <div class="form-group mb-3">
                    <label>Contact Number</label>
                    <input type="text" class="form-control donation-input" name="contact" id="contact" required>
                </div>
                <div class="form-group mb-3">
                    <label>Delivery Address</label>
                    <textarea class="form-control donation-textarea" name="address" rows="2"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label>Blood Group</label>
                    <select class="form-control donation-select" name="blood_group" required>
                        <option value="">Select</option>
                        <?php
                        $groups = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"];
                        foreach ($groups as $group) {
                            $selected = ($preselectedGroup === $group) ? "selected" : "";
                            echo "<option value='$group' $selected>$group</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label>Units Required</label>
                    <input type="number" class="form-control donation-input" name="units" id="units" min="1" max="20" value="1" required>
                </div>
                <button type="submit" class="btn btn-donate w-100">Submit Request</button>
            </form>
        </div>
        <a href="index.php" class="btn mt-3">Back to Homepage</a>
    </div>
</div>

<script>
    // Name: letters only
    document.getElementById('name').addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-Z\s]/g,'');
    });

    // Hospital / Blood Bank Name: letters only
    document.getElementById('entity_name').addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-Z\s]/g,'');
    });

    // Phone: digits only
    document.getElementById('contact').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g,'');
    });

    // DOB: minimum age 12
    const dobInput = document.getElementById('dob');
    function setMaxDOB() {
        const today = new Date();
        const year = today.getFullYear() - 12;
        const month = String(today.getMonth() + 1).padStart(2,'0');
        const day = String(today.getDate()).padStart(2,'0');
        dobInput.max = `${year}-${month}-${day}`;
    }
    dobInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const minAgeDate = new Date();
        minAgeDate.setFullYear(minAgeDate.getFullYear() - 12);
        if (selectedDate > minAgeDate) {
            alert('Minimum age to request blood is 12 years.');
            this.value = '';
        }
    });
    setMaxDOB();

    // Units max validation
    document.getElementById('units').addEventListener('input', function() {
        if (this.value > 20) this.value = 20;
        if (this.value < 1) this.value = 1;
    });

    function validateForm() {
        return true;
    }
</script>
</body>
</html>
