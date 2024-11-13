<?php
session_start();
include("database.php");

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Initialize a message variable
$message = "";
$searchQuery = '';

// Handle search requests
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search_query'];
}

// Fetch update requests from the update_requests table
$updateRequests = [];
$query = "SELECT ur.id, ur.user_id, ur.new_first_name, ur.new_last_name, 
                 ur.new_address, ur.new_phone, 
                 ur.new_licnumber, ur.new_car_model, ur.new_car_color, 
                 u.username, u.email 
          FROM update_requests ur 
          JOIN users u ON ur.user_id = u.id 
          WHERE CONCAT(u.username, ' ', ur.new_first_name, ' ', ur.new_last_name) LIKE ?
          ORDER BY ur.created_at DESC";

$stmt = $conn->prepare($query);
$searchTerm = '%' . $searchQuery . '%';
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $updateRequests[] = $row;
}

$stmt->close();

// Handle approve or reject actions via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $requestId = intval($_POST['request_id']);
    $userId = intval($_POST['user_id']);

    // Initialize new data variables
    $newFirstName = $newLastName = $newAddress = $newPhone = $newLicnumber = $newModel = $newColor = null;

    if ($action === 'approve') {
        // Fetch new data from POST for approval
        $newFirstName = $_POST['new_first_name'] ?? '';
        $newLastName = $_POST['new_last_name'] ?? '';
        $newAddress = $_POST['new_address'] ?? '';
        $newPhone = $_POST['new_phone'] ?? '';
        $newLicnumber = $_POST['new_licnumber'] ?? '';
        $newModel = $_POST['new_model'] ?? '';
        $newColor = $_POST['new_color'] ?? '';
    }

    $conn->begin_transaction();

    try {
        if ($action === 'approve') {
            // Update person table
            $updatePersonQuery = "UPDATE person SET name = CONCAT(?, ' ', ?), address = ?, phone = ? WHERE user_id = ?";
            $stmtUpdatePerson = $conn->prepare($updatePersonQuery);
            $stmtUpdatePerson->bind_param("ssssi", $newFirstName, $newLastName, $newAddress, $newPhone, $userId);
            $stmtUpdatePerson->execute();

            if ($stmtUpdatePerson->error) {
                throw new Exception("Error updating person: " . $stmtUpdatePerson->error);
            }
            $stmtUpdatePerson->close();

            // Update car table
            $updateCarQuery = "UPDATE car SET licnumber = ?, model = ?, color = ? WHERE user_id = ?";
            $stmtUpdateCar = $conn->prepare($updateCarQuery);
            $stmtUpdateCar->bind_param("sssi", $newLicnumber, $newModel, $newColor, $userId);
            $stmtUpdateCar->execute();

            if ($stmtUpdateCar->error) {
                throw new Exception("Error updating car: " . $stmtUpdateCar->error);
            }
            $stmtUpdateCar->close();

            // Delete the request after approval
            $deleteRequest = "DELETE FROM update_requests WHERE id = ?";
            $stmtDeleteRequest = $conn->prepare($deleteRequest);
            $stmtDeleteRequest->bind_param("i", $requestId);
            $stmtDeleteRequest->execute();

            if ($stmtDeleteRequest->error) {
                throw new Exception("Error deleting update request: " . $stmtDeleteRequest->error);
            }
            $stmtDeleteRequest->close();

            // Notify user
            $notificationMessage = "Your update request has been approved.";
            $notificationQuery = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $stmtNotification = $conn->prepare($notificationQuery);
            $stmtNotification->bind_param("is", $userId, $notificationMessage);
            $stmtNotification->execute();

            if ($stmtNotification->error) {
                throw new Exception("Error inserting notification: " . $stmtNotification->error);
            }
            $stmtNotification->close();

            // Commit transaction
            $conn->commit();
            echo json_encode(['success' => true]);
            exit; // Exit after sending response
        } elseif ($action === 'reject') {
            $comment = $_POST['reject_comment'] ?? '';

            // Update request with rejection comment
            $updateRequestQuery = "UPDATE update_requests SET comment = ? WHERE id = ?";
            $stmtUpdateRequest = $conn->prepare($updateRequestQuery);
            $stmtUpdateRequest->bind_param("si", $comment, $requestId);
            $stmtUpdateRequest->execute();

            if ($stmtUpdateRequest->error) {
                throw new Exception("Error updating request with rejection comment: " . $stmtUpdateRequest->error);
            }
            $stmtUpdateRequest->close();

            // Notify user of rejection
            $notificationMessage = "Your update request was rejected. Reason: " . $comment;
            $notificationQuery = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $stmtNotification = $conn->prepare($notificationQuery);
            $stmtNotification->bind_param("is", $userId, $notificationMessage);
            $stmtNotification->execute();

            if ($stmtNotification->error) {
                throw new Exception("Error inserting notification for rejection: " . $stmtNotification->error);
            }
            $stmtNotification->close();

            // Commit transaction
            $conn->commit();
            echo json_encode(['success' => true]);
            exit; // Exit after sending response
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error processing request: " . $e->getMessage()]);
        exit; // Exit in case of error
    }
}

