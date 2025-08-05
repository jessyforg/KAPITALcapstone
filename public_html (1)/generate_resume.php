<?php
session_start();
include('db_connection.php');
include('navbar.php');
require_once('ai_resume_helper.php');
require_once('config.php');

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker' || !isset($_SESSION['resume_data'])) {
    header("Location: resume_builder.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$resume_data = $_SESSION['resume_data'];

// Initialize AI helper
$ai_helper = new AIResumeHelper($conn);

try {
    // Generate AI-enhanced content
    $professional_summary = $ai_helper->generateProfessionalSummary(
        $resume_data['work_experience'],
        $resume_data['skills'],
        $resume_data['desired_role'],
        $user_id
    );

    $enhanced_experience = $ai_helper->enhanceWorkExperience(
        $resume_data['work_experience'],
        $resume_data['desired_role'],
        $user_id
    );

    $optimized_skills = $ai_helper->optimizeSkills(
        $resume_data['skills'],
        $resume_data['desired_role'],
        $user_id
    );

    $enhanced_achievements = $ai_helper->enhanceAchievements(
        $resume_data['achievements'],
        $resume_data['desired_role'],
        $user_id
    );

    // Store the enhanced data
    $enhanced_data = [
        'professional_summary' => $professional_summary,
        'work_experience' => $enhanced_experience,
        'skills' => $optimized_skills,
        'achievements' => $enhanced_achievements
    ];

} catch (Exception $e) {
    // If AI enhancement fails, use original content
    $enhanced_data = [
        'professional_summary' => "Experienced {$resume_data['desired_role']} with a proven track record of success...",
        'work_experience' => $resume_data['work_experience'],
        'skills' => $resume_data['skills'],
        'achievements' => $resume_data['achievements']
    ];
}

// Helper function to extract only the resume content from AI output
function extractResumeContent($text, $section = 'work') {
    if ($section === 'work') {
        // Try to extract from first **Position:** or similar marker
        if (preg_match('/(\*\*Position:\*\*.*)/s', $text, $matches)) {
            return $matches[1];
        }
    } elseif ($section === 'achievements') {
        // Try to extract from first bullet or similar marker
        if (preg_match('/(\*\*Achievement[s]?:\*\*.*)/s', $text, $matches)) {
            return $matches[1];
        }
        // Or from first dash
        if (preg_match('/(- .*)/s', $text, $matches)) {
            return $matches[1];
        }
    } elseif ($section === 'summary') {
        // Try to extract from first paragraph after any intro
        $lines = preg_split('/\r?\n/', $text);
        foreach ($lines as $line) {
            if (trim($line) !== '' && strpos($line, 'Certainly!') === false && strpos($line, 'Here') === false) {
                return $line;
            }
        }
    }
    // Fallback: return the whole text
    return $text;
}

// Clean AI output for resume sections
$work_experience_clean = extractResumeContent($enhanced_data['work_experience'], 'work');
$achievements_clean = extractResumeContent($enhanced_data['achievements'], 'achievements');
$professional_summary_clean = extractResumeContent($enhanced_data['professional_summary'], 'summary');

// Generate the resume HTML
$resume_html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Resume - ' . htmlspecialchars($resume_data['full_name']) . '</title>
    <style>
        body {
            font-family: "Arial", sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .contact-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .skill-item {
            background-color: #f0f0f0;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        @media print {
            body {
                padding: 0;
                margin: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($resume_data['full_name']) . '</h1>
        <div class="contact-info">
            <p>
                ' . htmlspecialchars($resume_data['email']) . ' | 
                ' . htmlspecialchars($resume_data['phone']) . '
            </p>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Professional Summary</h2>
        <p>' . nl2br(htmlspecialchars($professional_summary_clean)) . '</p>
    </div>

    <div class="section">
        <h2 class="section-title">Work Experience</h2>
        ' . nl2br(htmlspecialchars($work_experience_clean)) . '
    </div>

    <div class="section">
        <h2 class="section-title">Education</h2>
        ' . nl2br(htmlspecialchars($resume_data['education'])) . '
    </div>

    <div class="section">
        <h2 class="section-title">Skills</h2>
        <div class="skills-list">
            ' . implode('', array_map(function($skill) {
                return '<span class="skill-item">' . htmlspecialchars(trim($skill)) . '</span>';
            }, explode(',', $enhanced_data['skills']))) . '
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Achievements & Certifications</h2>
        ' . nl2br(htmlspecialchars($achievements_clean)) . '
    </div>
</body>
</html>
';

// Save the resume HTML to a temporary file
$temp_dir = 'uploads/resumes/temp/';
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

$filename = 'resume_' . $user_id . '_' . time() . '.html';
$filepath = $temp_dir . $filename;
file_put_contents($filepath, $resume_html);

// Clear the session resume data
unset($_SESSION['resume_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Generated - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #1a1a1a;
            color: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(243, 192, 0, 0.2);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        h1 {
            color: #f3c000;
            margin-bottom: 30px;
        }

        .success-message {
            color: #4caf50;
            background-color: rgba(46, 125, 50, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .error-message {
            color: #ff5252;
            background-color: rgba(211, 47, 47, 0.2);
            border: 1px solid rgba(255, 82, 82, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .suggestions {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: rgba(243, 192, 0, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(243, 192, 0, 0.2);
        }

        .suggestions h3 {
            color: #f3c000;
            margin-bottom: 15px;
        }

        .suggestions ul {
            list-style-type: none;
            padding: 0;
        }

        .suggestions li {
            margin-bottom: 12px;
            padding-left: 25px;
            position: relative;
            color: rgba(255, 255, 255, 0.9);
        }

        .suggestions li:before {
            content: "•";
            color: #f3c000;
            font-weight: bold;
            position: absolute;
            left: 0;
            font-size: 1.2em;
        }

        .suggestion-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #f3c000, #ffab00);
            color: #000;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(243, 192, 0, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 192, 0, 0.3);
        }

        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid rgba(243, 192, 0, 0.2);
            border-radius: 10px;
            margin-top: 20px;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-check-circle"></i> Resume Generated Successfully</h1>
        
        <div class="success-message">
            <i class="fas fa-magic"></i> Your resume has been generated with AI enhancements. You can preview it below.
        </div>

        <iframe src="<?php echo $filepath; ?>" title="Resume Preview"></iframe>

        <div class="button-group">
            <a href="<?php echo $filepath; ?>" download class="btn btn-primary">
                <i class="fas fa-download"></i> Download Resume
            </a>
            <a href="resume_builder.php" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Create New Resume
            </a>
        </div>
    </div>
</body>
</html> 