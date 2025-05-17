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
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please enter a valid email address.";
    } else {
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            
            // Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message, submission_date) 
                                   VALUES (:name, :email, :phone, :message, NOW())");
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);
            
            // Execute the statement
            $stmt->execute();
            
            // Set success message
            $response['success'] = true;
            $response['message'] = "Thank you for your message! We will get back to you soon.";
            
        } catch(PDOException $e) {
            $response['message'] = "Sorry, there was an error processing your request. Please try again later.";
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
        header("Location: ../ministry/children.html?status=success");
    } else {
        // Redirect with error parameter
        header("Location: ../ministry/children.html?status=error&msg=" . urlencode($response['message']));
    }
    exit;
}
?>
