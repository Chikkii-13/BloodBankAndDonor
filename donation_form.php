<?php
$conn = new mysqli("localhost", "root", "", "bloodbank");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donorName = $_POST['donorName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $bloodGroup = $_POST['bloodGroup'];
    $lastDonation = $_POST['lastDonation'] ?: NULL;
    $scheduleDate = $_POST['scheduleDate'];
    $units = (int) $_POST['units'];
    $status = 'pending';

    // Calculate age
    $age = date_diff(date_create($dob), date_create('today'))->y;

    // Validate age, units, and last donation
    if ($age < 18) {
        echo "<script>alert('You must be at least 18 years old to donate blood.');</script>";
    } elseif ($units != 1) {
        echo "<script>alert('You can donate only 1 unit.');</script>";
    } elseif ($lastDonation && (strtotime($scheduleDate) - strtotime($lastDonation)) < (90 * 24 * 60 * 60)) {
        echo "<script>alert('At least 3 months must pass since your last donation.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO donor_requests 
            (donorName, email, phone, gender, dob, blood_group, last_donation, schedule_date, units, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $donorName, $email, $phone, $gender, $dob, $bloodGroup, $lastDonation, $scheduleDate, $units, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Your donation request has been submitted successfully!');</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blood Donation Form</title>
<link href="https://fonts.googleapis.com/css2?family=Lexend+Deca&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/donation_form.css" rel="stylesheet">
</head>
<body>
<div class="container donation-container">
    <div class="card donation-card shadow">
        <div class="card-body">
            <h2 class="text-center mb-4 form-title">🩸 Blood Donation Form</h2>
            <form id="donationForm" class="donation-form" method="post" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="donorName">Full Name</label>
                    <input type="text" id="donorName" name="donorName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" required onkeydown="return false;">
                </div>
                <div class="form-group mb-3">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bloodGroup">Blood Group</label>
                    <select id="bloodGroup" name="bloodGroup" class="form-control" required>
                        <option value="">-- Select Blood Group --</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>O+</option>
                        <option>O-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lastDonation">Last Donation Date</label>
                    <input type="date" id="lastDonation" name="lastDonation" class="form-control" onkeydown="return false;">
                </div>
                <div class="form-group">
                    <label for="scheduleDate">Schedule Donation Date</label>
                    <input type="date" id="scheduleDate" name="scheduleDate" class="form-control" required onkeydown="return false;">
                </div>
                <div class="form-group">
                    <label for="units">Units to Donate</label>
                    <input type="number" id="units" name="units" class="form-control" value="1" min="1" max="1" readonly required>
                </div>
                <button type="submit" class="btn btn-donate w-100">Submit Donation</button>
            </form>
        </div>
        <a href="homepage.php" class="btn">Back to Homepage</a>
    </div>
</div>

<script>
// Name: letters only
document.getElementById('donorName').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g,'');
});

// Phone: digits only
document.getElementById('phone').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
});

// DOB: must be 18+
const dobInput = document.getElementById('dob');
function setMaxDOB() {
    const today = new Date();
    const year = today.getFullYear() - 18;
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    dobInput.max = `${year}-${month}-${day}`;
}
dobInput.addEventListener('change', function() {
    const selected = new Date(this.value);
    const minAge = new Date();
    minAge.setFullYear(minAge.getFullYear() - 18);
    if (selected > minAge) {
        alert("You must be at least 18 years old.");
        this.value = "";
    }
});
setMaxDOB();

// Last Donation & Schedule Donation: at least 3 months gap
const lastDonationInput = document.getElementById('lastDonation');
const scheduleInput = document.getElementById('scheduleDate');
function validateLastDonation() {
    if (lastDonationInput.value && scheduleInput.value) {
        const last = new Date(lastDonationInput.value);
        const schedule = new Date(scheduleInput.value);
        const diffDays = (schedule - last)/(1000*60*60*24);
        if (diffDays < 90) {
            alert("At least 3 months must pass since last donation.");
            scheduleInput.value = "";
        }
    }
}
lastDonationInput.addEventListener('change', validateLastDonation);
scheduleInput.addEventListener('change', validateLastDonation);

// Form validation before submit
function validateForm() {
    // Age check
    const dob = new Date(dobInput.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff===0 && today.getDate() < dob.getDate())) age--;
    if (age < 18) { alert("You must be at least 18 years old."); return false; }

    // Last donation check
    validateLastDonation();
    return true;
}
</script>
</body>
</html>