// Include header
include("header.php");
?>

<style>
    body {
        margin: 0; 
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    .container {
        max-width: 1200px;
        margin: 20px auto; 
        padding: 20px;
        background-color: #ffffff; 
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        border-radius: 8px; 
    }

    h1, h2 {
        color: #333; 
    }

    .styled-table {
        width: 100%; 
        border-collapse: collapse;
    }

    .styled-table th, .styled-table td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px; 
    }

    .styled-table th {
        background-color: #007BFF;
        color: white;
    }

    .styled-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .approve-button, .reject-button {
        padding: 10px 15px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        color: white; 
        cursor: pointer;
    }

    .approve-button {
        background-color: #28a745; 
    }

    .reject-button {
        background-color: #dc3545; 
    }

    .message {
        margin-bottom: 20px; 
        padding: 10px;
        border-radius: 5px;
        background-color: #f0f8ff;
        border: 1px solid #d1e7dd;
        color: #155724;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-box {
        padding: 10px;
        width: calc(100% - 24px); 
        border: 1px solid #ccc;
        border-radius: 5px;
    }
</style>

<div class="container">
    <h1>Admin Approval Dashboard</h1>

    <?php if ($message): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="search-container">
        <form method="POST">
            <input type="text" name="search_query" placeholder="Search by username or name..." value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-box"/>
            <button type="submit" name="search" class="approve-button">Search</button>
        </form>
    </div>

    <h2>Pending Update Requests</h2>
    <?php if (count($updateRequests) > 0): ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>New First Name</th>
                    <th>New Last Name</th>
                    <th>New Address</th>
                    <th>New Phone</th>
                    <th>New License Number</th>
                    <th>New Car Model</th>
                    <th>New Car Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($updateRequests as $request): ?>
                    <tr id="row_<?php echo $request['id']; ?>">
                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_first_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_last_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_address']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_phone']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_licnumber']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_car_model']); ?></td>
                        <td><?php echo htmlspecialchars($request['new_car_color']); ?></td>
                        <td>
                            <button type="button" onclick="approveRequest(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['new_first_name']); ?>', '<?php echo htmlspecialchars($request['new_last_name']); ?>', '<?php echo htmlspecialchars($request['new_address']); ?>', '<?php echo htmlspecialchars($request['new_phone']); ?>', '<?php echo htmlspecialchars($request['new_licnumber']); ?>', '<?php echo htmlspecialchars($request['new_car_model']); ?>', '<?php echo htmlspecialchars($request['new_car_color']); ?>')" class="approve-button">Approve</button>

                            <button type="button" onclick="document.getElementById('reject_comment_<?php echo $request['id']; ?>').style.display='block';">Reject</button>
                            <div id="reject_comment_<?php echo $request['id']; ?>" style="display:none;">
                                <input type="hidden" id="user_id_<?php echo $request['id']; ?>" value="<?php echo $request['user_id']; ?>">
                                <input type="hidden" id="request_id_<?php echo $request['id']; ?>" value="<?php echo $request['id']; ?>">
                                <input type="text" id="reject_comment_input_<?php echo $request['id']; ?>" placeholder="Rejection reason" required>
                                <button type="button" onclick="rejectRequest(<?php echo $request['id']; ?>)" class="reject-button">Submit Rejection</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No pending update requests.</p>
    <?php endif; ?>
</div>

<script>
function approveRequest(requestId, newFirstName, newLastName, newAddress, newPhone, newLicnumber, newModel, newColor) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "admin_approvals.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                document.getElementById("row_" + requestId).remove();
                alert("Update request approved successfully!");
            } else {
                alert(response.message);
            }
        }
    };
    xhr.send("action=approve&request_id=" + requestId + 
             "&user_id=" + document.getElementById("user_id_" + requestId).value +
             "&new_first_name=" + encodeURIComponent(newFirstName) +
             "&new_last_name=" + encodeURIComponent(newLastName) +
             "&new_address=" + encodeURIComponent(newAddress) +
             "&new_phone=" + encodeURIComponent(newPhone) +
             "&new_licnumber=" + encodeURIComponent(newLicnumber) +
             "&new_model=" + encodeURIComponent(newModel) +
             "&new_color=" + encodeURIComponent(newColor));
}

function rejectRequest(requestId) {
    var comment = document.getElementById("reject_comment_input_" + requestId).value;
    var userId = document.getElementById("user_id_" + requestId).value;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "admin_approvals.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                document.getElementById("row_" + requestId).remove();
                alert("Rejection noted successfully.");
            } else {
                alert(response.message);
            }
        }
    };
    xhr.send("action=reject&request_id=" + requestId + 
             "&user_id=" + userId +
             "&reject_comment=" + encodeURIComponent(comment));
}
</script>

<?php include("footer.php"); ?>