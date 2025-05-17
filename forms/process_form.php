<?php
// Database connection parameters
$host = "localhost";
$dbname = "changochurch_db";
$username = "root"; 
$password = ""; 

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create database connection
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        // Set PDO to throw exceptions on error
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get form data
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $dob = $_POST['dob'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        $ministry = $_POST['ministry'];
        $membership = $_POST['membership'];
        $attendance = $_POST['attendance'];
        $address = $_POST['address'];
        $guardian = isset($_POST['guardian']) ? trim($_POST['guardian']) : '';
        
        // Handle checkboxes (availability)
        $availability = isset($_POST['availability']) ? implode(", ", $_POST['availability']) : '';
        
        // Handle checkboxes (skills)
        $skills = isset($_POST['skills']) ? implode(", ", $_POST['skills']) : '';
        
        // Get motivation text
        $motivation = isset($_POST['motivation']) ? $_POST['motivation'] : '';
        
        // Get consent
        $consent = isset($_POST['consent']) ? 1 : 0;
        
        // Server-side validation for age less than zero
        if ($age < 0) {
            $response['message'] = 'Invalid age: Age cannot be less than zero.';
            echo json_encode($response);
            exit;
        }

        // Server-side validation for guardian if age < 5
        if ($age < 5 && empty($guardian)) {
            $response['message'] = 'Guardian name is required for children under 5 years old.';
            echo json_encode($response);
            exit;
        }
        
        // Prepare SQL statement
        $sql = "INSERT INTO ministry_registrations (
                    name, 
                    phone, 
                    email, 
                    dob, 
                    age, 
                    gender, 
                    ministry,
                    membership, 
                    attendance, 
                    address, 
                    availability, 
                    skills, 
                    motivation, 
                    consent,
                    registration_date
                ) VALUES (
                    :name, 
                    :phone, 
                    :email, 
                    :dob, 
                    :age, 
                    :gender, 
                    :ministry,
                    :membership, 
                    :attendance, 
                    :address, 
                    :availability, 
                    :skills, 
                    :motivation, 
                    :consent,
                    NOW()
                )";
        
        // Prepare statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':ministry', $ministry);
        $stmt->bindParam(':membership', $membership);
        $stmt->bindParam(':attendance', $attendance);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':availability', $availability);
        $stmt->bindParam(':skills', $skills);
        $stmt->bindParam(':motivation', $motivation);
        $stmt->bindParam(':consent', $consent);
        
        // Execute statement
        $stmt->execute();
        
        // Set response
        $response['success'] = true;
        $response['message'] = 'Registration successful!';
        
        // Log successful registration
        error_log("New ministry registration: $name ($ministry)");
        
    } catch(PDOException $e) {
        // Set error response
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Database error in ministry registration: " . $e->getMessage());
    }
    
    // Close connection
    $conn = null;
} else {
    // Not a POST request
    $response['message'] = 'Invalid request method';
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>