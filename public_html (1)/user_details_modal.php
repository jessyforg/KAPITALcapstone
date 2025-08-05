<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['show_details_modal']) && $_SESSION['show_details_modal'] === true) {
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

    // Define industries array
    $industries = [
        'Technology' => [
            'Software Development',
            'Artificial Intelligence',
            'Mobile App Development',
            'Cloud Computing',
            'Cybersecurity',
            'E-commerce',
            'Fintech',
            'Internet of Things (IoT)',
            'Blockchain',
            'Big Data'
        ],
        'Healthcare' => [
            'Medical Devices',
            'Healthcare IT',
            'Biotechnology',
            'Pharmaceuticals',
            'Telemedicine',
            'Mental Health',
            'Healthcare Services',
            'Medical Research',
            'Digital Health',
            'Wellness & Fitness'
        ],
        'Education' => [
            'EdTech',
            'Online Learning',
            'Educational Services',
            'Professional Training',
            'Language Learning',
            'Educational Content',
            'Learning Management Systems',
            'Educational Apps',
            'STEM Education',
            'Early Childhood Education'
        ],
        'Financial Services' => [
            'Banking',
            'Insurance',
            'Investment Management',
            'Payment Processing',
            'Cryptocurrency',
            'Personal Finance',
            'Lending',
            'Financial Advisory',
            'Asset Management',
            'Risk Management'
        ],
        'Retail & E-commerce' => [
            'Online Retail',
            'Mobile Commerce',
            'Retail Technology',
            'Fashion & Apparel',
            'Consumer Goods',
            'Marketplace Platforms',
            'Subscription Services',
            'Retail Analytics',
            'Supply Chain Management',
            'Customer Experience'
        ],
        'Manufacturing' => [
            'Advanced Manufacturing',
            'Industrial Automation',
            '3D Printing',
            'Smart Manufacturing',
            'Electronics Manufacturing',
            'Food Processing',
            'Textile Manufacturing',
            'Automotive Manufacturing',
            'Chemical Manufacturing',
            'Green Manufacturing'
        ],
        'Energy & Sustainability' => [
            'Renewable Energy',
            'Clean Technology',
            'Energy Efficiency',
            'Solar Power',
            'Wind Energy',
            'Energy Storage',
            'Green Building',
            'Waste Management',
            'Environmental Services',
            'Sustainable Transportation'
        ],
        'Agriculture' => [
            'AgTech',
            'Smart Farming',
            'Organic Farming',
            'Precision Agriculture',
            'Aquaculture',
            'Vertical Farming',
            'Agricultural Biotechnology',
            'Farm Management',
            'Agricultural Supply Chain',
            'Food Technology'
        ],
        'Transportation & Logistics' => [
            'Logistics Technology',
            'Fleet Management',
            'Last-Mile Delivery',
            'Transportation Services',
            'Autonomous Vehicles',
            'Shipping & Freight',
            'Urban Mobility',
            'Warehouse Management',
            'Supply Chain Solutions',
            'Delivery Optimization'
        ],
        'Real Estate & Construction' => [
            'PropTech',
            'Construction Technology',
            'Real Estate Services',
            'Property Management',
            'Smart Buildings',
            'Construction Management',
            'Architecture & Design',
            'Building Materials',
            'Real Estate Investment',
            'Facility Management'
        ]
    ];
?>
    <div id="userDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Complete Your Profile</h2>
                <p>Please provide additional information to help us better understand your profile.</p>
            </div>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <form id="userDetailsForm" method="POST" action="process_user_details.php">
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="Enter your contact number">
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
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
                    <label for="industry">Industry</label>
                    <select id="industry" name="industry" class="select2" required>
                        <option value="">Select Industry</option>
                        <?php foreach ($industries as $category => $subcategories): ?>
                            <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                <?php foreach ($subcategories as $industry): ?>
                                    <option value="<?php echo htmlspecialchars($industry); ?>">
                                        <?php echo htmlspecialchars($industry); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="introduction">Introduction</label>
                    <textarea id="introduction" name="introduction" rows="4" required placeholder="Tell us about yourself..."></textarea>
                </div>
                <div class="form-group">
                    <label for="accomplishments">Accomplishments</label>
                    <textarea id="accomplishments" name="accomplishments" rows="4" placeholder="Share your achievements..."></textarea>
                </div>
                <div class="form-group">
                    <label for="education">Education</label>
                    <textarea id="education" name="education" rows="4" placeholder="Your educational background..."></textarea>
                </div>
                <div class="form-group">
                    <label for="employment">Employment</label>
                    <textarea id="employment" name="employment" rows="4" placeholder="Your work experience..."></textarea>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer_not_to_say">Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate">
                </div>
                <div class="form-group">
                    <label for="facebook_url">Facebook URL</label>
                    <input type="url" id="facebook_url" name="facebook_url" placeholder="Your Facebook profile URL">
                </div>
                <div class="form-group">
                    <label for="twitter_url">Twitter URL</label>
                    <input type="url" id="twitter_url" name="twitter_url" placeholder="Your Twitter profile URL">
                </div>
                <div class="form-group">
                    <label for="instagram_url">Instagram URL</label>
                    <input type="url" id="instagram_url" name="instagram_url" placeholder="Your Instagram profile URL">
                </div>
                <div class="form-group">
                    <label for="linkedin_url">LinkedIn URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" placeholder="Your LinkedIn profile URL">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-submit">Save Details</button>
                    <button type="button" class="btn-skip">Skip for Now</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show modal immediately when the page loads
            $('#userDetailsModal').show();

            // Initialize Select2 for location and industry only
            $('#location, #industry').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Search or select',
                allowClear: true,
                minimumInputLength: 0,
                dropdownParent: $('#userDetailsModal'),
                templateResult: formatOption,
                templateSelection: formatOption,
                closeOnSelect: true
            }).on('select2:select', function(e) {
                $(this).select2('close');
            });

            function formatOption(option) {
                if (!option.id) {
                    return option.text;
                }
                return $('<span style="color: #FFFFFF;">' + option.text + '</span>');
            }

            // Enable modal scrolling
            $('.modal').on('mousewheel DOMMouseScroll', function(e) {
                var scrollTo = null;
                if (e.type == 'mousewheel') {
                    scrollTo = (e.originalEvent.wheelDelta * -1);
                } else if (e.type == 'DOMMouseScroll') {
                    scrollTo = 40 * e.originalEvent.detail;
                }
                if (scrollTo) {
                    e.preventDefault();
                    $(this).scrollTop(scrollTo + $(this).scrollTop());
                }
            });

            // Handle form submission
            $('#userDetailsForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous error messages
                $('.error-message').remove();
                
                // Basic validation
                var contactNumber = $('#contact_number').val();
                var location = $('#location').val();
                
                if (!contactNumber || !location) {
                    $('#userDetailsForm').prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">Contact number and location are required.</div>');
                    return;
                }
                
                $.ajax({
                    url: 'process_user_details.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.success) {
                                window.location.href = 'index.php';
                            } else {
                                $('#userDetailsForm').prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">' + (result.message || 'Error saving details. Please try again.') + '</div>');
                            }
                        } catch (e) {
                            $('#userDetailsForm').prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">Error processing response. Please try again.</div>');
                            console.error('Error parsing response:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#userDetailsForm').prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">An error occurred. Please try again.</div>');
                        console.error('Ajax error:', status, error);
                    }
                });
            });

            // Handle skip button
            $('.btn-skip').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'process_user_details.php',
                    type: 'POST',
                    data: { action: 'skip' },
                    success: function(response) {
                        try {
                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.success) {
                                window.location.href = 'index.php';
                            }
                        } catch (e) {
                            console.error('Error parsing skip response:', e);
                        }
                    }
                });
            });
        });
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px 0;
            font-family: 'Poppins', sans-serif;
        }

        .modal-content {
            background-color: #1e1e1e;
            margin: 50px auto;
            padding: 40px;
            border-radius: 15px;
            max-width: 700px;
            position: relative;
            border: 1px solid rgba(234, 88, 12, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .modal-header h2 {
            color: #ea580c;
            margin-bottom: 15px;
            font-size: 2.2em;
            font-weight: 600;
        }

        .modal-header p {
            color: #fff;
            opacity: 0.9;
            font-size: 1.1em;
            max-width: 500px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #ea580c;
            font-weight: 500;
            font-size: 1.05em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(234, 88, 12, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(234, 88, 12, 0.5);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .modal-buttons {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 40px;
        }

        .btn-submit,
        .btn-skip {
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            min-width: 140px;
        }

        .btn-submit {
            background-color: #ea580c;
            color: #fff;
        }

        .btn-skip {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn-submit:hover {
            background-color: #c44a0a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(234, 88, 12, 0.3);
        }

        .btn-skip:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(234, 88, 12, 0.3) !important;
            border-radius: 8px !important;
            height: 48px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #FFFFFF !important;
            line-height: 48px !important;
            padding-left: 15px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }

        .select2-dropdown {
            background-color: #1e1e1e !important;
            border: 1px solid rgba(234, 88, 12, 0.3) !important;
            border-radius: 8px !important;
            margin-top: 4px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .select2-container--default .select2-results__option {
            background-color: #1e1e1e !important;
            color: #FFFFFF !important;
            padding: 12px 15px !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: rgba(234, 88, 12, 0.2) !important;
            color: #ea580c !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(234, 88, 12, 0.3) !important;
            border-radius: 6px !important;
            padding: 10px !important;
        }

        .select2-container--default .select2-results__group {
            background-color: rgba(234, 88, 12, 0.1) !important;
            color: #ea580c !important;
            font-weight: 600 !important;
            padding: 12px 15px !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(234, 88, 12, 0.1) !important;
            color: #ea580c !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #ea580c transparent transparent transparent !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #ea580c transparent !important;
        }

        /* Ensure dropdown is above modal */
        .select2-container--open {
            z-index: 9999 !important;
        }

        /* Add styles for Select2 dropdown positioning */
        .select2-container--open .select2-dropdown {
            z-index: 9999;
        }

        .select2-container--default .select2-results > .select2-results__options {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Error message styling */
        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 20px;
                padding: 25px;
            }

            .modal-header h2 {
                font-size: 1.8em;
            }

            .modal-header p {
                font-size: 1em;
            }

            .modal-buttons {
                flex-direction: column;
                gap: 15px;
            }

            .btn-submit,
            .btn-skip {
                width: 100%;
                padding: 12px 20px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin: 15px;
                padding: 20px;
            }

            .modal-header h2 {
                font-size: 1.6em;
            }

            .form-group label {
                font-size: 1em;
            }
        }
    </style>
<?php
    // Only unset the session variable after displaying the modal
    unset($_SESSION['show_details_modal']);
}
?> 