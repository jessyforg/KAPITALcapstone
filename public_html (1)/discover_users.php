<?php
session_start();
include('navbar.php');
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Fetch all entrepreneurs except the current user
$current_user_id = $_SESSION['user_id'];

// First get the current user's industry and role
$current_user_query = "SELECT industry, role FROM Users WHERE user_id = ?";
$current_user_stmt = mysqli_prepare($conn, $current_user_query);
mysqli_stmt_bind_param($current_user_stmt, "i", $current_user_id);
mysqli_stmt_execute($current_user_stmt);
$current_user_result = mysqli_stmt_get_result($current_user_stmt);
$current_user = mysqli_fetch_assoc($current_user_result);
$current_industry = $current_user['industry'];
$current_role = $current_user['role'];

// Function to format role names
function formatRoleName($role) {
    if ($role === 'admin') {
        return 'TARAKI Admin';
    }
    $role = str_replace('_', ' ', $role);
    $role = ucwords($role);
    if (substr($role, -1) === 's') {
        return $role;
    }
    return $role;
}

// Main query with role-based filtering and industry similarity sorting
$query = "SELECT DISTINCT u.*, 
    u.industry as primary_industry,
    COALESCE(u.introduction, '') as about_me,
    GROUP_CONCAT(DISTINCT s.name) as startup_names,
    GROUP_CONCAT(DISTINCT s.industry) as startup_industries,
    CASE 
        WHEN u.industry = ? THEN 1
        WHEN u.industry COLLATE utf8mb4_unicode_ci LIKE CONCAT(?, '%') OR u.industry COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', ?) THEN 2
        ELSE 3
    END as industry_match
FROM Users u
LEFT JOIN Startups s ON u.user_id = s.entrepreneur_id AND s.approval_status = 'approved'
WHERE u.user_id != ? 
AND u.verification_status = 'verified'";

// Add role-based filtering
if ($current_role === 'entrepreneur') {
    $query .= " AND u.role = 'entrepreneur'";
} else {
    $query .= " AND u.role != 'entrepreneur'";
}

$query .= " GROUP BY u.user_id
ORDER BY industry_match ASC, u.name ASC";

if (!empty($search)) {
    $query .= " AND (u.name COLLATE utf8mb4_unicode_ci LIKE ? OR u.introduction COLLATE utf8mb4_unicode_ci LIKE ?)";
}

$stmt = mysqli_prepare($conn, $query);

if (!empty($search)) {
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "sssiss", $current_industry, $current_industry, $current_industry, $current_user_id, $search_param, $search_param);
} else {
    mysqli_stmt_bind_param($stmt, "sssi", $current_industry, $current_industry, $current_industry, $current_user_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Other Users - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #343131, #808080);
            background-attachment: fixed;
            min-height: 100vh;
            color: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #ea580c;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 40px;
            padding: 30px;
            max-width: 1800px;
            margin: 0 auto;
        }

        .user-card {
            background: #2C2F33;
            border: 1px solid #40444B;
            border-radius: 15px;
            padding: 30px;
            margin: 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: auto;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .user-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-info {
            flex: 1;
        }

        .user-info h3 {
            margin: 0;
            color: #FFFFFF;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: rgba(234, 88, 12, 0.1);
            color: #ea580c;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .user-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #B9BBBE;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .user-detail i {
            color: #ea580c;
            width: 16px;
            text-align: center;
        }

        .user-about {
            color: #DCDDDE;
            font-size: 0.95em;
            line-height: 1.5;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #40444B;
        }

        .user-info h2,
        .user-role,
        .user-bio,
        .view-profile-btn {
            display: none;
        }

        .user-actions {
            margin-top: auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 20px;
        }

        .action-button {
            flex: 1;
            min-width: 120px;
            background: linear-gradient(45deg, #ea580c, #c2410c);
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(234, 88, 12, 0.3);
            background: linear-gradient(45deg, #c2410c, #ea580c);
        }

        .action-button i {
            font-size: 1.1em;
        }

        .action-button.message-btn {
            background: linear-gradient(45deg, #7289DA, #5865F2);
            color: #fff;
        }

        .action-button.message-btn:hover {
            background: linear-gradient(45deg, #5865F2, #7289DA);
            box-shadow: 0 5px 15px rgba(114, 137, 218, 0.3);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            background: #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info h2 {
            margin: 0;
            font-size: 1.2rem;
            color: #fff;
        }

        .user-role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-top: 5px;
            background: #ea580c;
            color: #fff;
        }

        .user-bio {
            margin: 15px 0;
            font-size: 0.9rem;
            color: #ddd;
            line-height: 1.6;
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            background: rgba(0, 0, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
        }

        .view-profile-btn {
            display: inline-block;
            padding: 8px 20px;
            background: #ea580c;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }

        .view-profile-btn:hover {
            background: #c2410c;
        }

        @media (min-width: 1920px) {
            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 30px;
                padding: 20px;
            }
            
            .user-card {
                min-height: 250px;
            }
        }

        @media (max-width: 480px) {
            .user-header {
                flex-direction: column;
                text-align: center;
            }

            .user-avatar {
                margin: 0 auto 15px;
            }

            .user-info {
                text-align: center;
            }

            .user-detail {
                justify-content: center;
            }
        }

        .startup-info {
            background: rgba(0, 0, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .startup-info h4 {
            color: #ea580c;
            margin: 0 0 5px 0;
            font-size: 1.1em;
        }

        .startup-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .startup-list li {
            color: #B9BBBE;
            font-size: 0.9em;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .startup-list li:before {
            content: "•";
            color: #ea580c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $current_role === 'entrepreneur' ? 'Discover Co-Entrepreneurs' : 'Discover Other Users'; ?></h1>
        
        <div class="users-grid">
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <div class="user-card">
                    <div class="user-header">
                        <div class="user-avatar">
                            <?php if (!empty($user['profile_picture_url'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture_url']); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <span class="role-badge">
                                <i class="fas fa-lightbulb"></i>
                                <?php echo formatRoleName(htmlspecialchars($user['role'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['primary_industry'])): ?>
                    <div class="user-detail">
                        <i class="fas fa-briefcase"></i>
                        <?php echo htmlspecialchars($user['primary_industry']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($user['location'])): ?>
                    <div class="user-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($user['location']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['startup_names'])): ?>
                    <div class="startup-info">
                        <h4><i class="fas fa-building"></i> Startups</h4>
                        <ul class="startup-list">
                            <?php 
                            $startup_names = explode(',', $user['startup_names']);
                            $startup_industries = explode(',', $user['startup_industries']);
                            for ($i = 0; $i < count($startup_names); $i++): 
                            ?>
                                <li>
                                    <?php echo htmlspecialchars($startup_names[$i]); ?>
                                    <span style="color: #7289DA;">(<?php echo htmlspecialchars($startup_industries[$i]); ?>)</span>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['about_me'])): ?>
                    <div class="user-about">
                        <?php 
                        $about = htmlspecialchars($user['about_me']);
                        echo strlen($about) > 150 ? substr($about, 0, 147) . '...' : $about;
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="user-actions">
                        <a href="profile.php?user_id=<?php echo $user['user_id']; ?>" class="action-button">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        <a href="messages.php?recipient_id=<?php echo $user['user_id']; ?>" class="action-button message-btn">
                            <i class="fas fa-comments"></i> Message
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html> 