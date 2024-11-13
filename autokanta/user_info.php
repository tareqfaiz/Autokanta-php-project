<?php
session_start();
include("database.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the user ID from the session
$userId = $_SESSION['user_id'];

// Prepare to fetch user details, personal information, car information, and fines
$query = "SELECT u.username, u.email, p.name, p.address, p.phone, c.licnumber, c.model, c.color 
          FROM users u 
          LEFT JOIN person p ON u.id = p.user_id 
          LEFT JOIN car c ON u.id = c.user_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Initialize variables
$userDetails = null;

if ($result->num_rows > 0) {
    // Fetch the user details
    $userDetails = $result->fetch_assoc();
} else {
    error_log("No user found with ID: " . $userId);
}

$stmt->close();

// Fetch notifications for the logged-in user
$notifications = [];
$notificationQuery = "SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmtNotification = $conn->prepare($notificationQuery);
$stmtNotification->bind_param("i", $userId);
$stmtNotification->execute();
$notificationResult = $stmtNotification->get_result();

while ($row = $notificationResult->fetch_assoc()) {
    $notifications[] = $row;
}

$stmtNotification->close();

// Fetch fines for the user
$fines = [];
$fineQuery = "SELECT fine_amount, fine_reason, fine_date, paid FROM fine WHERE user_id = ?";
$stmtFine = $conn->prepare($fineQuery);
$stmtFine->bind_param("i", $userId);
$stmtFine->execute();
$fineResult = $stmtFine->get_result();

while ($row = $fineResult->fetch_assoc()) {
    $fines[] = $row;
}

$stmtFine->close();

// Initialize message variable
$message = "";

// Function to capitalize address correctly
function capitalizeWords($string) {
    $words = explode(" ", $string);
    foreach ($words as &$word) {
        if (preg_match('/^(\d+)([a-zA-Z]*)$/', $word, $matches)) {
            $word = $matches[1] . ucfirst(strtolower($matches[2]));
        } elseif (ctype_alpha($word[0])) { 
            $word = ucfirst(strtolower($word));
        }
    }
    return implode(" ", $words);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gather form data
    $firstName = capitalizeWords(trim($_POST['first_name']));
    $lastName = capitalizeWords(trim($_POST['last_name']));
    $address = capitalizeWords(trim($_POST['address']));
    $phone = trim($_POST['phone']);
    $licNumber = strtoupper(trim($_POST['licnumber']));
    $carModel = capitalizeWords(trim($_POST['car_model']));
    $carColor = capitalizeWords(trim($_POST['car_color']));

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($address) || empty($phone) || empty($licNumber) || empty($carModel) || empty($carColor)) {
        $message = "All fields are required.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Insert a new request into the update_requests table
            $insertRequestQuery = "INSERT INTO update_requests (user_id, new_first_name, new_last_name, new_address, new_phone, 
                new_licnumber, new_car_model, new_car_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtRequest = $conn->prepare($insertRequestQuery);
            $stmtRequest->bind_param("isssssss", $userId, $firstName, $lastName, $address, $phone, $licNumber, $carModel, $carColor);
            $stmtRequest->execute();
            // Commit transaction
            $conn->commit();
            $message = "Update request submitted successfully! An admin will review your request.";
        } catch (Exception $e) {
            // Roll back on error
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            $message = "Error submitting update request. Please try again later.";
        } finally {
            // Close prepared statements
            $stmtRequest->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Information</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #0056b3;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
            background-color: #e9ecef;
            color: #333;
        }
        .notifications {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f0f8ff; /* Light blue background */
        }
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item:hover {
            background-color: #e9f5ff; /* Light blue hover effect */
        }
        .more-notifications {
            cursor: pointer;
            color: #007BFF;
            text-decoration: underline;
            margin-top: 10px;
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: #e7f3ff; /* Light background for the link */
            border: 1px solid #007BFF; /* Border matching link color */
            transition: background-color 0.3s, color 0.3s;
        }
        .more-notifications:hover {
            background-color: #007BFF;
            color: white; /* Change text color on hover */
        }
        .dropdown {
            display: none;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .fines {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            background-color: #ffffff; /* White background for fines */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .fines table {
            width: 100%;
            border-collapse: collapse;
        }
        .fines th, .fines td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .fines th {
            background-color: #0056b3;
            color: white;
        }
        .fines tr:nth-child(even) {
            background-color: #f9f9f9; /* Light grey background for even rows */
        }
        .fines tr:hover {
            background-color: #e7f3ff; /* Light blue background on hover */
        }
    </style>
</head>
<body>

<?php include("header.php"); ?>

<div class="container">
    <h1>User Information</h1>

    <?php if ($userDetails): ?>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($userDetails['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($userDetails['name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($userDetails['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($userDetails['phone']); ?></p>
        <p><strong>License Number:</strong> <?php echo htmlspecialchars($userDetails['licnumber']); ?></p>
        <p><strong>Car Model:</strong> <?php echo htmlspecialchars($userDetails['model']); ?></p>
        <p><strong>Car Color:</strong> <?php echo htmlspecialchars($userDetails['color']); ?></p>
    <?php else: ?>
        <p>No user details found. Please contact support.</p>
    <?php endif; ?>

    <h2>Your Notifications</h2>
    <?php if (count($notifications) > 0): ?>
        <div class="notifications">
            <?php 
                // Display up to 5 notifications
                $shownNotifications = array_slice($notifications, 0, 5);
                foreach ($shownNotifications as $notification): ?>
                <div class="notification-item">
                    <strong><?php echo htmlspecialchars($notification['created_at']); ?>:</strong> 
                    <?php echo htmlspecialchars($notification['message']); ?>
                </div>
            <?php endforeach; ?>
            <?php if (count($notifications) > 5): ?>
                <span class="more-notifications" onclick="toggleDropdown()">More Notifications</span>
                <div id="moreNotifications" class="dropdown">
                    <?php 
                    // Display remaining notifications
                    $additionalNotifications = array_slice($notifications, 5);
                    foreach ($additionalNotifications as $notification): ?>
                        <div class="notification-item">
                            <strong><?php echo htmlspecialchars($notification['created_at']); ?>:</strong> 
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>No new notifications.</p>
    <?php endif; ?>

    <h2>Your Fines</h2>
    <?php if (count($fines) > 0): ?>
        <div class="fines">
            <table>
                <thead>
                    <tr>
                        <th>Fine Amount</th>
                        <th>Fine Reason</th>
                        <th>Fine Date</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fine['fine_amount']); ?></td>
                            <td><?php echo htmlspecialchars($fine['fine_reason']); ?></td>
                            <td><?php echo htmlspecialchars($fine['fine_date']); ?></td>
                            <td><?php echo $fine['paid'] ? 'Yes' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No fines found for this user.</p>
    <?php endif; ?>

    <h2>Request Information Update</h2>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h3>Update Your Information</h3>
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars(explode(' ', $userDetails['name'])[0]); ?>" required>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars(explode(' ', $userDetails['name'])[1]); ?>" required>
        
        <label>Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($userDetails['address']); ?>" required>
        
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($userDetails['phone']); ?>" required>

        <h3>Car Information</h3>
        <label>License Number:</label>
        <input type="text" name="licnumber" value="<?php echo htmlspecialchars($userDetails['licnumber']); ?>" required>
        
        <label>Car Model:</label>
        <input type="text" name="car_model" value="<?php echo htmlspecialchars($userDetails['model']); ?>" required>
        
        <label>Car Color:</label>
        <input type="text" name="car_color" value="<?php echo htmlspecialchars($userDetails['color']); ?>" required>

        <button type="submit">Submit Update Request</button>
    </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>