<?php
// Start session for potential user authentication
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection parameters
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'changochurch_db';
$db_charset = 'utf8mb4';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Initialize variables
$search = '';
$ministry_filter = '';
$registrations = [];

// Handle registration actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Insert new record
        $name = sanitize($_POST['name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $dob = sanitize($_POST['dob']);
        $age = intval($_POST['age']);
        $gender = sanitize($_POST['gender']);
        $ministry = sanitize($_POST['ministry']);
        $membership = sanitize($_POST['membership']);
        $attendance = sanitize($_POST['attendance']);
        $address = sanitize($_POST['address']);
        $availability = sanitize($_POST['availability']);
        $skills = sanitize($_POST['skills']);
        $motivation = sanitize($_POST['motivation']);
        $consent = isset($_POST['consent']) ? 1 : 0;
        $registration_date = sanitize($_POST['registration_date']);
        $processed = isset($_POST['processed']) ? 1 : 0;
        $notes = sanitize($_POST['notes']);

        $stmt = $conn->prepare("
            INSERT INTO ministry_registrations 
            (name, phone, email, dob, age, gender, ministry, membership, attendance, address, availability, skills, motivation, consent, registration_date, processed, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssissssssssisii",
            $name, $phone, $email, $dob, $age, $gender, $ministry, $membership, $attendance, $address, $availability, $skills, $motivation, $consent, $registration_date, $processed, $notes
        );
        $stmt->execute();
        $stmt->close();

        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $dob = sanitize($_POST['dob']);
        $age = intval($_POST['age']);
        $gender = sanitize($_POST['gender']);
        $ministry = sanitize($_POST['ministry']);
        $membership = sanitize($_POST['membership']);
        $attendance = sanitize($_POST['attendance']);
        $address = sanitize($_POST['address']);
        $availability = sanitize($_POST['availability']);
        $skills = sanitize($_POST['skills']);
        $motivation = sanitize($_POST['motivation']);
        $consent = isset($_POST['consent']) ? 1 : 0;
        $registration_date = sanitize($_POST['registration_date']);
        $processed = isset($_POST['processed']) ? 1 : 0;
        $notes = sanitize($_POST['notes']);

        $stmt = $conn->prepare("
            UPDATE ministry_registrations SET
            name = ?, phone = ?, email = ?, dob = ?, age = ?, gender = ?, ministry = ?, 
            membership = ?, attendance = ?, address = ?, availability = ?, skills = ?, 
            motivation = ?, consent = ?, registration_date = ?, processed = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssssissssssssisiii",
            $name, $phone, $email, $dob, $age, $gender, $ministry, $membership, $attendance, $address, $availability, $skills, $motivation, $consent, $registration_date, $processed, $notes, $id
        );
        $stmt->execute();
        $stmt->close();

        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM ministry_registrations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Calculate statistics for dashboard
// Total registrations
$total_registrations = $conn->query("SELECT COUNT(*) as count FROM ministry_registrations")->fetch_assoc()['count'];

// Registrations by ministry
$ministry_stats = [];
$ministry_result = $conn->query("SELECT ministry, COUNT(*) as count FROM ministry_registrations GROUP BY ministry");
while ($row = $ministry_result->fetch_assoc()) {
    $ministry_stats[$row['ministry']] = $row['count'];
}

// Registrations by gender
$gender_stats = [];
$gender_result = $conn->query("SELECT gender, COUNT(*) as count FROM ministry_registrations GROUP BY gender");
while ($row = $gender_result->fetch_assoc()) {
    $gender_stats[$row['gender']] = $row['count'];
}

// Recent registrations (last 30 days)
$recent_count = $conn->query("SELECT COUNT(*) as count FROM ministry_registrations WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];

// Processed vs Unprocessed
$processed_count = $conn->query("SELECT COUNT(*) as count FROM ministry_registrations WHERE processed = 1")->fetch_assoc()['count'];
$unprocessed_count = $total_registrations - $processed_count;

// Fetch records for display
if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
}
if (isset($_GET['ministry']) && !empty($_GET['ministry'])) {
    $ministry_filter = sanitize($_GET['ministry']);
}

$sql = "SELECT * FROM ministry_registrations WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR phone LIKE '%$search%')";
}
if (!empty($ministry_filter)) {
    $sql .= " AND ministry = '$ministry_filter'";
}
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $registrations[] = $row;
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="registrations_export_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'ID', 'Name', 'Phone', 'Email', 'DOB', 'Age', 'Gender',
        'Ministry', 'Membership', 'Attendance', 'Address',
        'Availability', 'Skills', 'Motivation', 'Consent',
        'Registration Date', 'Processed', 'Notes'
    ]);

    foreach ($registrations as $reg) {
        fputcsv($output, $reg);
    }

    fclose($output);
    exit;
}

