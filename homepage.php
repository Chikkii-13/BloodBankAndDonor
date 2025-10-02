<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "bloodbank");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch blood group inventory
$inventory = [];
$result = $conn->query("SELECT blood_group, units FROM inventory"); // make sure column is 'units'
if($result){
    while($row = $result->fetch_assoc()){
        $inventory[$row['blood_group']] = intval($row['units']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Online Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/homepage.css" />
</head>
<body>

<?php include '_nav.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Donate Blood, Save Lives</h1>
        <p>Join our community to help those in need. Your donation can make a difference.</p>
        <a href="donation_form.php" class="blood-btn">Become a Donor</a>
    </div>
</section>

<section class="brand-window">
    <h1>Blood Group Inventory</h1>
    <div class="blood-groups-grid">
        <?php
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        foreach ($groups as $group) {
            $qty = $inventory[$group] ?? 0;
            $btnClass = $qty > 0 ? 'blood-group-btn available' : 'blood-group-btn disabled';
            echo "<button class='$btnClass' onclick='showGroup(\"$group\")'>
                    $group<br><small>$qty units</small>
                  </button>";
        }
        ?>
    </div>
</section>

<section class="mid-section">
    <div class="mid-container container">
        <div class="mid-text">
            <h2>About Sarvodaya Blood Bank</h2>
            <p>At Sarvodaya Blood Bank, we are committed to providing safe and timely blood supply for hospitals
            and patients. Our donors are heroes who make a real difference in saving lives. We ensure that
            every donation is carefully tested, stored, and distributed efficiently.</p>
            <p>Click on your blood group to see availability or request a donation.</p>
             <a href="about.php" class="about-blood-btn">Read More</a>
        </div>
        <div class="mid-image">
            <img src="images/image-homepage.jpeg" alt="Blood Bank">
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
