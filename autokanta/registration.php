<?php
include("database.php");

$message = ''; // Initialize a message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and trim input data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash the password

    // Step 1: Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
    } else {
        // Step 2: Check for existing username or email
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmtCheck->bind_param("ss", $username, $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        
        if ($result->num_rows > 0) {
            $message = "Username or email already exists!";
        } else {
            // Step 3: Insert the user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $password);
            
            if ($stmt->execute()) {
                $message = "Registration successful!";
                // Optionally, you could redirect to login page or other page after successful registration
                // header("Location: login.php");
                // exit;
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmtCheck->close(); // Close check statement
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #0056b3;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        nav {
            background-color: #007bff;
            padding: 10px;
            display: flex;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        nav a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            margin: 0 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav a:hover {
            background-color: #0056b3;
        }
        .container {
            max-width: 400px; /* Container width */
            margin: 30px auto; /* Center the container */
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .message {
            color: #ff0000; /* Style for error messages */
            text-align: center;
        }
        h4 {
            color: #333;
            text-align: center;
        }
        input {
            margin: 10px 0;  /* Reduced margin */
            padding: 10px;
            width: calc(100% - 4px); /* Reduced width calculation to fit parent */
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding in width */
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%; /* Full width for the button */
            padding: 12px; /* Increased button padding for larger size */
            margin: 10px 0; /* Reduced margin */
            border-radius: 4px; /* Match radius with input boxes */
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<?php include("header.php"); ?>

<div class="container">
    <h4>Register</h4>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Username" required autocomplete="off"/>
        <br>
        <input type="email" name="email" placeholder="Email" required/>
        <br>
        <input type="password" name="password" placeholder="Password" required autocomplete="new-password"/>
        <br>
        <input type="submit" value="Register"/>
    </form>
</div>

<?php include("footer.php"); ?>

</body>
</html>