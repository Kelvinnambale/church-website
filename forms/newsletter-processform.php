<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "changochurch_db";

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect and sanitize form data
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    
    // Basic validation
    if (empty($email)) {
        $response['message'] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please enter a valid email address.";
    } else {
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            
            // Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO newsletter_subscriptions (email, subscription_date) 
                                   VALUES (:email, NOW())");
            
            // Bind parameters
            $stmt->bindParam(':email', $email);
            
            // Execute the statement
            $stmt->execute();
            
            // Set success message
            $response['success'] = true;
            $response['message'] = "Thank you for subscribing to our newsletter!";
            
        } catch(PDOException $e) {
            $response['message'] = "Sorry, there was an error processing your subscription. Please try again later.";
            // For debugging (remove in production):
            // error_log("Database Error: " . $e->getMessage());
        }
        
        // Close connection
        $conn = null;
    }
}

// Handle the response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // If AJAX request, return JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // If regular form submission, redirect with message
    if ($response['success']) {
        // Redirect with success parameter
        header("Location: ../index.html?newsletter=success");
    } else {
        // Redirect with error parameter
        header("Location: ../index.html?newsletter=error&msg=" . urlencode($response['message']));
    }
    exit;
}
?>
