<?php
session_start(); // Start the session
include('navbar.php');
include('db_connection.php'); // Assuming a separate file for database connection

// Redirect if the user is not logged in or does not have the entrepreneur role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entrepreneur') {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Check if the entrepreneur exists in the Entrepreneurs table
$query = "SELECT entrepreneur_id FROM Entrepreneurs WHERE entrepreneur_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    die("Entrepreneur profile not found. Please ensure you have registered as an entrepreneur.");
}

// Function to handle file uploads
function uploadFile($file, $upload_dir, $allowed_types)
{
    if (!empty($file["name"])) {
        $file_name = basename($file["name"]);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . "_" . $file_name;
        $target_file = $upload_dir . '/' . $new_file_name;

        if (!in_array($file_ext, $allowed_types)) {
            return ["success" => false, "message" => "Invalid file type: " . $file_ext, "path" => ""];
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            // Return relative path for database storage
            $relative_path = 'uploads/' . basename($upload_dir) . '/' . $new_file_name;
            return ["success" => true, "path" => $relative_path];
        } else {
            error_log("Upload failed for file: " . $file["name"] . " to " . $target_file);
            error_log("Upload error: " . error_get_last()['message']);
            return ["success" => false, "message" => "File upload failed.", "path" => ""];
        }
    }
    return ["success" => true, "path" => ""]; // No file uploaded
}

// Define industries
$industries = [
    'Technology' => [
        'Software Development',
        'E-commerce',
        'FinTech',
        'EdTech',
        'HealthTech',
        'AI/ML',
        'Cybersecurity',
        'Cloud Computing',
        'Digital Marketing',
        'Mobile Apps'
    ],
    'Healthcare' => [
        'Medical Services',
        'Healthcare Technology',
        'Wellness & Fitness',
        'Mental Health',
        'Telemedicine',
        'Medical Devices',
        'Healthcare Analytics'
    ],
    'Finance' => [
        'Banking',
        'Insurance',
        'Investment',
        'Financial Services',
        'Payment Solutions',
        'Cryptocurrency',
        'Financial Planning'
    ],
    'Education' => [
        'Online Learning',
        'Educational Technology',
        'Skills Training',
        'Language Learning',
        'Professional Development',
        'Educational Content'
    ],
    'Retail' => [
        'E-commerce',
        'Fashion',
        'Food & Beverage',
        'Consumer Goods',
        'Marketplace',
        'Retail Technology'
    ],
    'Manufacturing' => [
        'Industrial Manufacturing',
        'Clean Technology',
        '3D Printing',
        'Supply Chain',
        'Smart Manufacturing'
    ],
    'Agriculture' => [
        'AgTech',
        'Organic Farming',
        'Food Processing',
        'Agricultural Services',
        'Sustainable Agriculture'
    ],
    'Transportation' => [
        'Logistics',
        'Ride-sharing',
        'Delivery Services',
        'Transportation Technology',
        'Smart Mobility'
    ],
    'Real Estate' => [
        'Property Technology',
        'Real Estate Services',
        'Property Management',
        'Real Estate Investment',
        'Smart Homes'
    ],
    'Other' => [
        'Social Impact',
        'Environmental',
        'Creative Industries',
        'Sports & Entertainment',
        'Other Services'
    ]
];

