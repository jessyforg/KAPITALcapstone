<?php
session_start();
include '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle ticket status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['new_status'];
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);

    $sql = "UPDATE Tickets SET 
            status = ?, 
            admin_notes = ?,
            updated_at = NOW()
            WHERE ticket_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $admin_notes, $ticket_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Ticket status updated successfully!";
    } else {
        $error_message = "Error updating ticket status.";
    }
    mysqli_stmt_close($stmt);
}

// Get all tickets with user information
$sql = "SELECT t.*, u.username, u.email 
        FROM Tickets t 
        JOIN Users u ON t.user_id = u.user_id 
        ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .tickets-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .ticket-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .ticket-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
        }

        .ticket-meta {
            color: #666;
            font-size: 0.9em;
        }

        .ticket-description {
            margin: 15px 0;
            line-height: 1.6;
        }

        .ticket-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-resolved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .ticket-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            background-color: #f3c000;
            color: #000;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover {
            background-color: #e5b000;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-section select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="tickets-container">
        <h1>Manage Tickets</h1>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="filter-section">
            <select id="statusFilter" onchange="filterTickets()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="typeFilter" onchange="filterTickets()">
                <option value="all">All Types</option>
                <option value="bug">Bug Reports</option>
                <option value="feature">Feature Suggestions</option>
                <option value="improvement">Improvements</option>
                <option value="other">Other</option>
            </select>
        </div>

        <?php while ($ticket = mysqli_fetch_assoc($result)): ?>
            <div class="ticket-card" data-status="<?php echo $ticket['status']; ?>" data-type="<?php echo $ticket['type']; ?>">
                <div class="ticket-header">
                    <div class="ticket-title"><?php echo htmlspecialchars($ticket['title']); ?></div>
                    <div class="ticket-meta">
                        Submitted by: <?php echo htmlspecialchars($ticket['username']); ?> 
                        (<?php echo htmlspecialchars($ticket['email']); ?>)
                        on <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                    </div>
                </div>
                <div class="ticket-description">
                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                </div>
                <div class="ticket-status status-<?php echo $ticket['status']; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                </div>
                <form method="POST" action="" class="ticket-form">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                    <div class="form-group">
                        <label for="new_status_<?php echo $ticket['ticket_id']; ?>">Update Status</label>
                        <select name="new_status" id="new_status_<?php echo $ticket['ticket_id']; ?>">
                            <option value="pending" <?php echo $ticket['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="rejected" <?php echo $ticket['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="admin_notes_<?php echo $ticket['ticket_id']; ?>">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes_<?php echo $ticket['ticket_id']; ?>" rows="3"><?php echo htmlspecialchars($ticket['admin_notes'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn">Update Ticket</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function filterTickets() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const tickets = document.querySelectorAll('.ticket-card');

            tickets.forEach(ticket => {
                const status = ticket.dataset.status;
                const type = ticket.dataset.type;
                const statusMatch = statusFilter === 'all' || status === statusFilter;
                const typeMatch = typeFilter === 'all' || type === typeFilter;

                ticket.style.display = statusMatch && typeMatch ? 'block' : 'none';
            });
        }
    </script>
</body>
</html> 