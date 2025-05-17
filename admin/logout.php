<?php
session_start();

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Start a new session for the message
session_start();
$_SESSION['login_error'] = "You have been logged out successfully";

// Redirect to login page
header("Location: index.php");
exit();
?>