// Define Philippine regions and cities
$locations = [
    'National Capital Region (NCR)' => [
        'Manila',
        'Quezon City',
        'Caloocan',
        'Las Piñas',
        'Makati',
        'Malabon',
        'Mandaluyong',
        'Marikina',
        'Muntinlupa',
        'Navotas',
        'Parañaque',
        'Pasay',
        'Pasig',
        'Pateros',
        'San Juan',
        'Taguig',
        'Valenzuela',
        'Pateros'
    ],
    'Cordillera Administrative Region (CAR)' => [
        'Baguio City',
        'Tabuk City',
        'La Trinidad',
        'Bangued',
        'Lagawe',
        'Bontoc'
    ],
    'Ilocos Region (Region I)' => [
        'San Fernando City',
        'Laoag City',
        'Vigan City',
        'Dagupan City',
        'San Carlos City',
        'Urdaneta City'
    ],
    'Cagayan Valley (Region II)' => [
        'Tuguegarao City',
        'Cauayan City',
        'Santiago City',
        'Ilagan City'
    ],
    'Central Luzon (Region III)' => [
        'San Fernando City',
        'Angeles City',
        'Olongapo City',
        'Malolos City',
        'Cabanatuan City',
        'San Jose City',
        'Science City of Muñoz',
        'Palayan City'
    ],
    'CALABARZON (Region IV-A)' => [
        'Calamba City',
        'San Pablo City',
        'Antipolo City',
        'Batangas City',
        'Cavite City',
        'Lipa City',
        'San Pedro',
        'Santa Rosa',
        'Tagaytay City',
        'Trece Martires City'
    ],
    'MIMAROPA (Region IV-B)' => [
        'Calapan City',
        'Puerto Princesa City',
        'San Jose',
        'Romblon'
    ],
    'Bicol Region (Region V)' => [
        'Naga City',
        'Legazpi City',
        'Iriga City',
        'Ligao City',
        'Tabaco City',
        'Sorsogon City'
    ],
    'Western Visayas (Region VI)' => [
        'Iloilo City',
        'Bacolod City',
        'Roxas City',
        'Passi City',
        'Silay City',
        'Talisay City',
        'Escalante City',
        'Sagay City',
        'Cadiz City',
        'Bago City',
        'La Carlota City',
        'Kabankalan City',
        'San Carlos City',
        'Sipalay City',
        'Himamaylan City'
    ],
    'Central Visayas (Region VII)' => [
        'Cebu City',
        'Mandaue City',
        'Lapu-Lapu City',
        'Talisay City',
        'Toledo City',
        'Dumaguete City',
        'Bais City',
        'Bayawan City',
        'Canlaon City',
        'Guihulngan City',
        'Tanjay City'
    ],
    'Eastern Visayas (Region VIII)' => [
        'Tacloban City',
        'Ormoc City',
        'Calbayog City',
        'Catbalogan City',
        'Maasin City',
        'Baybay City',
        'Borongan City'
    ],
    'Zamboanga Peninsula (Region IX)' => [
        'Zamboanga City',
        'Dipolog City',
        'Dapitan City',
        'Isabela City',
        'Pagadian City'
    ],
    'Northern Mindanao (Region X)' => [
        'Cagayan de Oro City',
        'Iligan City',
        'Oroquieta City',
        'Ozamiz City',
        'Tangub City',
        'Gingoog City',
        'El Salvador',
        'Malaybalay City',
        'Valencia City'
    ],
    'Davao Region (Region XI)' => [
        'Davao City',
        'Digos City',
        'Mati City',
        'Panabo City',
        'Samal City',
        'Tagum City'
    ],
    'SOCCSKSARGEN (Region XII)' => [
        'Koronadal City',
        'Cotabato City',
        'General Santos City',
        'Kidapawan City',
        'Tacurong City'
    ],
    'Caraga (Region XIII)' => [
        'Butuan City',
        'Surigao City',
        'Bislig City',
        'Tandag City',
        'Bayugan City',
        'Cabadbaran City'
    ],
    'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' => [
        'Cotabato City',
        'Marawi City',
        'Lamitan City'
    ]
];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve the form data
    $startup_name = mysqli_real_escape_string($conn, $_POST['startup_name']);
    $industry = mysqli_real_escape_string($conn, $_POST['industry']);
    $funding_stage = mysqli_real_escape_string($conn, $_POST['funding_stage']);
    $startup_stage = mysqli_real_escape_string($conn, $_POST['startup_stage']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $website = filter_var($_POST['website'], FILTER_VALIDATE_URL) ? mysqli_real_escape_string($conn, $_POST['website']) : null;
    $pitch_deck_url = filter_var($_POST['pitch_deck_url'], FILTER_VALIDATE_URL) ? mysqli_real_escape_string($conn, $_POST['pitch_deck_url']) : null;
    $business_plan_url = filter_var($_POST['business_plan_url'], FILTER_VALIDATE_URL) ? mysqli_real_escape_string($conn, $_POST['business_plan_url']) : null;

    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {
        $logo_upload = uploadFile($_FILES["logo"], UPLOAD_LOGOS_DIR, ["jpg", "jpeg", "png"]);
        if (!$logo_upload["success"]) {
            echo "<script>alert('" . $logo_upload["message"] . "');</script>";
        } else {
            $logo_path = $logo_upload["path"];
        }
    }

    // Handle file upload (Video Pitch / General File)
    $file_path = null;
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        $file_upload = uploadFile($_FILES["file"], UPLOAD_FILES_DIR, ["mp4", "avi", "mov", "pdf", "docx", "pptx"]);
        if (!$file_upload["success"]) {
            echo "<script>alert('" . $file_upload["message"] . "');</script>";
        } else {
            $file_path = $file_upload["path"];
        }
    }

    // Insert startup record using prepared statement
    $query = "INSERT INTO Startups (
        entrepreneur_id, 
        name, 
        industry, 
        funding_stage,
        startup_stage,
        description, 
        location, 
        website, 
        pitch_deck_url, 
        business_plan_url, 
        logo_url, 
        video_url,
        approval_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("isssssssssss", 
            $user_id,
            $startup_name,
            $industry,
            $funding_stage,
            $startup_stage,
            $description,
            $location,
            $website,
            $pitch_deck_url,
            $business_plan_url,
            $logo_path,
            $file_path
        );
        
        if ($stmt->execute()) {
            echo "<script>alert('Startup profile created successfully!');</script>";
            // Redirect to prevent form resubmission
            header("Location: startup_ai_advisor.php");
            exit();
        } else {
            echo "<script>alert('Error creating startup profile: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Startup Profile - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #1a1a1a;
            color: #f9f9f9;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            margin-top: 100px;
            padding: 30px;
            background: #242424;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(234, 88, 12, 0.2);
            border: 1px solid #ea580c;
        }

        h1 {
            color: #ea580c;
            margin: 0 0 25px 0;
            font-size: 2em;
            font-weight: 600;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-size: 1rem;
            color: #ea580c;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            background-color: #2a2a2a;
            border: 1px solid #ea580c;
            border-radius: 8px;
            color: #f9f9f9;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff6b1a;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .container button {
            background-color: #ea580c;
            color: white;
            padding: 14px 28px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .container button:hover {
            background-color: #ff6b1a;
            transform: translateY(-2px);
        }

        .error-message {
            color: #ff4d4d;
            background: rgba(255, 77, 77, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Logo Upload Styles */
        .logo-upload {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 30px;
            cursor: pointer;
            text-align: center;
        }

        .logo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(234, 88, 12, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #ea580c;
            transition: all 0.3s ease;
            position: relative;
            padding: 10px;
        }

        .logo-preview:hover {
            border-color: #ff6b1a;
            background: rgba(234, 88, 12, 0.15);
        }

        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .logo-preview .default-logo-icon {
            font-size: 50px;
            color: #ea580c;
        }

        .logo-upload input {
            display: none;
        }

        .logo-label {
            font-size: 14px;
            color: #ea580c;
            margin-top: 10px;
            display: block;
            text-align: center;
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            background-color: #2a2a2a;
            border: 1px solid #ea580c;
            border-radius: 8px;
            color: #f9f9f9;
            height: 42px;
            overflow: hidden;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f9f9f9;
            line-height: 42px;
            padding-left: 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
        }

        .select2-container--default .select2-selection--single {
            position: relative;
        }

        .select2-container--default .select2-results__option {
            background-color: #2a2a2a;
            color: #f9f9f9;
            padding: 10px 15px;
            text-align: left;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ea580c;
            color: #f9f9f9;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #2a2a2a;
            color: #f9f9f9;
            border: 1px solid #ea580c;
            border-radius: 4px;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            outline: none;
            border-color: #ff6b1a;
        }

        .select2-dropdown {
            background-color: #2a2a2a;
            border: 1px solid #ea580c;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: auto;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #B9BBBE;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #ea580c transparent transparent transparent;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #ea580c transparent;
        }

        /* Style for optgroups */
        .select2-results__group {
            background-color: #23272A;
            color: #ea580c;
            font-weight: bold;
            padding: 8px 10px;
        }

        /* Style for options within optgroups */
        .select2-results__option {
            padding-left: 20px;
        }

        /* Custom scrollbar for Select2 dropdown */
        .select2-dropdown::-webkit-scrollbar {
            width: 8px;
        }

        .select2-dropdown::-webkit-scrollbar-track {
            background: #2a2a2a;
            border-radius: 4px;
        }

        .select2-dropdown::-webkit-scrollbar-thumb {
            background: #ea580c;
            border-radius: 4px;
        }

        .select2-dropdown::-webkit-scrollbar-thumb:hover {
            background: #ff6b1a;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                margin: 20px auto;
                padding: 20px;
            }

            .logo-upload {
                width: 120px;
                height: 120px;
                margin-bottom: 20px;
            }

            .logo-preview {
                width: 120px;
                height: 120px;
            }

            .logo-preview .default-logo-icon {
                font-size: 40px;
            }

            h1 {
                font-size: 1.6rem;
            }

            .form-group label {
                font-size: 0.95rem;
            }

            .container button {
                padding: 12px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Create Startup Profile</h1>
        <?php if (isset($error_message)) {
            echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $error_message</div>";
        } ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="logo-upload">
                <label for="logo">
                    <div class="logo-preview" id="logoPreview">
                        <i class="fas fa-building default-logo-icon"></i>
                    </div>
                </label>
                <span class="logo-label">Click to Upload Logo</span>
                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/jpg" onchange="previewLogo(this);">
            </div>

            <div class="form-group">
                <label for="startup_name"><i class="fas fa-building"></i> Startup Name</label>
                <input type="text" id="startup_name" name="startup_name" placeholder="Enter your startup's name" required>
            </div>

            <div class="form-group">
                <label for="industry"><i class="fas fa-industry"></i> Industry</label>
                <select id="industry" name="industry" class="select2" required>
                    <option value="">Select Industry</option>
                    <?php foreach ($industries as $category => $subcategories): ?>
                        <optgroup label="<?php echo htmlspecialchars($category); ?>">
                            <?php foreach ($subcategories as $subcategory): ?>
                                <option value="<?php echo htmlspecialchars($subcategory); ?>">
                                    <?php echo htmlspecialchars($subcategory); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="funding_stage"><i class="fas fa-chart-line"></i> Funding Stage</label>
                <select id="funding_stage" name="funding_stage" required>
                    <option value="pre_seed">Pre-Seed</option>
                    <option value="seed">Seed</option>
                    <option value="series_a">Series A</option>
                    <option value="series_b">Series B</option>
                    <option value="series_c">Series C</option>
                    <option value="late_stage">Late Stage</option>
                    <option value="exit">Exit</option>
                </select>
            </div>

            <div class="form-group">
                <label for="startup_stage"><i class="fas fa-rocket"></i> Startup Stage</label>
                <select id="startup_stage" name="startup_stage" required>
                    <option value="ideation">Ideation Stage</option>
                    <option value="validation">Validation Stage</option>
                    <option value="mvp">MVP Stage</option>
                    <option value="growth">Growth Stage</option>
                    <option value="maturity">Maturity Stage</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-align-left"></i> Description</label>
                <textarea id="description" name="description" placeholder="Provide a brief description of your startup" required></textarea>
            </div>

            <div class="form-group">
                <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                <select id="location" name="location" class="select2" required>
                    <option value="">Select Location</option>
                    <?php foreach ($locations as $region => $cities): ?>
                        <optgroup label="<?php echo htmlspecialchars($region); ?>">
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>">
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="file"><i class="fas fa-file-upload"></i> Video Pitch / File Upload</label>
                <input type="file" id="file" name="file" accept="video/mp4, video/avi, video/mov, application/pdf, application/msword, application/vnd.ms-powerpoint">
            </div>

            <div class="form-group">
                <label for="website"><i class="fas fa-globe"></i> Website</label>
                <input type="url" id="website" name="website" placeholder="https://">
            </div>

            <div class="form-group">
                <label for="pitch_deck_url"><i class="fas fa-file-powerpoint"></i> Pitch Deck URL</label>
                <input type="url" id="pitch_deck_url" name="pitch_deck_url" placeholder="https://">
            </div>

            <div class="form-group">
                <label for="business_plan_url"><i class="fas fa-file-pdf"></i> Business Plan URL</label>
                <input type="url" id="business_plan_url" name="business_plan_url" placeholder="https://">
            </div>

            <button type="submit">
                <i class="fas fa-save"></i> Create Startup Profile
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for industry and location dropdowns
            $('#industry, #location').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Search or select an option',
                allowClear: true,
                minimumInputLength: 0,
                dropdownAutoWidth: true,
                scrollAfterSelect: true,
                closeOnSelect: true,
                matcher: function(params, data) {
                    // If there are no search terms, return all of the data
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Do not display the item if there is no 'text' property
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    // Search in the text and in the optgroup label
                    var searchStr = data.text.toLowerCase();
                    if (data.element && data.element.parentElement) {
                        searchStr += ' ' + data.element.parentElement.label.toLowerCase();
                    }

                    if (searchStr.indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }

                    // Return `null` if the term should not be displayed
                    return null;
                }
            });

            // Add custom class to Select2 container for styling
            $('.select2-container').addClass('custom-select2');
        });

        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            const defaultIcon = preview.querySelector('.default-logo-icon');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (defaultIcon) {
                        defaultIcon.style.display = 'none';
                    }
                    
                    let img = preview.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        preview.appendChild(img);
                    }
                    
                    // Create a temporary image to check dimensions
                    const tempImg = new Image();
                    tempImg.src = e.target.result;
                    tempImg.onload = function() {
                        const width = this.width;
                        const height = this.height;
                        
                        img.src = e.target.result;
                        img.style.display = 'block';
                        
                        // Adjust container padding based on aspect ratio
                        if (width > height) {
                            preview.style.padding = '25px 10px';
                        } else if (height > width) {
                            preview.style.padding = '10px 25px';
                        } else {
                            preview.style.padding = '10px';
                        }
                    };
                }
                
                reader.readAsDataURL(input.files[0]);
            } else if (defaultIcon) {
                defaultIcon.style.display = 'block';
                const img = preview.querySelector('img');
                if (img) {
                    img.remove();
                }
                preview.style.padding = '10px';
            }
        }
    </script>
</body>

</html>