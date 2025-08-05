<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch all notifications, including read and unread, sorted by created_at
    $stmt_notifications = $conn->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_notifications->bind_param('i', $user_id);
    $stmt_notifications->execute();
    $result_notifications = $stmt_notifications->get_result();
    $notifications = $result_notifications->fetch_all(MYSQLI_ASSOC);
    $notification_count = count(array_filter($notifications, function($notification) {
        return $notification['status'] == 'unread';
    }));

    // Modify the messages query to get the latest message per user
    $stmt_messages = $conn->prepare(
        "WITH RankedMessages AS (
            SELECT 
                m.message_id, 
                m.sender_id, 
                m.receiver_id, 
                m.content, 
                m.status, 
                m.sent_at,
                u.name AS sender_name,
                u.profile_picture_url,
                ROW_NUMBER() OVER (
                    PARTITION BY CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id 
                        ELSE m.sender_id 
                    END 
                    ORDER BY m.sent_at DESC
                ) as rn
            FROM Messages m
            JOIN Users u ON u.user_id = m.sender_id
            WHERE (m.sender_id = ? OR m.receiver_id = ?)
        )
        SELECT * FROM RankedMessages WHERE rn = 1 ORDER BY sent_at DESC"
    );
    $stmt_messages->bind_param('iii', $user_id, $user_id, $user_id);
    $stmt_messages->execute();
    $result_messages = $stmt_messages->get_result();
    $messages = $result_messages->fetch_all(MYSQLI_ASSOC);

    // Get count of unique users with unread messages received by the user
    $stmt_unread_messages = $conn->prepare(
        "SELECT COUNT(DISTINCT m.sender_id) as unread_count
        FROM Messages m
        WHERE m.receiver_id = ? AND m.status = 'unread'"
    );
    $stmt_unread_messages->bind_param('i', $user_id);
    $stmt_unread_messages->execute();
    $result_unread_messages = $stmt_unread_messages->get_result();
    $unread_message_data = $result_unread_messages->fetch_assoc();
    $unread_message_count = $unread_message_data['unread_count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS Configuration -->
    <link rel="stylesheet" href="tailwind-config.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-init.js"></script>
    
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #181818;
            color: #fff;
            min-width: 320px;
        }
        /* Floating, blurred navbar - Enhanced with Tailwind-compatible styles */
        .floating-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999; /* Increased z-index to ensure it's always on top */
            margin: 0 auto;
            max-width: 1200px;
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.35); /* Slightly stronger shadow */
            background: rgba(36, 37, 38, 0.95); /* More opaque background */
            border: 1px solid rgba(234, 88, 12, 0.3); /* Slightly more visible border */
            backdrop-filter: blur(20px) saturate(180%); /* Stronger blur */
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 72px;
            transition: all 0.3s ease;
            transform: translateY(16px);
        }
        
        /* Responsive navbar adjustments */
        @media (max-width: 768px) {
            .floating-navbar {
                margin: 0 8px;
                max-width: calc(100% - 16px);
                padding: 0 12px;
                min-height: 64px;
                transform: translateY(8px);
            }
        }
        
        @media (max-width: 475px) {
            .floating-navbar {
                margin: 0 4px;
                max-width: calc(100% - 8px);
                padding: 0 8px;
                min-height: 56px;
                border-radius: 16px;
                transform: translateY(4px);
            }
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-brand .kapital-logo {
            color: #ea580c;
            font-weight: 700;
            font-size: 1.6em;
            text-decoration: none;
            letter-spacing: -1px;
        }
        .navbar-brand .brand-x {
            color: #fff;
            font-size: 1.3em;
            font-weight: 400;
            margin: 0 4px;
        }
        .navbar-brand .taraki-logo {
            height: 32px;
            width: auto;
            display: inline-block;
            vertical-align: middle;
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .back-button {
            color: #fff;
            text-decoration: none;
            font-size: 1.08em;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(234, 88, 12, 0.1);
            border: 1px solid rgba(234, 88, 12, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .back-button:hover {
            background: rgba(234, 88, 12, 0.2);
            color: #ea580c;
        }
        .back-button i {
            font-size: 0.9em;
        }
        .navbar-center {
            flex: 1;
            display: flex;
            justify-content: center;
            margin: 0 24px;
        }
        .navbar-menu {
            display: flex;
            gap: 36px;
            align-items: center;
        }
        .navbar-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 1.08em;
            font-weight: 500;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }
        .navbar-menu a.active,
        .navbar-menu a:hover {
            color: #ea580c;
            border-bottom: 2px solid #ea580c;
        }
        .navbar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .navbar-actions .login-btn {
            background: transparent;
            color: #fff;
            border: 1.5px solid #ea580c;
            border-radius: 8px;
            padding: 7px 22px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .navbar-actions .login-btn:hover {
            background: #ea580c;
            color: #fff;
        }
        .navbar-actions .getstarted-btn {
            background: #ea580c;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 22px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .navbar-actions .getstarted-btn:hover {
            background: #ff6b1a;
            transform: translateY(-1px);
        }
        .search-container {
            position: relative;
            width: 300px;
        }
        .search-form {
            display: flex;
            align-items: center;
            background: rgba(35, 39, 42, 0.5);
            border: 1px solid rgba(234, 88, 12, 0.2);
            border-radius: 12px;
            padding: 4px 12px;
            transition: all 0.2s ease;
        }
        .search-form:focus-within {
            background: rgba(35, 39, 42, 0.8);
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1);
        }
        .search-form input[type="text"] {
            border: none;
            background: transparent;
            color: #fff;
            font-size: 0.95em;
            padding: 8px;
            outline: none;
            width: 100%;
        }
        .search-form input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .search-form button {
            background: none;
            border: none;
            color: #ea580c;
            font-size: 1.1em;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        .search-form button:hover {
            color: #fff;
            background: rgba(234, 88, 12, 0.2);
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(24, 24, 24, 0.98);
            border: 1px solid rgba(234, 88, 12, 0.2);
            border-radius: 12px;
            margin-top: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            z-index: 1000;
            overflow: hidden;
        }
        .result-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .result-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: inherit;
            transition: background 0.2s ease;
        }
        .result-item a:hover {
            background: rgba(234, 88, 12, 0.1);
        }
        .result-icon {
            color: #ea580c;
            font-size: 1.2em;
            width: 24px;
            text-align: center;
        }
        .result-content {
            flex: 1;
            min-width: 0;
        }
        .result-title {
            font-weight: 600;
            color: #fff;
            margin-bottom: 2px;
        }
        .result-subtitle {
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.6);
        }
        .result-type {
            color: #ea580c;
            font-size: 0.85em;
            font-weight: 500;
        }
        .navbar-actions .login-btn,
        .navbar-actions .getstarted-btn {
            text-decoration: none !important;
        }
        .navbar-actions .login-btn:hover,
        .navbar-actions .getstarted-btn:hover,
        .navbar-actions .login-btn:active,
        .navbar-actions .getstarted-btn:active {
            text-decoration: none !important;
        }
        @media (max-width: 900px) {
            .floating-navbar {
                max-width: 98vw;
                padding: 0 16px;
            }
            .search-container {
                width: 200px;
            }
            .navbar-menu {
                gap: 18px;
            }
        }
        @media (max-width: 700px) {
            .floating-navbar {
                flex-direction: column;
                align-items: stretch;
                min-height: unset;
                padding: 10px 4vw;
            }
            .navbar-left {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .search-container {
                width: 100%;
            }
            .navbar-center {
                justify-content: flex-start;
                margin: 10px 0;
            }
            .navbar-actions {
                justify-content: flex-end;
            }
        }
        @media (max-width: 768px) {
            .floating-navbar {
                flex-direction: column;
                padding: 16px;
            }
            .navbar-menu {
                flex-direction: column;
                gap: 16px;
            }
        }
        /* Notification/Message Dropdown Styles */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }
        .icon-btn {
            position: relative;
            color: #ea580c;
            text-decoration: none;
            font-size: 1.2em;
            padding: 8px;
            transition: all 0.2s ease;
            background: none;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .icon-btn:hover {
            color: #ff6b1a;
            transform: translateY(-1px);
        }
        .dropdown-container:hover .dropdown-content:not(.profile-dropdown-content),
        .dropdown-container:focus-within .dropdown-content:not(.profile-dropdown-content) {
            display: block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 120%;
            min-width: 280px;
            max-width: 340px;
            background: rgba(24, 24, 24, 0.98);
            border-radius: 14px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
            z-index: 2000;
            overflow: hidden;
            border: 1px solid rgba(234, 88, 12, 0.2);
        }
        .dropdown-content.show {
            display: block;
        }
        .notification-item, .message-item {
            padding: 10px 14px;
            border-bottom: 1px solid #292929;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
            font-size: 0.98em;
            min-height: 48px;
        }
        .notification-item:last-child, .message-item:last-child {
            border-bottom: none;
        }
        .notification-item.unread, .message-item.unread {
            background: rgba(255, 145, 0, 0.08);
            font-weight: bold;
        }
        .notification-item.read, .message-item.read {
            background: none;
            color: #bbb;
        }
        .notification-item:hover, .message-item:hover {
            background: rgba(255, 145, 0, 0.15);
        }
        .message-avatar img, .message-avatar i {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            background: #222;
            border: 2px solid #ea580c;
            display: block;
        }
        .message-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
        }
        .message-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.97em;
        }
        .message-name {
            font-weight: 600;
            color: #fff;
            font-size: 1em;
        }
        .message-direction {
            font-size: 0.85em;
            color: #FF9100;
            margin-left: 8px;
        }
        .message-preview {
            font-size: 0.97em;
            color: #bbb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        /* Profile Dropdown Styles */
        .profile-avatar-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 8px;
            transition: all 0.2s ease;
            position: relative;
        }
        .profile-avatar-btn:hover {
            transform: translateY(-1px);
        }
        .profile-avatar-outline {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }
        .profile-avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            background: #222;
            display: block;
        }
        .profile-avatar-img-lg {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background: #222;
            border: 2px solid #ea580c;
        }
        .profile-dropdown-content {
            min-width: 240px;
            max-width: 320px;
            padding: 0;
            background: rgba(24, 24, 24, 0.98);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
            overflow: hidden;
            right: 0;
            top: 120%;
            border: 1px solid rgba(234, 88, 12, 0.2);
        }
        .profile-dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 18px 12px 18px;
            border-bottom: 1px solid #333;
        }
        .profile-dropdown-name {
            font-weight: 700;
            color: #fff;
            font-size: 1.1em;
        }
        .profile-dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            color: #fff;
            text-decoration: none;
            font-size: 1em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }
        .profile-dropdown-content a:hover {
            background: rgba(234, 88, 12, 0.1);
            color: #ea580c;
        }
        .profile-dropdown-content .logout-link {
            color: #ff4d4f;
            font-weight: 600;
        }
        .profile-dropdown-content .logout-link:hover {
            background: rgba(255, 77, 79, 0.12);
            color: #ff4d4f;
        }
        .dropdown-divider {
            height: 1px;
            background: #333;
            margin: 0;
            border: none;
        }
        .floating-navbar .search-container {
            display: flex;
            align-items: center;
            height: 100%;
            min-height: 0;
            margin: 0;
            padding: 0;
        }
        .floating-navbar .search-form {
            display: flex;
            align-items: center;
            height: 44px;
            padding: 0 12px;
            box-sizing: border-box;
        }
        .floating-navbar .search-form input[type='text'] {
            height: 32px;
            line-height: 32px;
            padding: 0 8px;
            font-size: 1em;
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            margin: 0;
        }
        .floating-navbar .search-form button {
            height: 32px;
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            line-height: 1;
            background: none;
            border: none;
            color: #ea580c;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 4px;
            padding: 0;
            box-sizing: border-box;
        }
        .floating-navbar .search-form button i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.2em;
            width: 100%;
            height: 100%;
        }
        /* Badge styles for notifications and messages */
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ea580c;
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px #181818;
            z-index: 10;
        }
    </style>