// Fetch single record for modal view or edit
$registration_details = null;
$edit_registration = null;

if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $id = intval($_GET['view']);
    $result = $conn->query("SELECT * FROM ministry_registrations WHERE id = $id");
    $registration_details = $result->fetch_assoc();
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM ministry_registrations WHERE id = $id");
    $edit_registration = $result->fetch_assoc();
}

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Church Registration Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="admin/admin.css">
  <style>
    :root {
      --black-color: hsl(216, 73%, 19%);
      --black-color-light: hsl(216, 60%, 30%);
      --black-color-lighten: hsl(216, 40%, 50%);
      --white-color: hsl(36, 100%, 99%);
      --body-color: hsl(36, 100%, 98%);
      --accent-color: #FF5733;
      --blue-accent: var(--black-color);
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    /* General styles */
    /* Dashboard */
    .dashboard {
      margin-bottom: 30px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background-color: var(--white-color);
      padding: 20px;
      border-radius: 8px;
      box-shadow: var(--shadow);
      text-align: center;
      transition: var(--transition);
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-card .icon {
      font-size: 2.5rem;
      color: var(--accent-color);
      margin-bottom: 10px;
    }

    .stat-card h3 {
      font-size: 1.8rem;
      margin-bottom: 5px;
      color: var(--black-color);
    }

    .stat-card p {
      color: var(--black-color-light);
      font-size: 1rem;
    }

    .stat-chart {
      background-color: var(--white-color);
      padding: 20px;
      border-radius: 8px;
      box-shadow: var(--shadow);
      margin-bottom: 20px;
    }

    .chart-container {
      display: flex;
      justify-content: space-around;
      margin-top: 15px;
    }

    .chart-item {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .chart-bar {
      width: 40px;
      background-color: var(--accent-color);
      margin-bottom: 5px;
      border-radius: 3px 3px 0 0;
    }

    /* Controls */
    .controls {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .search-filter {
      display: flex;
      gap: 10px;
    }

    .search-filter input,
    .search-filter select {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .actions {
      display: flex;
      gap: 10px;
    }

    /* Table */
    .table-container {
      background-color: var(--white-color);
      border-radius: 8px;
      box-shadow: var(--shadow);
      padding: 20px;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background-color: var(--black-color);
      color: var(--white-color);
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    tr:hover {
      background-color: rgba(0, 0, 0, 0.02);
    }

    /* Buttons */
    .btn {
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      font-weight: 500;
    }

    .btn-primary {
      background-color: var(--blue-accent);
      color: var(--white-color);
    }

    .btn-primary:hover {
      background-color: var(--black-color-light);
    }

    .btn-accent {
      background-color: var(--accent-color);
      color: var(--white-color);
    }

    .btn-accent:hover {
      background-color: #e04a29;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: var(--white-color);
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    /* Forms */
    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: var(--black-color);
      font-weight: 500;
    }

    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    textarea.form-control {
      min-height: 100px;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      overflow-y: auto;
    }

    .modal-content {
      background-color: var(--white-color);
      margin: 50px auto;
      max-width: 800px;
      width: 90%;
      border-radius: 8px;
      box-shadow: var(--shadow);
      padding: 30px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .modal-body {
      margin-bottom: 20px;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding-top: 15px;
      border-top: 1px solid #eee;
    }

    /* Responsiveness */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      
      .sidebar {
        width: 100%;
        padding: 10px 0;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .controls {
        flex-direction: column;
        gap: 15px;
      }
      
      .search-filter {
        flex-direction: column;
      }
    }

    /* Checkbox styling */
    input[type="checkbox"] {
      width: auto;
      margin-right: 10px;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
    }
    .checkbox-group label {
      margin: 0;
      font-size: 1rem;
      color: var(--black-color);
    }
    :root {
      --primary-color: #3a7bd5;
      --primary-light: #6fa1ff;
      --primary-dark: #00569e;
      --accent-color: #f5a623;
      --text-light: #ffffff;
      --text-dark: #333333;
      --bg-light: #f8f9fa;
      --bg-dark: #243447;
      --success: #28a745;
      --warning: #f5a623;
      --danger: #dc3545;
      --gray-100: #f8f9fa;
      --gray-200: #e9ecef;
      --gray-300: #dee2e6;
      --gray-400: #ced4da;
      --gray-500: #adb5bd;
      --gray-600: #6c757d;
      --gray-700: #495057;
      --gray-800: #343a40;
      --gray-900: #212529;
      --transition: all 0.3s ease;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
      --shadow: 0 4px 6px rgba(0,0,0,0.1);
      --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
      --border-radius: 8px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--bg-light);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .container {
      display: flex;
      min-height: 100vh;
    }

    /* Modern Header & Sidebar */
    .sidebar {
      width: 280px;
      background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
      color: var(--text-light);
      transition: var(--transition);
      position: fixed;
      height: 100%;
      z-index: 1000;
      box-shadow: var(--shadow-lg);
      overflow-y: auto;
    }

    .sidebar-header {
      padding: 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.05);
    }

    .sidebar-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
      z-index: 0;
    }

    .sidebar-header h2 {
      font-size: 1.6rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
      position: relative;
      z-index: 1;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .sidebar-header p {
      color: rgba(255, 255, 255, 0.8);
      font-size: 1rem;
      font-style: italic;
      position: relative;
      z-index: 1;
    }

    .sidebar-user-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      background-color: rgba(0, 0, 0, 0.1);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.9rem;
    }

    .sidebar-user-info .user-avatar {
      display: flex;
      align-items: center;
    }

    .sidebar-user-info .user-avatar img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      margin-right: 10px;
      border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .sidebar-user-info .user-name {
      font-weight: 600;
      display: block;
    }

    .sidebar-user-info .user-role {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.8rem;
    }

    .sidebar-user-info .time {
      text-align: right;
      white-space: nowrap;
      padding-left: 10px;
    }

    .nav-list {
      padding: 1rem 0;
    }

    .nav-item {
      padding: 0;
      margin: 0.3rem 0.8rem;
      border-radius: var(--border-radius);
      transition: var(--transition);
      position: relative;
    }

    .nav-item a {
      color: var(--text-light);
      text-decoration: none;
      padding: 0.8rem 1rem;
      display: flex;
      align-items: center;
      transition: var(--transition);
      font-weight: 500;
      border-radius: var(--border-radius);
    }

    .nav-item:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .nav-item.active {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .nav-item.active a {
      color: var(--accent-color);
    }

    .nav-item i {
      margin-right: 12px;
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
      transition: var(--transition);
    }

    .nav-item:hover i {
      transform: translateX(3px);
    }

    .nav-divider {
      height: 1px;
      background-color: rgba(255, 255, 255, 0.1);
      margin: 1rem 1.5rem;
    }

    .sidebar-footer {
      padding: 1rem 1.5rem;
      text-align: center;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.6);
      background-color: rgba(0, 0, 0, 0.1);
    }

    /* Main content area */
    .main-content {
      flex: 1;
      padding: 0 0 2rem 280px; /* Add left padding equal to sidebar width */
      position: relative;
      transition: var(--transition);
    }

    /* New modern header for main content */
    .main-header {
      background-color: var(--text-light);
      box-shadow: var(--shadow);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 900;
    }

    .header-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--primary-color);
      display: flex;
      align-items: center;
    }

    .header-title i {
      margin-right: 10px;
      color: var(--accent-color);
      font-size: 1.8rem;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .header-search {
      position: relative;
      min-width: 300px;
    }

    .header-search input {
      width: 100%;
      padding: 0.6rem 1rem 0.6rem 2.5rem;
      border: 1px solid var(--gray-300);
      border-radius: 50px;
      font-size: 0.9rem;
      transition: var(--transition);
    }

    .header-search i {
      position: absolute;
      left: 0.8rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray-500);
    }

    .header-search input:focus {
      outline: none;
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(58, 123, 213, 0.15);
    }

    .header-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1rem;
      border-radius: 50px;
      background-color: transparent;
      border: 1px solid var(--gray-300);
      font-weight: 500;
      font-size: 0.9rem;
      color: var(--gray-700);
      cursor: pointer;
      transition: var(--transition);
    }

    .header-btn:hover {
      background-color: var(--gray-100);
    }

    .header-btn.primary {
      background-color: var(--primary-color);
      color: var(--text-light);
      border: none;
    }

    .header-btn.primary:hover {
      background-color: var(--primary-dark);
    }

    .header-user {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      position: relative;
      cursor: pointer;
    }

    .header-user img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 2px solid var(--gray-300);
    }

    .header-user-info {
      display: none;
    }

    .header-notification {
      position: relative;
      cursor: pointer;
    }

    .header-notification i {
      font-size: 1.3rem;
      color: var(--gray-700);
    }

    .notification-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: var(--danger);
      color: white;
      font-size: 0.7rem;
      font-weight: bold;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .content-wrapper {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Mobile responsive */
    .mobile-toggle {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--primary-color);
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        padding-left: 0;
      }
      
      .mobile-toggle {
        display: block;
      }
      
      .header-search {
        min-width: 200px;
      }
    }

    @media (max-width: 768px) {
      .header-search {
        display: none;
      }
      
      .header-actions {
        gap: 0.5rem;
      }
      
      .header-btn span {
        display: none;
      }
      
      .header-btn {
        padding: 0.5rem;
      }
      
      .header-title {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Modern Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <h2>Chango Friends</h2>
        <p>Church System</p>
      </div>
              
      <div class="nav-list">
        <div class="nav-item active">
          <a href="?tab=dashboard">
            <i class="fas fa-chart-pie"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="?tab=registrations">
            <i class="fas fa-users"></i>
            Registrations
          </a>
        </div>
        <div class="nav-divider"></div>
        <div class="nav-item">
          <a href="?export=csv">
            <i class="fas fa-file-export"></i>
            Export Data
          </a>
        </div>
        <div class="nav-item">
          <a href="#">
            <i class="fas fa-cog"></i>
            Settings
          </a>
        </div>
        <div class="nav-item">
          <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            Logout
          </a>
        </div>
        <div class="sidebar-user-info" style="padding: 1rem 1.5rem; background-color: rgba(0, 0, 0, 0.1); border-bottom: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.9rem; line-height: 1.4;">
        <div>Logged in:as
        <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></div>
        <div id="current-time" style="white-space: nowrap;"></div>
      </div>
      </div>
      
      <div class="sidebar-footer">
        ChurchAdmin v2.0 © 2025
      </div>
    </div>

    <!-- Main Content Area with Modern Header -->
    <div class="main-content">
      <!-- Modern Main Header -->
      <div class="main-header">
        <div class="header-left">
          <div class="mobile-toggle">
            <i class="fas fa-bars"></i>
          </div>
          <div class="header-title">
            <i class="fas fa-church"></i>
            Ministry Registration Dashboard
          </div>
        </div>
        <div class="header-actions">
          <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search registrations...">
          </div>
          <div class="header-notification">
            <i class="fas fa-bell"></i>
            <span class="notification-count">3</span>
          </div>
          <button class="header-btn primary">
            <i class="fas fa-plus"></i>
            <span>New Registration</span>
          </button>
          <div class="header-user">
            
            <div class="header-user-info">
              <span class="user-name">Admin</span>
            </div>
          </div>
        </div>
      </div>
             
      <div class="content-wrapper">
        <!-- Your existing content for dashboard etc. -->
        <div style="background-color: var(--gray-100); border-radius: var(--border-radius); padding: 2rem; text-align: center;">
          <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Welcome to the Chango Friends Management System</h2>
          <p style="color: var(--gray-600);">Manage your church efficiently and effectively.</p>   

    <!-- Dashboard Tab -->
    <?php if ($active_tab == 'dashboard'): ?>
    <div class="dashboard">
      <h2>Dashboard</h2>
      
      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
          <h3><?= $total_registrations ?></h3>
          <p>Total Registrations</p>
        </div>
        
        <div class="stat-card">
          <div class="icon">
            <i class="fas fa-user-plus"></i>
          </div>
          <h3><?= $recent_count ?></h3>
          <p>New (Last 30 Days)</p>
        </div>
        
        <div class="stat-card">
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3><?= $processed_count ?></h3>
          <p>Processed</p>
        </div>
        
        <div class="stat-card">
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
          <h3><?= $unprocessed_count ?></h3>
          <p>Pending</p>
        </div>
      </div>
      

      <!-- Recent Registrations -->
      <div class="table-container">
        <h3>Recent Registrations</h3>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Ministry</th>
              <th>Registration Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $recent_regs = $conn->query("SELECT * FROM ministry_registrations ORDER BY registration_date DESC LIMIT 5");
            while ($row = $recent_regs->fetch_assoc()): 
            ?>
            <tr>
              <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['ministry'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['registration_date'] ?? '') ?></td>
              <td><?= $row['processed'] ? '<span style="color: green;">Processed</span>' : '<span style="color: orange;">Pending</span>' ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Registrations Tab -->
    <?php else: ?>
    
    <div class="controls">
      <form method="GET" action="" class="search-filter">
        <input type="hidden" name="tab" value="registrations">
        <input type="text" name="search" placeholder="Search by name or phone" value="<?= htmlspecialchars($search ?? '') ?>">
        <select name="ministry">
          <option value="">All Ministries</option>
          <option value="Children Ministry" <?= $ministry_filter == 'Children Ministry' ? 'selected' : '' ?>>Children Ministry</option>
          <option value="YFP Ministry" <?= $ministry_filter == 'YFP Ministry' ? 'selected' : '' ?>>YFP Ministry</option>
          <option value="USFW/Quakermen" <?= $ministry_filter == 'USFW/Quakermen' ? 'selected' : '' ?>>USFW/Quakermen</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
      </form>
      <div class="actions">
        <button class="btn btn-accent" onclick="document.getElementById('addModal').style.display='block'">
          <i class="fas fa-plus"></i> Add New
        </button>
      </div>
    </div>

    <!-- Registrations Table -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Ministry</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registrations as $r): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['ministry'] ?? '') ?></td>
              <td><?= $r['processed'] ? '<span style="color: green;">Processed</span>' : '<span style="color: orange;">Pending</span>' ?></td>
              <td class="actions">
                <a href="?tab=registrations&view=<?= $r['id'] ?>"><button class="btn btn-secondary"><i class="fas fa-eye"></i></button></a>
                <a href="?tab=registrations&edit=<?= $r['id'] ?>"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn btn-accent" onclick="return confirm('Are you sure you want to delete this registration?')"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- View Modal -->
