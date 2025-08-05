<?php
// Include the database connection file
include 'db_connection.php';

// Start the session
session_start();

// Check if the user is an entrepreneur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'entrepreneur') {
    header('Location: index.php'); // Redirect if not an entrepreneur
    exit;
}

// Fetch the startup_id of the logged-in entrepreneur
$user_id = $_SESSION['user_id'];
$query = "SELECT startup_id FROM Startups WHERE entrepreneur_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $startup_id = $row['startup_id'];
} else {
    echo "Error: Startup not found for this entrepreneur.";
    exit;
}

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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and trim form inputs
    $role = trim($_POST['role']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);
    $salary_range_min = (float)$_POST['salary_range_min'];
    $salary_range_max = (float)$_POST['salary_range_max'];

    // Validate salary range
    if ($salary_range_min > $salary_range_max) {
        echo "Error: Minimum salary cannot exceed maximum salary.";
        exit;
    }

    // Insert the new job into the database
    $query = "INSERT INTO Jobs (startup_id, role, description, requirements, location, salary_range_min, salary_range_max, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "issssdd", $startup_id, $role, $description, $requirements, $location, $salary_range_min, $salary_range_max);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['status_message'] = 'Your job posting has been submitted for admin verification.';
        header('Location: entrepreneurs.php'); // Redirect after posting the job
        exit;
    } else {
        echo "Error posting job: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Kapital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #1a1a1a;
            color: #f9f9f9;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 100px auto 40px auto;
            padding: 30px;
            background-color: #242424;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(234, 88, 12, 0.2);
            border: 1px solid #ea580c;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            color: #ea580c;
            margin-bottom: 30px;
            text-align: center;
        }

        .container form label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #ea580c;
            font-size: 1rem;
        }

        .container form input,
        .container form textarea {
            width: 100%;
            padding: 12px;
            background-color: #2a2a2a;
            border: 1px solid #ea580c;
            border-radius: 8px;
            color: #f9f9f9;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .container form input:focus,
        .container form textarea:focus {
            outline: none;
            border-color: #ff6b1a;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1);
        }

        .container form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .container form input[type="number"] {
            -moz-appearance: textfield;
        }

        .container form input[type="number"]::-webkit-outer-spin-button,
        .container form input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .salary-range {
            display: flex;
            gap: 20px;
        }

        .salary-range .form-group {
            flex: 1;
        }

        form button {
            background-color: #ea580c;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        form button:hover {
            background-color: #ff6b1a;
            transform: translateY(-2px);
        }

        form button i {
            font-size: 1.1rem;
        }

        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            .section-title {
                font-size: 1.6rem;
            }

            .salary-range {
                flex-direction: column;
                gap: 10px;
            }

            form button {
                padding: 12px 20px;
            }
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            background-color: #2a2a2a;
            border: 1px solid #ea580c;
            border-radius: 8px;
            color: #f9f9f9;
            height: 42px;
            overflow: visible;
            display: flex;
            align-items: center;
            position: relative;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f9f9f9 !important;
            line-height: 42px;
            padding-left: 15px;
            padding-right: 60px;
            overflow: visible;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            text-align: left;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            position: absolute;
            height: 42px;
            width: 25px;
            right: 0;
            top: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 25px;
            top: 0;
            height: 42px;
            width: 20px;
            line-height: 42px;
            text-align: center;
            color: #f9f9f9;
            font-weight: bold;
            margin: 0;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
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
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>
    
    <div class="container">
        <h1 class="section-title">Post a Job</h1>

        <form method="POST">
            <div class="form-group">
                <label for="role"><i class="fas fa-briefcase"></i> Job Role</label>
                <input type="text" id="role" name="role" placeholder="Enter the job role" required>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-align-left"></i> Job Description</label>
                <textarea id="description" name="description" placeholder="Describe the job responsibilities and expectations" required></textarea>
            </div>

            <div class="form-group">
                <label for="requirements"><i class="fas fa-list-ul"></i> Job Requirements</label>
                <textarea id="requirements" name="requirements" placeholder="List the required skills, experience, and qualifications" required></textarea>
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

            <div class="salary-range">
                <div class="form-group">
                    <label for="salary_range_min">Minimum Salary (₱)</label>
                    <input type="number" id="salary_range_min" name="salary_range_min" placeholder="Enter minimum salary" required>
                </div>

                <div class="form-group">
                    <label for="salary_range_max">Maximum Salary (₱)</label>
                    <input type="number" id="salary_range_max" name="salary_range_max" placeholder="Enter maximum salary" required>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-paper-plane"></i> Post Job
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#location').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Search or select a location',
                allowClear: true,
                minimumInputLength: 0,
                dropdownAutoWidth: true,
                templateResult: formatOption,
                templateSelection: formatOption
            }).on('select2:select select2:unselect', function() {
                // Force a re-render of the selected option
                $(this).trigger('change');
                // Ensure the selected text is visible
                $(this).next('.select2-container').find('.select2-selection__rendered').css({
                    'color': '#FFFFFF',
                    'visibility': 'visible',
                    'display': 'block'
                });
            });

            function formatOption(option) {
                if (!option.id) {
                    return option.text;
                }
                return $('<div style="color: #FFFFFF; display: block; visibility: visible;">' + option.text + '</div>');
            }
        });
    </script>
</body>

</html>