</head>

<body>
    <div class="floating-navbar px-4 sm:px-6 lg:px-8">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="flex items-center gap-3 sm:gap-4 lg:gap-6 flex-1 min-w-0">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                $pages_with_back = ['startup_detail.php', 'edit_startup.php', 'create_startup.php', 'post-job.php'];
                if (in_array($current_page, $pages_with_back)):
                ?>
                <a href="javascript:history.back()" class="back-button hidden xs:flex">
                    <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Back</span>
                </a>
                <?php endif; ?>
                <div class="flex items-center gap-2 sm:gap-3 navbar-brand">
                    <a href="index.php" class="kapital-logo flex-shrink-0">
                        <img src="imgs/kapitalwhiteorange.svg" alt="Kapital Logo" 
                             class="w-16 h-8 xs:w-20 xs:h-10 sm:w-24 sm:h-12 lg:w-28 lg:h-14 object-contain">
                    </a>
                    <span class="brand-x text-white text-lg sm:text-xl font-normal mx-1">×</span>
                    <a href="https://taraki.vercel.app" target="_blank" rel="noopener" class="flex-shrink-0">
                        <img src="imgs/logo.png" alt="Taraki Logo" class="h-6 sm:h-8 w-auto object-contain">
                    </a>
                </div>
                <?php if (empty($hide_navbar_search)): ?>
                <div class="search-container hidden md:block flex-1 max-w-sm">
                    <form class="search-form" action="search.php" method="get" onsubmit="return false;">
                        <input type="text" id="searchInput" name="query" 
                               placeholder="Search..." autocomplete="off" 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-full text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-orange focus:border-transparent" />
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div id="searchResults" class="search-results" style="display: none;"></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="navbar-center">
                <?php
                $show_nav_links = false;
                $current_page = basename($_SERVER['PHP_SELF']);
                $pages_with_nav = ['index.php', 'messages.php', 'about-us.php'];
                if (isset($_SESSION['user_id']) && in_array($current_page, $pages_with_nav)) {
                    $show_nav_links = true;
                }
                // Determine dashboard link based on user role
                $dashboard_link = 'dashboard.php';
                if (isset($_SESSION['role'])) {
                    if ($_SESSION['role'] === 'entrepreneur') {
                        $dashboard_link = 'entrepreneurs.php';
                    } elseif ($_SESSION['role'] === 'investor') {
                        $dashboard_link = 'investors.php';
                    } elseif ($_SESSION['role'] === 'job_seeker') {
                        $dashboard_link = 'job-seekers.php';
                    } elseif ($_SESSION['role'] === 'admin') {
                        $dashboard_link = 'admin-panel.php';
                    }
                }
                ?>
                <?php if ($show_nav_links && $current_page !== 'messages.php'): ?>
                <nav class="navbar-menu">
                    <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo $dashboard_link; ?>" class="<?php echo in_array($current_page, ['dashboard.php','entrepreneurs.php','investors.php','job-seekers.php','admin-panel.php']) ? 'active' : ''; ?>">Dashboard</a>
                    <a href="about-us.php" class="<?php echo $current_page == 'about-us.php' ? 'active' : ''; ?>">About Us</a>
                </nav>
                <?php endif; ?>
            </div>
            <div class="navbar-actions">
                <div class="dropdown-container">
                    <a class="icon-btn" onclick="toggleDropdown('notificationsDropdown')">
                        <i class="fas fa-bell"></i>
                        <?php if ($notification_count > 0): ?>
                        <span class="badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div id="notificationsDropdown" class="dropdown-content notifications-dropdown">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a href="notification_redirect.php?notification_id=<?php echo $notification['notification_id']; ?>"
                                    class="notification-item <?php echo ($notification['status'] == 'unread') ? 'unread' : 'read'; ?>"
                                    data-notification-id="<?php echo $notification['notification_id']; ?>">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notification-item read">No new notifications</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown-container">
                    <a href="messages.php" class="icon-btn" onclick="toggleDropdown('messagesDropdown')">
                        <i class="fas fa-envelope"></i>
                        <?php if ($unread_message_count > 0): ?>
                        <span class="badge"><?php echo $unread_message_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div id="messagesDropdown" class="dropdown-content messages-dropdown">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                                <a href="messages.php?chat_with=<?php echo ($message['sender_id'] == $user_id) ? $message['receiver_id'] : $message['sender_id']; ?>"
                                    class="message-item <?php echo ($message['status'] == 'unread') ? 'unread' : 'read'; ?>"
                                    data-message-id="<?php echo $message['message_id']; ?>">
                                    <div class="message-avatar">
                                        <?php if ($message['profile_picture_url']): ?>
                                            <img src="<?php echo htmlspecialchars($message['profile_picture_url']); ?>" alt="Profile Picture" class="profile-avatar-img">
                                        <?php else: ?>
                                            <i class="fas fa-user" style="color:#ea580c; font-size: 22px; display: block; line-height: 1; vertical-align: middle;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-header">
                                            <span class="message-name"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                                            <span class="message-direction">
                                                <?php echo ($message['sender_id'] == $user_id) ? 'Sent' : 'Received'; ?>
                                            </span>
                                        </div>
                                        <div class="message-preview">
                                            <?php echo htmlspecialchars($message['content']); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="message-item read">No messages</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-dropdown dropdown-container">
                    <a class="profile-avatar-btn" onclick="toggleDropdown('profileDropdown')">
                        <span class="profile-avatar-outline">
                            <?php
                            // Fetch user profile picture for avatar
                            $stmt_avatar = $conn->prepare("SELECT profile_picture_url, name FROM Users WHERE user_id = ? LIMIT 1");
                            $stmt_avatar->bind_param('i', $user_id);
                            $stmt_avatar->execute();
                            $result_avatar = $stmt_avatar->get_result();
                            $user_avatar = $result_avatar->fetch_assoc();
                            $avatar_url = $user_avatar && $user_avatar['profile_picture_url'] ? htmlspecialchars($user_avatar['profile_picture_url']) : '';
                            $user_name = $user_avatar ? htmlspecialchars($user_avatar['name']) : 'Profile';
                            ?>
                            <?php if ($avatar_url): ?>
                                <img src="<?php echo $avatar_url; ?>" alt="Profile" class="profile-avatar-img">
                            <?php else: ?>
                                <i class="fas fa-user" style="color:#ea580c; font-size: 28px; display: block;"></i>
                            <?php endif; ?>
                        </span>
                    </a>
                    <div id="profileDropdown" class="dropdown-content profile-dropdown-content">
                        <div class="profile-dropdown-header">
                            <span class="profile-avatar-outline">
                                <?php if ($avatar_url): ?>
                                    <img src="<?php echo $avatar_url; ?>" alt="Profile" class="profile-avatar-img">
                                <?php else: ?>
                                    <i class="fas fa-user" style="color:#ea580c; font-size: 28px; display: block;"></i>
                                <?php endif; ?>
                            </span>
                            <div class="profile-dropdown-name"><?php echo $user_name; ?></div>
                        </div>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="navbar-brand">
                <a href="index.php" class="kapital-logo">
                    <img src="imgs/kapitalwhiteorange.svg" alt="Kapital Logo" class="kapital-logo-img" style="width:110px;height:60px;vertical-align:middle;">
                </a>
                <span class="brand-x">×</span>
                <a href="https://taraki.vercel.app" target="_blank" rel="noopener">
                    <img src="imgs/logo.png" alt="Taraki Logo" class="taraki-logo">
                </a>
            </div>
            <div class="navbar-center">
                <nav class="navbar-menu">
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="about-us.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about-us.php' ? 'active' : ''; ?>">About</a>
                </nav>
            </div>
            <div class="navbar-actions">
                <a href="sign_in.php" class="login-btn">Login</a>
                <a href="sign_up.php" class="getstarted-btn">Sign Up</a>
                <!-- <button onclick="openWaitlistModal()" class="getstarted-btn">Join Waitlist</button> -->
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Function to toggle only the profile dropdown
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const allDropdowns = document.querySelectorAll('.profile-dropdown-content');
            // Close all other profile dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.remove('show');
                }
            });
            // Toggle the clicked dropdown
            dropdown.classList.toggle('show');
        }
        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const profileDropdown = document.getElementById('profileDropdown');
            const isClickInside = event.target.closest('.profile-dropdown');
            if (!isClickInside && profileDropdown) {
                profileDropdown.classList.remove('show');
            }
        });

        // Function to mark the message as read and update badge count
        document.querySelectorAll('.message-item').forEach(function (item) {
            item.addEventListener('click', function () {
                const messageId = this.getAttribute('data-message-id');
                const wasUnread = this.classList.contains('unread');

                fetch('mark_message_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message_id: messageId }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.classList.remove('unread');
                            this.classList.add('read');
                            
                            // Update badge count if message was unread
                            if (wasUnread) {
                                updateMessageBadgeCount();
                            }
                        }
                    });
            });
        });

        // Function to update message badge count
        function updateMessageBadgeCount() {
            fetch('get_unread_count.php')
                .then(response => response.json())
                .then(data => {
                    // Find the messages dropdown by looking for the envelope icon
                    const envelopeIcon = document.querySelector('.navbar-actions .fa-envelope');
                    const messagesDropdown = envelopeIcon ? envelopeIcon.closest('.dropdown-container') : null;
                    const badge = messagesDropdown ? messagesDropdown.querySelector('.badge') : null;
                    
                    if (data.unread_count > 0) {
                        if (badge) {
                            badge.textContent = data.unread_count;
                        } else {
                            // Create badge if it doesn't exist
                            const iconBtn = messagesDropdown ? messagesDropdown.querySelector('.icon-btn') : null;
                            if (iconBtn) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'badge';
                                newBadge.textContent = data.unread_count;
                                iconBtn.appendChild(newBadge);
                            }
                        }
                    } else {
                        // Remove badge if no unread messages
                        if (badge) {
                            badge.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating badge count:', error);
                });
        }

        // Function to mark the notification as read
        document.querySelectorAll('.notification-item').forEach(function (item) {
            item.addEventListener('click', function () {
                const notificationId = this.getAttribute('data-notification-id');

                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        this.classList.add('read');
                    }
                });
            });
        });

        // Function to set active class on the current page
        function setActiveLink() {
            const currentPage = window.location.pathname;
            const navLinks = document.querySelectorAll('nav ul li a');
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (currentPage.includes(linkPath)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }

        // Adjust dropdown positioning based on screen height
        function adjustDropdownPosition() {
            const dropdowns = document.querySelectorAll('.dropdown-container');
            dropdowns.forEach(function (dropdown) {
                const dropdownContent = dropdown.querySelector('.dropdown-content');
                const rect = dropdown.getBoundingClientRect();
                const windowHeight = window.innerHeight;

                // If the dropdown overflows the bottom of the window, show it above
                if (rect.bottom + dropdownContent.offsetHeight > windowHeight) {
                    dropdownContent.classList.add('dropdown-content-upward');
                } else {
                    dropdownContent.classList.remove('dropdown-content-upward');
                }
            });
        }

        // Call the function after the page loads or when resizing
        window.addEventListener('resize', adjustDropdownPosition);
        window.addEventListener('load', adjustDropdownPosition);
        setActiveLink(); // Set active class on page load

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`search.php?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="result-item"><div class="result-content">Error: ${data.error}</div></div>`;
                                searchResults.style.display = 'block';
                                return;
                            }
                            
                            if (Array.isArray(data) && data.length > 0) {
                                displaySearchResults(data);
                            } else {
                                searchResults.innerHTML = '<div class="result-item"><div class="result-content">No results found</div></div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            searchResults.innerHTML = `<div class="result-item"><div class="result-content">Error: ${error.message}</div></div>`;
                            searchResults.style.display = 'block';
                        });
                }, 300);
            });
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            if (searchResults && !searchResults.contains(event.target) && event.target !== searchInput) {
                searchResults.style.display = 'none';
            }
        });

        // Mobile Menu Toggle with Overlay
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navContainer = document.querySelector('.nav-container');
            const menuOverlay = document.querySelector('.menu-overlay');
            const dropdowns = document.querySelectorAll('.dropdown-container');

            function toggleMenu() {
                navContainer.classList.toggle('active');
                menuOverlay.classList.toggle('active');
                const isOpen = navContainer.classList.contains('active');
                mobileMenuBtn.innerHTML = isOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
                document.body.style.overflow = isOpen ? 'hidden' : '';
            }

            // Close dropdowns when menu is closed
            function closeDropdowns() {
                dropdowns.forEach(dropdown => {
                    const content = dropdown.querySelector('.dropdown-content');
                    if (content) {
                        content.style.display = 'none';
                    }
                });
            }

            mobileMenuBtn.addEventListener('click', toggleMenu);
            menuOverlay.addEventListener('click', () => {
                toggleMenu();
                closeDropdowns();
            });

            // Handle dropdowns in mobile view
            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.icon-btn, .dropdown-btn');
                const content = dropdown.querySelector('.dropdown-content');
                
                if (trigger && content) {
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Close other dropdowns
                        dropdowns.forEach(otherDropdown => {
                            if (otherDropdown !== dropdown) {
                                const otherContent = otherDropdown.querySelector('.dropdown-content');
                                if (otherContent) {
                                    otherContent.style.display = 'none';
                                }
                            }
                        });

                        // Toggle current dropdown
                        const isVisible = content.style.display === 'block';
                        content.style.display = isVisible ? 'none' : 'block';
                    });
                }
            });

            // Close menu and dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.nav-container') && 
                    !event.target.closest('.mobile-menu-btn') && 
                    navContainer.classList.contains('active')) {
                    toggleMenu();
                    closeDropdowns();
                }
            });

            // Close menu on window resize if open
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && navContainer.classList.contains('active')) {
                    toggleMenu();
                    closeDropdowns();
                }
            });

            // Handle search results in mobile view
            if (searchInput && searchResults) {
                searchInput.addEventListener('focus', () => {
                    if (window.innerWidth <= 768) {
                        searchResults.style.position = 'fixed';
                        searchResults.style.top = '60px';
                        searchResults.style.left = '0';
                        searchResults.style.width = '100%';
                        searchResults.style.maxHeight = 'calc(100vh - 60px)';
                        searchResults.style.zIndex = '1001';
                    }
                });

                searchInput.addEventListener('blur', () => {
                    if (window.innerWidth <= 768) {
                        searchResults.style.position = '';
                        searchResults.style.top = '';
                        searchResults.style.left = '';
                        searchResults.style.width = '';
                        searchResults.style.maxHeight = '';
                        searchResults.style.zIndex = '';
                    }
                });
            }
        });

        // Add displaySearchResults function for search dropdown
        function displaySearchResults(data) {
            const searchResults = document.getElementById('searchResults');
            if (!searchResults) return;

            if (!Array.isArray(data) || data.length === 0) {
                searchResults.innerHTML = '<div class="result-item"><div class="result-content">No results found</div></div>';
                searchResults.style.display = 'block';
                return;
            }

            let html = '';
            data.forEach(item => {
                if (item.type === 'User') {
                    html += `<div class="result-item">
                        <a href="profile.php?user_id=${item.user_id}">
                            <span class="result-icon"><i class="fas fa-user"></i></span>
                            <span class="result-content">
                                <span class="result-title">${item.title}</span>
                                <span class="result-subtitle">${item.subtitle}</span>
                            </span>
                            <span class="result-type">${item.type}</span>
                        </a>
                    </div>`;
                } else if (item.type === 'Startup') {
                    html += `<div class="result-item">
                        <a href="startup_detail.php?startup_id=${item.startup_id}">
                            <span class="result-icon"><i class="fas fa-building"></i></span>
                            <span class="result-content">
                                <span class="result-title">${item.name}</span>
                                <span class="result-subtitle">${item.industry || ''}</span>
                            </span>
                            <span class="result-type">${item.type}</span>
                        </a>
                    </div>`;
                } else if (item.type === 'Job') {
                    html += `<div class="result-item">
                        <a href="job_detail.php?job_id=${item.job_id}">
                            <span class="result-icon"><i class="fas fa-briefcase"></i></span>
                            <span class="result-content">
                                <span class="result-title">${item.title}</span>
                                <span class="result-subtitle">${item.company || ''} - ${item.location || ''}</span>
                            </span>
                            <span class="result-type">${item.type}</span>
                        </a>
                    </div>`;
                }
            });

            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }
    </script>
</body>

</html>