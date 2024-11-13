<?php
session_start();
include("database.php");

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the user ID from session
$userId = $_SESSION['user_id'];

// Prepare and execute the query to fetch user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Initialize the $userDetails variable
$userDetails = null;

if ($result->num_rows > 0) {
    // Fetch the user details
    $userDetails = $result->fetch_assoc();
} else {
    error_log("No user found with ID: " . $userId);
}

// Initialize variables for form data and messages
$address = $phone = $firstName = $lastName = $socNumber = $licNumber = $carModel = $carColor = "";
$message = "";

// Function to validate date
function isValidDate($day, $month) {
    if ($month < 1 || $month > 12) return false; // Month must be 01-12
    $daysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; // index 0 is unused
    return ($day >= 1 && $day <= $daysInMonth[$month]);
}

// Function to capitalize words in an address
function capitalizeAddress($address) {
    $address = ucwords(strtolower($address));
    $address = preg_replace_callback('/(?<=\d)([a-zA-Z])/', function ($matches) {
        return strtoupper($matches[1]);
    }, $address);
    return $address;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gathering information from the form
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $socNumber = trim($_POST['socnumber']);  
    $licNumber = trim($_POST['licnumber']);
    $carModel = trim($_POST['car_model']);
    $carColor = trim($_POST['car_color']);

    // Capitalize names
    $firstName = ucwords(strtolower($firstName));
    $lastName = ucwords(strtolower($lastName));
    
    // Capitalize the address
    $address = capitalizeAddress($address);
    
    // Capitalize the first letter of the Social Security Number (if any letter)
    $socNumber = preg_replace_callback('/[A-Za-z]/', function ($matches) {
        return ucfirst(strtolower($matches[0]));
    }, $socNumber);
    
    // Capitalize the first letter of car model and car color
    $carModel = ucfirst(strtolower($carModel));
    $carColor = ucfirst(strtolower($carColor));
    
    // Capitalize all alphabetic characters in the License Number
    $licNumber = strtoupper($licNumber);
    
    // Combine the first and last name
    $fullName = $firstName . " " . $lastName;

    // Validate inputs (basic validation)
    if (empty($firstName) || empty($lastName) || empty($address) || empty($phone) || empty($socNumber) || empty($licNumber) || empty($carModel) || empty($carColor)) {
        $message = "All fields are required.";
    } elseif (!preg_match('/^\d{6}[-\w\W]{0,5}$/', $socNumber)) {
        $message = "Invalid Social Security Number format. Please enter in the format: 200292-195 or similar, allowing max 5 additional characters.";
    } else {
        // Extract day and month from SocNumber
        $day = (int)substr($socNumber, 0, 2);
        $month = (int)substr($socNumber, 2, 2);

        // Validate the date from SocNumber
        if (!isValidDate($day, $month)) {
            $message = "The day-month combination in the Social Security Number is invalid.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            try {
                // Insert into 'person' table
                $insertPersonQuery = "INSERT INTO person (user_id, name, address, phone, socnumber) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertPersonQuery);
                $stmt->bind_param("issss", $userId, $fullName, $address, $phone, $socNumber);
                $stmt->execute();
                $stmt->close();

                // Insert into 'car' table
                $insertCarQuery = "INSERT INTO car (user_id, licnumber, model, color, owner) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertCarQuery);
                $stmt->bind_param("issss", $userId, $licNumber, $carModel, $carColor, $socNumber);  
                $stmt->execute();
                $stmt->close();

                // Commit transaction
                $conn->commit();
                $message = "Information saved successfully!";
            } catch (Exception $e) {
                // Roll back on error
                $conn->rollback();
                error_log("Database error: " . $e->getMessage());
                $message = "Error saving information. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        h1, h2 {
            color: #333;
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
            display: block; /* Make button display as a block */
            margin: auto; /* Center the button */
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
    </style>
</head>
<body>

<?php include("header.php"); ?> <!-- Header Include -->

<div class="container">
    <h1>User Details</h1>

    <?php if ($userDetails): ?>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($userDetails['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
    <?php else: ?>
        <p>No user details found. Please contact support.</p>
    <?php endif; ?>

    <h2>Enter Your Information</h2>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h3>Personal Information</h3>
        <label>First Name:</label>
        <input type="text" name="first_name" required>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" required>
        
        <label>Address:</label>
        <input type="text" name="address" required>
        
        <label>Phone:</label>
        <input type="text" name="phone" required>
        
        <label>Social Security Number:</label>
        <input type="text" name="socnumber" required>

        <h3>Car Information</h3>
        <label>License Number:</label>
        <input type="text" name="licnumber" required>
        
        <label>Car Model:</label>
        <input type="text" name="car_model" required>
        
        <label>Car Color:</label>
        <input type="text" name="car_color" required>

        <button type="submit">Submit All Information</button>
    </form>
</div>

<?php include("footer.php"); ?>

</body>
</html>