<?php if ($registration_details): ?>
<div class="modal" id="viewModal" style="display:block">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Registration Details</h2>
      <button onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-secondary"><i class="fas fa-times"></i> Close</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Add New Registration</h2>
      <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="dob" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Age</label>
          <input type="number" name="age" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="gender" class="form-control" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Ministry</label>
          <select name="ministry" class="form-control" required>
            <option value="">Select Ministry</option>
            <option value="Children Ministry">Children Ministry</option>
            <option value="YFP Ministry">YFP Ministry</option>
            <option value="USFW/Quakermen">USFW/Quakermen</option>
          </select>
        </div>
        <div class="form-group">
          <label>Membership Status</label>
          <select name="membership" class="form-control" required>
            <option value="">Select Status</option>
            <option value="Member">Member</option>
            <option value="Regular Attendee">Regular Attendee</option>
            <option value="Visitor">Visitor</option>
            <option value="New Believer">New Believer</option>
          </select>
        </div>
        <div class="form-group">
          <label>Attendance Frequency</label>
          <select name="attendance" class="form-control" required>
            <option value="">Select Frequency</option>
            <option value="Weekly">Weekly</option>
            <option value="Monthly">Monthly</option>
            <option value="Occasionally">Occasionally</option>
            <option value="First Time">First Time</option>
          </select>
        </div>
        <div class="form-group">
          <label>Address</label>
          <textarea name="address" class="form-control" required></textarea>
        </div>
        <div class="form-group">
          <label>Availability</label>
          <textarea name="availability" class="form-control" placeholder="Days and times available for ministry service"></textarea>
        </div>
        <div class="form-group">
          <label>Skills</label>
          <textarea name="skills" class="form-control" placeholder="Relevant skills or talents"></textarea>
        </div>
        <div class="form-group">
          <label>Motivation</label>
          <textarea name="motivation" class="form-control" placeholder="Reason for joining this ministry"></textarea>
        </div>
        <div class="form-group checkbox-group">
          <input type="checkbox" name="consent" id="consent" value="1">
          <label for="consent">I consent to the storage and usage of my data for church ministry purposes</label>
        </div>
        <div class="form-group">
          <label>Registration Date</label>
          <input type="datetime-local" name="registration_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>
        <div class="form-group checkbox-group">
          <input type="checkbox" name="processed" id="processed" value="1">
          <label for="processed">Mark as processed</label>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <textarea name="notes" class="form-control" placeholder="Additional information or administrative notes"></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
          <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<?php if ($edit_registration): ?>
