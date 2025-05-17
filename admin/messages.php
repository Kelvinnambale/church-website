<?php
// Start session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection parameters
$servername = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$dbname = "church_db";

// Initialize variables
$messages = [];
$totalMessages = 0;
$messagesPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $messagesPerPage;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Create database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle message actions (mark as read/unread, delete)
    if (isset($_POST['action']) && isset($_POST['message_id'])) {
        $messageId = (int)$_POST['message_id'];
        
        if ($_POST['action'] === 'mark_read') {
            $stmt = $conn->prepare("UPDATE contact_messages SET is_read = TRUE WHERE id = :id");
            $stmt->bindParam(':id', $messageId);
            $stmt->execute();
        } elseif ($_POST['action'] === 'mark_unread') {
            $stmt = $conn->prepare("UPDATE contact_messages SET is_read = FALSE WHERE id = :id");
            $stmt->bindParam(':id', $messageId);
            $stmt->execute();
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->bindParam(':id', $messageId);
            $stmt->execute();
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['page']) ? "?page=" . $_GET['page'] : ""));
        exit;
    }
    
    // Prepare search condition
    $searchCondition = '';
    $searchParams = [];
    if (!empty($searchTerm)) {
        $searchCondition = " WHERE name LIKE :search OR email LIKE :search OR message LIKE :search OR phone LIKE :search";
        $searchParams[':search'] = "%$searchTerm%";
    }
    
    // Count total messages (for pagination)
    $countQuery = "SELECT COUNT(*) FROM contact_messages" . $searchCondition;
    $stmt = $conn->prepare($countQuery);
    if (!empty($searchParams)) {
        foreach ($searchParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $totalMessages = $stmt->fetchColumn();
    
    // Calculate total pages
    $totalPages = ceil($totalMessages / $messagesPerPage);
    
    // Adjust current page if out of bounds
    if ($currentPage < 1) $currentPage = 1;
    if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;
    
    // Get messages with pagination
    $query = "SELECT * FROM contact_messages" . $searchCondition . 
             " ORDER BY submission_date DESC LIMIT :offset, :limit";
    $stmt = $conn->prepare($query);
    if (!empty($searchParams)) {
        foreach ($searchParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $messagesPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Messages</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .nav-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-form {
            display: flex;
            gap: 10px;
        }
        .search-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .messages-table th, .messages-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .messages-table th {
            background-color: #f2f2f2;
        }
        .messages-table tr:hover {
            background-color: #f9f9f9;
        }
        .message-unread {
            font-weight: bold;
            background-color: #f0f7ff;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            background-color: #f2f2f2;
            margin: 0 4px;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination .active {
            background-color: #4CAF50;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }
        .action-buttons .read-btn {
            background-color: #4CAF50;
        }
        .action-buttons .unread-btn {
            background-color: #2196F3;
        }
        .action-buttons .delete-btn {
            background-color: #f44336;
        }
        .message-details {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }
        .no-messages {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .logout-btn {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Form Messages</h1>
        
        <div class="nav-bar">
            <form class="search-form" action="" method="GET">
                <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="padding: 8px; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($messages)): ?>
            <div class="no-messages">No messages found.</div>
        <?php else: ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr class="<?php echo $message['is_read'] ? '' : 'message-unread'; ?>">
                            <td><?php echo htmlspecialchars($message['id']); ?></td>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($message['phone'] ?: 'N/A'); ?></td>
                            <td class="message-details"><?php echo htmlspecialchars($message['message']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($message['submission_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="post">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <?php if ($message['is_read']): ?>
                                            <button type="submit" name="action" value="mark_unread" class="unread-btn">Mark Unread</button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="mark_read" class="read-btn">Mark Read</button>
                                        <?php endif; ?>
                                        <button type="submit" name="action" value="delete" class="delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=1<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">&laquo; First</a>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">&lsaquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    // Display page numbers
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">Next &rsaquo;</a>
                        <a href="?page=<?php echo $totalPages; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">Last &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
        // Add click event to show full message content
        document.querySelectorAll('.message-details').forEach(cell => {
            cell.addEventListener('click', function() {
                const fullMessage = this.innerText;
                alert(fullMessage);
            });
            cell.style.cursor = 'pointer';
            cell.title = 'Click to view full message';
        });
    </script>
</body>
</html>