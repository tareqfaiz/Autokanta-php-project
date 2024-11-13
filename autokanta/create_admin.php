<?php
session_start();
include("database.php");

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch update requests from the database
$updateRequests = [];
$query = "SELECT ur.id, ur.user_id, ur.new_first_name, ur.new_last_name, ur.new_address, 
                ur.new_phone, ur.new_socnumber, ur.new_licnumber, ur.new_car_model, 
                ur.new_car_color, u.username, u.email 
          FROM update_requests ur 
          JOIN users u ON ur.user_id = u.id 
          ORDER BY ur.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $updateRequests[] = $row;
    }
}
$stmt->close();

// Handle approval or rejection of requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    // Get action and request ID from the form submission
    $action = $_POST['action'];
    $requestId = intval($_POST['request_id']);
    $userId = intval($_POST['user_id']);

    // Begin a transaction
    $conn->begin_transaction();

    try {
        if ($action === 'approve') {
            // Update personal information
            $updateQuery = "UPDATE person SET name = ?, address = ?, phone = ?, socnumber = ? WHERE user_id = ?";
            $stmtUpdatePerson = $conn->prepare($updateQuery);
            $stmtUpdatePerson->bind_param("ssssi", $_POST['new_full_name'], $_POST['new_address'], $_POST['new_phone'], $_POST['new_socnumber'], $userId);
            $stmtUpdatePerson->execute();
            $stmtUpdatePerson->close();

            // Update car information
            $updateCarQuery = "UPDATE car SET licnumber = ?, model = ?, color = ? WHERE user_id = ?";
            $stmtUpdateCar = $conn->prepare($updateCarQuery);
            $stmtUpdateCar->bind_param("sssi", $_POST['new_licnumber'], $_POST['new_model'], $_POST['new_color'], $userId);
            $stmtUpdateCar->execute();
            $stmtUpdateCar->close();
            
            // Delete the update request
            $deleteQuery = "DELETE FROM update_requests WHERE id = ?";
            $stmtDelete = $conn->prepare($deleteQuery);
            $stmtDelete->bind_param("i", $requestId);
            $stmtDelete->execute();
            $stmtDelete->close();

            $message = "Update request approved successfully.";
        } elseif ($action === 'reject') {
            // Delete the update request directly if rejected
            $deleteQuery = "DELETE FROM update_requests WHERE id = ?";
            $stmtDelete = $conn->prepare($deleteQuery);
            $stmtDelete->bind_param("i", $requestId);
            $stmtDelete->execute();
            $stmtDelete->close();

            $message = "Update request rejected.";
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Roll back on error
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        $message = "Error processing the update request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Approval Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { margin-bottom: 20px; color: green; }
        .error { color: red; }
        button { margin-right: 5px; }
    </style>
</head>
<body>
    <h1>Admin Approval Page</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h2>Pending Update Requests</h2>

    <?php if (count($updateRequests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>New First Name</th>
                    <th>New Last Name</th>
                    <th>New Address</th>
                    <th>New Phone</th>
                    <th>New Social Security Number</th>
                    <th>New License Number</th>
                    <th>New Car Model</th>
                    <th>New Car Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($updateRequests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_first_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_last_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_address']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_phone']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_socnumber']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_licnumber']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_car_model']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_car_color']); ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                <input type="hidden" name="new_full_name" value="<?php echo htmlspecialchars($request['new_first_name'] . ' ' . $request['new_last_name']); ?>">
                                <input type="hidden" name="new_address" value="<?php echo htmlspecialchars($request['new_address']); ?>">
                                <input type="hidden" name="new_phone" value="<?php echo htmlspecialchars($request['new_phone']); ?>">
                                <input type="hidden" name="new_socnumber" value="<?php echo htmlspecialchars($request['new_socnumber']); ?>">
                                <input type="hidden" name="new_licnumber" value="<?php echo htmlspecialchars($request['new_licnumber']); ?>">
                                <input type="hidden" name="new_model" value="<?php echo htmlspecialchars($request['new_car_model']); ?>">
                                <input type="hidden" name="new_color" value="<?php echo htmlspecialchars($request['new_car_color']); ?>">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="reject" style="background-color: #dc3545; color: white;">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No pending update requests at the moment.</p>
    <?php endif; ?>
    
    <a href="logout.php">Logout</a>
</body>
</html>