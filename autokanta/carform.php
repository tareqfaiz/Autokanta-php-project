<?php
include("database.php"); // Make sure this connects to your database

// Initialize variables
$license = $color = $model = $owner = "";
$errorMessage = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Retrieve and sanitize form data
    $license = isset($_POST['license']) ? trim($_POST['license']) : null;
    $color = isset($_POST['color']) ? trim($_POST['color']) : null;
    $model = isset($_POST['model']) ? trim($_POST['model']) : null;
    $owner = isset($_POST['owner']) ? $_POST['owner'] : null;

    // Check if license number is empty
    if (empty($license)) {
        $errorMessage = "License number is required.";
    } else {
        // Check if a car with the same license number already exists
        $stmtCheckCar = $conn->prepare("SELECT * FROM car WHERE licnumber = ?");
        $stmtCheckCar->bind_param("s", $license);
        $stmtCheckCar->execute();
        $resultCheckCar = $stmtCheckCar->get_result();
        
        if ($resultCheckCar->num_rows > 0) {
            $errorMessage = "The license number is already in use.";
        } else {
            // Insert the new car record into database
            $stmtInsertCar = $conn->prepare("INSERT INTO car (licnumber, color, model, owner) VALUES (?, ?, ?, ?)");
            $stmtInsertCar->bind_param("ssss", $license, $color, $model, $owner);

            // Execute and check for success
            if ($stmtInsertCar->execute()) {
                echo "New car added successfully!";
            } else {
                $errorMessage = "Error: " . $stmtInsertCar->error;
            }

            // Close the statement
            $stmtInsertCar->close();
        }

        // Close the checking car statement
        $stmtCheckCar->close();
    }
}

// Fetch owners to populate the dropdown from the person table
$sql = "SELECT socnumber, name FROM person";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add a car</title>
</head>
<body>
    <h4>Input car information:</h4>
    
    <?php
    // Display error messages
    if ($errorMessage) {
        echo "<p style='color: red;'>" . htmlspecialchars($errorMessage) . "</p>";
    }
    ?>

    <form action="carform.php" method="post">
        <input type="text" name="license" placeholder="License number" value="<?php echo htmlspecialchars($license); ?>" required/><br><br>
        <input type="text" name="color" placeholder="Color" value="<?php echo htmlspecialchars($color); ?>" required/><br><br>
        <input type="text" name="model" placeholder="Model (year)" value="<?php echo htmlspecialchars($model); ?>" required/><br><br>
        
        <label>Select Owner:</label><br>
        <select name="owner" required>
            <option value="">Select Owner</option>
            <?php
            // Populate the dropdown with owners
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['socnumber'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
            } else {
                echo "<option value=''>No owners found</option>";
            }
            ?>
        </select><br><br>
        
        <input type="submit" name="add" value="Add car"><br><br>
    </form>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>