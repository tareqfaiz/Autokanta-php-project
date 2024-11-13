<?php
include("database.php");
session_start(); // Start the session at the beginning of the script
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Search</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to an external CSS file -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }

        header, footer {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 600px;
            margin: 30px auto; /* Center the container */
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h4 {
            color: #333;
            text-align: center; /* Center align the heading */
            margin: 0 0 20px 0; /* Add bottom margin for spacing */
            font-size: 24px; /* Increase font size */
            padding: 15px; /* Add padding for better appearance */
            background-color: #007BFF; /* Match header color */
            color: white; /* Change text color to white */
            border-radius: 5px; /* Round edges */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center align items */
        }

        input {
            margin: 10px 0;
            padding: 10px;
            width: 90%; /* Make input fields responsive */
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #007BFF; /* Change border color on focus */
            outline: none;
        }

        input[type="submit"] {
            background-color: #28A745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            width: 90%;
            max-width: 300px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .result {
            margin-top: 20px;
        }

        .result div {
            margin-bottom: 10px;
            background-color: #e9ecef;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<?php include("header.php"); ?> <!-- Include the header -->

<div class="container">
    <h4>Search for a Car</h4>
    <form action="carsearch.php" method="post">
        <input type="text" name="licence_number" placeholder="License Number" /><br> <!-- License Number is not required -->
        <input type="text" name="color" placeholder="Color" /><br>
        <input type="text" name="model" placeholder="Model" /><br>
        <input type="text" name="socnumber" placeholder="Owner's Social Security Number" /><br>
        <input type="text" name="owner_name" placeholder="Owner's Name" /><br>
        <input type="text" name="username" placeholder="Username" /><br>
        <input type="text" name="email" placeholder="Email" /><br>
        <input type="submit" name="search" value="Search" /><br>
    </form>

    <?php
    // Check if the form has been submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Retrieve and sanitize input values
        $licence_number = !empty($_POST['licence_number']) ? $conn->real_escape_string(trim($_POST['licence_number'])) : null;
        $color = !empty($_POST['color']) ? $conn->real_escape_string(trim($_POST['color'])) : null;
        $model = !empty($_POST['model']) ? $conn->real_escape_string(trim($_POST['model'])) : null;
        $socnumber = !empty($_POST['socnumber']) ? $conn->real_escape_string(trim($_POST['socnumber'])) : null;
        $owner_name = !empty($_POST['owner_name']) ? $conn->real_escape_string(trim($_POST['owner_name'])) : null;
        $username = !empty($_POST['username']) ? $conn->real_escape_string(trim($_POST['username'])) : null;
        $email = !empty($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : null;

        // Prepare the SQL search query
        $sql = "SELECT car.*, person.name AS owner_name, users.username, users.email, person.socnumber AS owner_ssn 
                FROM car 
                LEFT JOIN person ON car.owner = person.socnumber 
                LEFT JOIN users ON person.user_id = users.id 
                WHERE 1=1"; // Base where clause

        // Append conditions based on filled-out search fields
        if ($licence_number) {
            $sql .= " AND car.licnumber = '$licence_number'";
        }
        if ($color) {
            $sql .= " AND car.color = '$color'";
        }
        if ($model) {
            $sql .= " AND car.model = '$model'";
        }
        if ($socnumber) {
            $sql .= " AND car.owner = '$socnumber'";
        }
        if ($owner_name) {
            $sql .= " AND person.name LIKE '%$owner_name%'";
        }
        if ($username) {
            $sql .= " AND users.username LIKE '%$username%'";
        }
        if ($email) {
            $sql .= " AND users.email LIKE '%$email%'";
        }

        // Execute the query
        $result = $conn->query($sql);

        echo '<div class="result">'; // Start results container

        // Check if there are results
        if ($result && $result->num_rows > 0) {
            // Output data for each row
            while ($row = $result->fetch_assoc()) {
                echo "<div>";
                echo "<strong>License Number:</strong> " . htmlspecialchars($row["licnumber"]) . "<br>";
                echo "<strong>Color:</strong> " . htmlspecialchars($row["color"]) . "<br>";
                echo "<strong>Model:</strong> " . htmlspecialchars($row["model"]) . "<br>";
                echo "<strong>Owner's Name:</strong> " . htmlspecialchars($row["owner_name"]) . "<br>";
                echo "<strong>Username:</strong> " . htmlspecialchars($row["username"]) . "<br>";
                echo "<strong>Email:</strong> " . htmlspecialchars($row["email"]) . "<br>";

                // Check user role and owners' SSN visibility
                if ($_SESSION['role'] == 'admin' || $_SESSION['user_id'] == $row["user_id"]) {
                    echo "<strong>Owner's Social Security Number:</strong> " . htmlspecialchars($row["owner_ssn"]) . "<br>";
                } else {
                    echo "<strong>Owner's Social Security Number:</strong> [Hidden for privacy]<br>";
                }
                echo "</div>";
            }
        } else {
            echo "<div class='error'>No results found for the given criteria.</div>";
        }

        echo '</div>'; // End results container
    }

    $conn->close(); // Close the connection
    ?>
</div>

<?php include("footer.php"); ?> <!-- Include the footer -->

</body>
</html>