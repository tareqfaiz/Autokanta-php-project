<?php
session_start();
include("database.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users with their details
$resultUsers = $conn->query("
    SELECT users.id, users.username, users.email, person.address, person.phone 
    FROM users 
    LEFT JOIN person ON users.id = person.user_id
");

$resultCars = $conn->query("SELECT * FROM car");

// Delete user
if (isset($_GET['delete_user'])) {
    $userId = (int)$_GET['delete_user']; // Sanitize input
    $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmtDeleteUser->bind_param("i", $userId);
    $stmtDeleteUser->execute();
    $stmtDeleteUser->close();
    header("Location: admin.php"); // Redirect after delete
    exit;
}

// Get user details for editing
$userToEdit = null;
if (isset($_GET['edit_user'])) {
    $userId = (int)$_GET['edit_user']; // Sanitize input
    $stmtEditUser = $conn->prepare("
        SELECT u.id, u.username, u.email, p.address, p.phone 
        FROM users u 
        LEFT JOIN person p ON u.id = p.user_id 
        WHERE u.id = ?
    ");
    $stmtEditUser->bind_param("i", $userId);
    $stmtEditUser->execute();
    $resultEdit = $stmtEditUser->get_result();
    if ($resultEdit->num_rows > 0) {
        $userToEdit = $resultEdit->fetch_assoc();
    }
    $stmtEditUser->close();
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $userId = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    // Update the person table
    $stmtUpdatePerson = $conn->prepare("UPDATE person SET address = ?, phone = ? WHERE user_id = ?");
    $stmtUpdatePerson->bind_param("ssi", $address, $phone, $userId);
    $stmtUpdatePerson->execute();

    // Update the users table
    $stmtUpdateUser = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmtUpdateUser->bind_param("ssi", $username, $email, $userId);
    $stmtUpdateUser->execute();

    $stmtUpdatePerson->close();
    $stmtUpdateUser->close();
    
    header("Location: admin.php"); // Redirect after update
    exit;
}

// Handle fine submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_fine'])) {
    $userId = intval($_POST['user_id']);
    $licnumber = $_POST['licnumber'];
    $fineAmount = floatval($_POST['fine_amount']);
    $fineReason = $_POST['fine_reason'];
    $fineDate = $_POST['fine_date'];

    // Insert the fine into the database
    $insertFineQuery = "INSERT INTO fine (user_id, licnumber, fine_amount, fine_reason, fine_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertFineQuery);
    $stmt->bind_param("issss", $userId, $licnumber, $fineAmount, $fineReason, $fineDate);
    
    if ($stmt->execute()) {
        $fineMessage = "Fine added successfully!";
    } else {
        $fineMessage = "Error adding fine: " . $stmt->error;
    }
    
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        /* CSS Reset */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f3f4f6;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007BFF;
            margin-bottom: 20px;
            font-size: 1.5em;
            text-align: center;
            font-weight: bold;
            border-bottom: 3px solid #007BFF;
            padding-bottom: 10px;
        }

        h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button, .cancel-button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .cancel-button {
            background-color: #dc3545; /* Bootstrap danger color */
            margin-left: 5px;
        }

        .cancel-button:hover {
            background-color: #c82333; /* Darker shade for hover */
        }

        .message {
            color: #28a745; /* Bootstrap success color */
            margin-bottom: 20px;
        }

        .error {
            color: #dc3545; /* Bootstrap danger color */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include("header.php"); ?> <!-- Include header -->

<div class="container">
    <h1>Admin Dashboard</h1> <!-- Clear heading for admin dashboard -->

    <h4>Manage Users</h4> <!-- Enhanced heading for Users section -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($userRow = $resultUsers->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($userRow['id']); ?></td>
                <td><?php echo htmlspecialchars($userRow['username']); ?></td>
                <td><?php echo htmlspecialchars($userRow['email']); ?></td>
                <td><?php echo htmlspecialchars($userRow['address']); ?></td>
                <td><?php echo htmlspecialchars($userRow['phone']); ?></td>
                <td>
                    <a href="?edit_user=<?php echo $userRow['id']; ?>">Edit</a>
                    <a href="?delete_user=<?php echo $userRow['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($userToEdit): ?>
        <h4>Edit User</h4>
        <form method="POST" action="">
            <input type="hidden" name="user_id" value="<?php echo $userToEdit['id']; ?>">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($userToEdit['username']); ?>" required>
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($userToEdit['email']); ?>" required>
            <label>Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($userToEdit['address']); ?>" required>
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($userToEdit['phone']); ?>" required>
            <button type="submit" name="update_user">Update User</button>
            <a href="admin.php" class="cancel-button" style="display: inline-block;">Cancel</a> <!-- Cancel button -->
        </form>
    <?php endif; ?>

    <h4>Manage Cars</h4>  <!-- Enhanced heading for Cars section -->
    <table>
        <thead>
            <tr>
                <th>License Number</th>
                <th>Color</th>
                <th>Model</th>
                <th>Owner</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($carRow = $resultCars->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($carRow['licnumber']); ?></td>
                <td><?php echo htmlspecialchars($carRow['color']); ?></td>
                <td><?php echo htmlspecialchars($carRow['model']); ?></td>
                <td><?php echo htmlspecialchars($carRow['owner']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4>Add Fine</h4>
    <?php if (isset($fineMessage)): ?>
        <div class="message"><?php echo htmlspecialchars($fineMessage); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="user_id">Select User:</label>
        <select name="user_id" id="user_id" required>
            <option value="">Select a user</option>
            <?php
            // Reset the users result pointer
            $resultUsers->data_seek(0);
            while ($userRow = $resultUsers->fetch_assoc()): ?>
                <option value="<?php echo $userRow['id']; ?>"><?php echo htmlspecialchars($userRow['username']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="licnumber">Select License Number:</label>
        <select name="licnumber" id="licnumber" required>
            <option value="">Select a license number</option>
            <?php
            // Reset the cars result pointer
            $resultCars->data_seek(0);
            while ($carRow = $resultCars->fetch_assoc()): ?>
                <option value="<?php echo $carRow['licnumber']; ?>"><?php echo htmlspecialchars($carRow['licnumber']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="fine_amount">Fine Amount:</label>
        <input type="number" step="0.01" name="fine_amount" id="fine_amount" required>

        <label for="fine_reason">Fine Reason:</label>
        <input type="text" name="fine_reason" id="fine_reason" required>

        <label for="fine_date">Fine Date:</label>
        <input type="date" name="fine_date" id="fine_date" required>

        <button type="submit" name="submit_fine">Add Fine</button>
    </form>
</div>

<?php include("footer.php"); ?> <!-- Include footer -->

</body>
</html>