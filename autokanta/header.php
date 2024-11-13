<?php
// Check if a session is already active before starting one
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if none exists
}

// Function to get formatted date and time
function getCurrentDateTime() {
    return date('l, F j, Y - h:i A'); // Example format: Monday, January 1, 2023 - 02:30 PM
}

$current_time = getCurrentDateTime(); // Get current date and time

// Automatically redirect logged-in users or admins to their respective pages
$current_page = basename($_SERVER['PHP_SELF']);
if (isset($_SESSION['username'])) {
    if ($current_page === 'login.php') {
        header("Location: user_info.php"); // Redirect to User Info page if user is already logged in
        exit();
    }

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $current_page === 'login.php') {
        header("Location: admin.php"); // Redirect Admin to admin page
        exit();
    }
}

// CSS Styles
?>
<style>
    header {
        background-color: #0056b3;
        color: #fff;
        text-align: center;
        padding: 20px;
    }
    img.logo {
        width: 50px; /* logo size */
    }
    nav {
        background-color: #007bff;
        padding: 10px;
        display: flex;
        justify-content: center; /* Center the nav items */
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }
    nav a {
        color: white;
        padding: 14px 20px;
        text-decoration: none;
        text-align: center;
        margin: 0 10px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    nav a:hover {
        background-color: #0056b3;
    }
</style>

<header>
    <img src="./autokanta.jpg" alt="Autokanta Logo" class="logo">
    <h1>Welcome to Autokanta</h1>

    <?php if (isset($_SESSION['username'])): ?>
        <p>
            Hello <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : htmlspecialchars($_SESSION['username']); ?>!
        </p>
    <?php else: ?>
        <p>Hello Guest!</p>
    <?php endif; ?>

    <p>Current Date and Time: <?php echo $current_time; ?></p>
</header>

<nav>
    <?php if ($current_page !== 'index.php'): ?>
        <a href="index.php">Home</a>
    <?php endif; ?>

    <?php if (!isset($_SESSION['username'])): ?>
        <?php if ($current_page !== 'login.php'): ?>
            <a href="login.php">Login</a>
        <?php endif; ?>

        <?php if ($current_page !== 'registration.php'): ?>
            <a href="registration.php">Sign Up</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="user_info.php">User Info</a>
        <a href="carsearch.php">Car Search</a>
        <a href="logout.php">Logout</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php">Admin</a>
            <a href="admin_approvals.php">Admin Approvals</a> <!-- Added link for admin approvals -->
        <?php endif; ?>
    <?php endif; ?>
</nav>