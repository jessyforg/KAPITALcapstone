<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db_connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$is_settings = ($current_page === 'settings.php');
$active_section = isset($active_section) ? $active_section : 'startups'; // Set this in dashboard pages
$dashboard_file = [
    'entrepreneur' => 'entrepreneurs.php',
    'investor' => 'investors.php',
    'job_seeker' => 'job-seekers.php',
    'admin' => 'admin-panel.php'
];

// Fetch user info for profile section
$user = ['name' => '', 'profile_picture_url' => ''];
if ($user_id) {
    $user_query = "SELECT name, profile_picture_url FROM Users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result) ?: $user;
}
?>
<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="sidebar-profile" style="margin-top: 32px;">
        <div class="sidebar-avatar">
            <?php if (!empty($user['profile_picture_url']) && file_exists($user['profile_picture_url'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?> profile picture">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
        <div class="sidebar-username">
            <?php echo htmlspecialchars($user['name']); ?>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php if ($role === 'entrepreneur'): ?>
            <a href="entrepreneurs.php?section=startups" class="sidebar-link<?php echo ($active_section === 'startups') ? ' active' : ''; ?>"><i class="fas fa-rocket"></i> <span>Startups</span></a>
            <a href="entrepreneurs.php?section=cofounders" class="sidebar-link<?php echo ($active_section === 'cofounders') ? ' active' : ''; ?>"><i class="fas fa-user-tie"></i> <span>Co-Founders</span></a>
            <a href="entrepreneurs.php?section=investors" class="sidebar-link<?php echo ($active_section === 'investors') ? ' active' : ''; ?>"><i class="fas fa-hand-holding-usd"></i> <span>Investors</span></a>
            <a href="entrepreneurs.php?section=job-seekers" class="sidebar-link<?php echo ($active_section === 'job-seekers') ? ' active' : ''; ?>"><i class="fas fa-users"></i> <span>Job Seekers</span></a>
        <?php elseif ($role === 'investor'): ?>
            <a href="investors.php?section=startups" class="sidebar-link<?php echo ($active_section === 'startups') ? ' active' : ''; ?>"><i class="fas fa-rocket"></i> <span>Startups</span></a>
            <a href="investors.php?section=entrepreneurs" class="sidebar-link<?php echo ($active_section === 'entrepreneurs') ? ' active' : ''; ?>"><i class="fas fa-user-tie"></i> <span>Entrepreneurs</span></a>
            <a href="investors.php?section=investors" class="sidebar-link<?php echo ($active_section === 'investors') ? ' active' : ''; ?>"><i class="fas fa-hand-holding-usd"></i> <span>Investors</span></a>
        <?php elseif ($role === 'job_seeker'): ?>
            <a href="job-seekers.php?section=startups" class="sidebar-link<?php echo ($active_section === 'startups') ? ' active' : ''; ?>"><i class="fas fa-rocket"></i> <span>Startups</span></a>
            <a href="job-seekers.php?section=entrepreneurs" class="sidebar-link<?php echo ($active_section === 'entrepreneurs') ? ' active' : ''; ?>"><i class="fas fa-user-tie"></i> <span>Entrepreneurs</span></a>
            <a href="job-seekers.php?section=jobs" class="sidebar-link<?php echo ($active_section === 'jobs') ? ' active' : ''; ?>"><i class="fas fa-briefcase"></i> <span>Jobs</span></a>
            <a href="job-seekers.php?section=job-seekers" class="sidebar-link<?php echo ($active_section === 'job-seekers') ? ' active' : ''; ?>"><i class="fas fa-users"></i> <span>Job Seekers</span></a>
        <?php elseif ($role === 'admin'): ?>
            <a href="admin-panel.php?section=startup-applications" class="sidebar-link<?php echo ($active_section === 'startup-applications') ? ' active' : ''; ?>"><i class="fas fa-rocket"></i> <span>Startup Applications</span></a>
            <a href="admin-panel.php?section=user-verifications" class="sidebar-link<?php echo ($active_section === 'user-verifications') ? ' active' : ''; ?>"><i class="fas fa-id-badge"></i> <span>User Verifications</span></a>
            <a href="admin-panel.php?section=job-verifications" class="sidebar-link<?php echo ($active_section === 'job-verifications') ? ' active' : ''; ?>"><i class="fas fa-briefcase"></i> <span>Job Verifications</span></a>
            <a href="admin-panel.php?section=users-list" class="sidebar-link<?php echo ($active_section === 'users-list') ? ' active' : ''; ?>"><i class="fas fa-users"></i> <span>Users List</span></a>
            <a href="admin-panel.php?section=tickets" class="sidebar-link<?php echo ($active_section === 'tickets') ? ' active' : ''; ?>"><i class="fas fa-ticket-alt"></i> <span>Tickets</span></a>
            <a href="messages.php" class="sidebar-link<?php echo ($current_page === 'messages.php') ? ' active' : ''; ?>"><i class="fas fa-envelope"></i> <span>Messages</span></a>
            <a href="about-us.php" class="sidebar-link"><i class="fas fa-info-circle"></i> <span>About Us</span></a>
            <a href="settings.php" class="sidebar-link<?php echo $is_settings ? ' active' : ''; ?>"><i class="fas fa-cog"></i> <span>Settings</span></a>
        <?php else: ?>
            <a href="messages.php" class="sidebar-link<?php echo ($current_page === 'messages.php') ? ' active' : ''; ?>"><i class="fas fa-envelope"></i> <span>Messages</span></a>
            <a href="about-us.php" class="sidebar-link"><i class="fas fa-info-circle"></i> <span>About Us</span></a>
            <a href="settings.php" class="sidebar-link<?php echo $is_settings ? ' active' : ''; ?>"><i class="fas fa-cog"></i> <span>Settings</span></a>
        <?php endif; ?>
        <a href="logout.php" class="sidebar-link logout-link"><i class="fas fa-sign-out-alt logout-icon"></i> <span>Logout</span></a>
    </nav>
</div>
<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
}
</script>
<style>
.sidebar {
    margin-top: 80px; /* Adjust based on navbar height */
    width: 260px;
    background: rgba(36, 37, 38, 0.85); /* semi-transparent */
    backdrop-filter: blur(12px) saturate(180%);
    -webkit-backdrop-filter: blur(12px) saturate(180%);
    border-radius: 24px;
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
    padding: 0;
    min-height: 80vh;
    border: none;
    position: fixed;
    top: 16px;
    left: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: width 0.3s;
    box-sizing: border-box;
    z-index: 999; /* Higher than navbar */
}
.sidebar.collapsed {
    width: 0 !important;
    min-width: 0 !important;
    overflow: hidden;
    padding: 0 !important;
}
.sidebar-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 32px;
}
.sidebar-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #3a3b3c;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 12px;
    font-size: 60px;
    color: #ea580c;
}
.sidebar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
}
.sidebar-username {
    color: #fff;
    font-weight: 600;
    font-size: 1.1rem;
    text-align: center;
}
.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 32px;
}
.brand-x {
    color: #ea580c;
    font-size: 1.5rem;
    margin-right: 8px;
}
.taraki-logo {
    width: 120px;
    height: 40px;
    object-fit: contain;
}
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    padding: 0 24px;
}
.sidebar-link {
    color: #e4e6eb;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s, color 0.2s;
    background: none;
}
.sidebar-link.active {
    background: #3a3b3c;
    color: #ea580c;
}
.sidebar-link:not(.active):hover {
    background: #23272a;
    color: #ea580c;
}
.sidebar-link.logout-link {
    color: #ff4d4f !important;
    font-weight: 600 !important;
}
.sidebar-link.logout-link:hover {
    background: rgba(255, 77, 79, 0.12) !important;
    color: #fff !important;
}
.logout-icon {
    color: #ff4d4f !important;
}
/* Sidebar toggle button */
.sidebar-toggle {
    display: none;
    position: absolute;
    top: 16px;
    left: 16px;
    background: none;
    border: none;
    color: #ea580c;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 10;
}
@media (max-width: 900px) {
    .sidebar {
        width: 70px;
        padding: 0;
        top: 16px;
        left: 8px;
        border-radius: 20px;
    }
    .sidebar-profile {
        margin-bottom: 16px;
    }
    .sidebar-username {
        display: none;
    }
    .sidebar-link {
        justify-content: center;
        padding: 12px 0;
        font-size: 1.2rem;
    }
    .sidebar-link span {
        display: none;
    }
}
@media (max-width: 700px) {
    .sidebar {
        flex-direction: row;
        width: 100vw;
        height: 60px;
        min-height: 0;
        border-right: none;
        border-bottom: 1px solid #3a3b3c;
        padding: 0;
        align-items: center;
        justify-content: space-between;
        top: 0;
        left: 0;
        border-radius: 0;
        background: #242526;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        box-shadow: none;
    }
    .sidebar-profile {
        flex-direction: row;
        margin-bottom: 0;
        margin-left: 16px;
    }
    .sidebar-avatar {
        width: 40px;
        height: 40px;
        font-size: 28px;
        margin-bottom: 0;
    }
    .sidebar-nav {
        flex-direction: row;
        gap: 0;
        padding: 0 8px;
        width: auto;
    }
    .sidebar-link {
        padding: 8px 10px;
        font-size: 1.1rem;
    }
}
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        top: 0;
        left: 0;
        border-radius: 0;
    }
    .sidebar-nav {
        flex-direction: row;
        overflow-x: auto;
    }
}
</style> 