<div class="modal" id="editModal" style="display:block">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Registration</h2>
      <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= $edit_registration['id'] ?>">
        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_registration['name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($edit_registration['phone']) ?>" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_registration['email']) ?>">
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($edit_registration['dob']) ?>" required>
        </div>
        <div class="form-group">
          <label>Age</label>
          <input type="number" name="age" class="form-control" value="<?= $edit_registration['age'] ?>" required>
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="gender" class="form-control" required>
            <option value="Male" <?= $edit_registration['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $edit_registration['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $edit_registration['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Ministry</label>
          <select name="ministry" class="form-control" required>
            <option value="Children Ministry" <?= $edit_registration['ministry'] == 'Children Ministry' ? 'selected' : '' ?>>Children Ministry</option>
            <option value="YFP Ministry" <?= $edit_registration['ministry'] == 'YFP Ministry' ? 'selected' : '' ?>>YFP Ministry</option>
            <option value="USFW/Quakermen" <?= $edit_registration['ministry'] == 'USFW/Quakermen' ? 'selected' : '' ?>>USFW/Quakermen</option>
          </select>
        </div>
        <div class="form-group">
          <label>Membership Status</label>
          <select name="membership" class="form-control" required>
            <option value="Member" <?= $edit_registration['membership'] == 'Member' ? 'selected' : '' ?>>Member</option>
            <option value="Regular Attendee" <?= $edit_registration['membership'] == 'Regular Attendee' ? 'selected' : '' ?>>Regular Attendee</option>
            <option value="Visitor" <?= $edit_registration['membership'] == 'Visitor' ? 'selected' : '' ?>>Visitor</option>
            <option value="New Believer" <?= $edit_registration['membership'] == 'New Believer' ? 'selected' : '' ?>>New Believer</option>
          </select>
        </div>
        <div class="form-group">
          <label>Attendance Frequency</label>
          <select name="attendance" class="form-control" required>
            <option value="Weekly" <?= $edit_registration['attendance'] == 'Weekly' ? 'selected' : '' ?>>Weekly</option>
            <option value="Monthly" <?= $edit_registration['attendance'] == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="Occasionally" <?= $edit_registration['attendance'] == 'Occasionally' ? 'selected' : '' ?>>Occasionally</option>
            <option value="First Time" <?= $edit_registration['attendance'] == 'First Time' ? 'selected' : '' ?>>First Time</option>
          </select>
        </div>
        <div class="form-group">
          <label>Address</label>
          <textarea name="address" class="form-control" required><?= htmlspecialchars($edit_registration['address']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Availability</label>
          <textarea name="availability" class="form-control"><?= htmlspecialchars($edit_registration['availability']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Skills</label>
          <textarea name="skills" class="form-control"><?= htmlspecialchars($edit_registration['skills']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Motivation</label>
          <textarea name="motivation" class="form-control"><?= htmlspecialchars($edit_registration['motivation']) ?></textarea>
        </div>
        <div class="form-group checkbox-group">
          <input type="checkbox" name="consent" id="edit_consent" value="1" <?= $edit_registration['consent'] ? 'checked' : '' ?>>
          <label for="edit_consent">I consent to the storage and usage of my data for church ministry purposes</label>
        </div>
        <div class="form-group">
          <label>Registration Date</label>
          <input type="datetime-local" name="registration_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($edit_registration['registration_date'])) ?>" required>
        </div>
        <div class="form-group checkbox-group">
          <input type="checkbox" name="processed" id="edit_processed" value="1" <?= $edit_registration['processed'] ? 'checked' : '' ?>>
          <label for="edit_processed">Mark as processed</label>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <textarea name="notes" class="form-control"><?= htmlspecialchars($edit_registration['notes']) ?></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
          <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
<!-- View Modal -->
<?php if ($registration_details): ?>
<div class="modal" id="viewModal" style="display:block">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Registration Details</h2>
      <button onclick="closeModal('viewModal')" class="btn btn-secondary"><i class="fas fa-times"></i> Close</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Name:</label>
        <p><?= htmlspecialchars($registration_details['name'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Phone:</label>
        <p><?= htmlspecialchars($registration_details['phone'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Email:</label>
        <p><?= htmlspecialchars($registration_details['email'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Date of Birth:</label>
        <p><?= htmlspecialchars($registration_details['dob'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Age:</label>
        <p><?= $registration_details['age'] ?></p>
      </div>
      <div class="form-group">
        <label>Gender:</label>
        <p><?= htmlspecialchars($registration_details['gender'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Ministry:</label>
        <p><?= htmlspecialchars($registration_details['ministry'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Membership:</label>
        <p><?= htmlspecialchars($registration_details['membership'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Attendance:</label>
        <p><?= htmlspecialchars($registration_details['attendance'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Address:</label>
        <p><?= htmlspecialchars($registration_details['address'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Availability:</label>
        <p><?= htmlspecialchars($registration_details['availability'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Skills:</label>
        <p><?= htmlspecialchars($registration_details['skills'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Motivation:</label>
        <p><?= htmlspecialchars($registration_details['motivation'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Consent:</label>
        <p><?= $registration_details['consent'] ? 'Yes' : 'No' ?></p>
      </div>
      <div class="form-group">
        <label>Registration Date:</label>
        <p><?= htmlspecialchars($registration_details['registration_date'] ?? '') ?></p>
      </div>
      <div class="form-group">
        <label>Processed:</label>
        <p><?= $registration_details['processed'] ? 'Yes' : 'No' ?></p>
      </div>
      <div class="form-group">
        <label>Notes:</label>
        <p><?= htmlspecialchars($registration_details['notes'] ?? '') ?></p>
      </div>
    </div>
    <div class="modal-footer">
      <a href="?tab=registrations&edit=<?= $registration_details['id'] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
      <button onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-secondary"><i class="fas fa-times"></i> Close</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Add a logout link/button -->
<div style="position: fixed; top: 10px; right: 10px;">
  <a href="logout.php" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.9rem; border-radius: 4px; text-decoration: none;">Logout</a>
</div>


<script>
 // Calculate age automatically when date of birth changes
 document.addEventListener('DOMContentLoaded', function() {
   // For add form
   const dobInputAdd = document.querySelector('#addModal input[name="dob"]');
   const ageInputAdd = document.querySelector('#addModal input[name="age"]');
   
   if (dobInputAdd && ageInputAdd) {
     dobInputAdd.addEventListener('change', function() {
       const dob = new Date(this.value);
       const today = new Date();
       let age = today.getFullYear() - dob.getFullYear();
       const monthDiff = today.getMonth() - dob.getMonth();
       
       if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
         age--;
       }
       
       ageInputAdd.value = age;
     });
   }
   
   // For edit form
   const dobInputEdit = document.querySelector('#editModal input[name="dob"]');
   const ageInputEdit = document.querySelector('#editModal input[name="age"]');
   
   if (dobInputEdit && ageInputEdit) {
     dobInputEdit.addEventListener('change', function() {
       const dob = new Date(this.value);
       const today = new Date();
       let age = today.getFullYear() - dob.getFullYear();
       const monthDiff = today.getMonth() - dob.getMonth();
       
       if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
         age--;
       }
       
       ageInputEdit.value = age;
     });
   }

   // Add current time display in sidebar
   function updateTime() {
     const now = new Date();
     const timeString = now.toLocaleTimeString();
     const dateString = now.toLocaleDateString();
     const currentTimeElem = document.getElementById('current-time');
     if (currentTimeElem) {
       currentTimeElem.textContent = dateString + ' ' + timeString;
     }
   }
   setInterval(updateTime, 1000);
   updateTime();
 });
 </script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.querySelector('.mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      if (overlay) {
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
      }
    });
  }
  
  if (overlay) {
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      overlay.style.display = 'none';
    });
  }
});
</script>

<!-- JavaScript for modal close -->
<script>
  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
    }
  }

  // Close modal when clicking outside modal content
  window.onclick = function(event) {
    if (event.target.classList && event.target.classList.contains('modal')) {
      event.target.style.display = 'none';
    }
  }

  // Close modal on ESC key press
  document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
      const modals = document.querySelectorAll('.modal');
      modals.forEach(modal => {
        if (modal.style.display === 'block') {
          modal.style.display = 'none';
        }
      });
    }
  });
</script>
</body>
</html>

