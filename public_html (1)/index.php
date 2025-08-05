<?php
session_start(); // Start session to check login status
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    
    <!-- Tailwind CSS Configuration -->
    <link rel="stylesheet" href="tailwind-config.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-init.js"></script>

    <!-- Additional CSS for custom styles -->
    <style>
        /* General Reset */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background-color: #1e1e1e;
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-section {
            text-align: left;
            padding: 100px 20px;
            background: url('imgs/newbg.png') no-repeat center center/cover;
            color: #fff;
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .hero-section .container {
            max-width: 1200px;
            width: 100%;
            padding: 0 40px;
        }

        /* Overlay for better text visibility */
        .hero-section::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        /* Heading Styling */
        .hero-section h1 {
            font-size: 3.5em;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
            max-width: 700px;
            text-align: left;
        }

        /* Paragraph Styling */
        .hero-section p {
            font-size: 1.5em;
            margin-bottom: 30px;
            max-width: 700px;
            line-height: 1.6;
            text-align: left;
        }

        /* Button Styling */
        .hero-section .btn {
            background-color: #ea580c;
            color: #000;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
        }

        /* Button Hover Effects */
        .hero-section .btn:hover {
            background-color: #000;
            color: #ea580c;
        }

        /* Features Section Inside Hero */
        .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        /* Feature Card Styling */
        .feature-card {
            background-color: #333;
            padding: 30px;
            border-radius: 10px;
            width: 250px;
            text-align: center;
            transition: transform 0.3s ease, background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .feature-card h3 {
            font-size: 1.5em;
            color: #ea580c;
            margin-bottom: 15px;
        }

        .feature-card p {
            font-size: 1em;
            color: #fff;
        }

        /* Hover Effect for Feature Cards */
        .feature-card:hover {
            transform: translateY(-10px);
            color: #000;
        }

        /* Analytics Section */
        .analytics-section {
            background-color: #23272A;
            padding: 40px 20px;
            text-align: center;
            margin-top: 40px;
        }

        .analytics-section h2 {
            color: #ea580c;
            margin-bottom: 20px;
        }

        .analytics-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .analytics-card {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            width: 200px;
            color: #fff;
            transition: transform 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 120px;
        }

        .analytics-card h3 {
            font-size: 2.5em;
            margin: 0 0 10px 0;
            text-align: center;
        }

        .analytics-card p {
            margin: 0;
            text-align: center;
            width: 100%;
            line-height: 1.2;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
        }

        /* Taraki Section */
        .taraki-section {
            background-color: #2a2a2a;
            padding: 80px 20px;
            text-align: center;
        }

        .taraki-section img {
            max-width: 200px;
            margin-bottom: 30px;
        }

        .taraki-section h2 {
            color: #ea580c;
            margin-bottom: 20px;
        }

        .taraki-section p {
            max-width: 800px;
            margin: 0 auto 20px;
            color: #fff;
        }

        .taraki-section .btn {
            display: inline-block;
            padding: 15px 30px;
            background-color: #ea580c;
            color: #000;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
        }

        .taraki-section .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
            background-color: #000;
            color: #ea580c;
        }

        /* FAQ Section */
        .faq-section {
            padding: 80px 20px;
            background-color: #1e1e1e;
        }

        .faq-section h2 {
            text-align: center;
            color: #ea580c;
            margin-bottom: 40px;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: #333;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            position: relative;
            font-weight: 600;
            color: #ea580c;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question::after {
            content: '+';
            font-size: 1.5em;
            transition: transform 0.3s ease;
        }

        .faq-question.active::after {
            transform: rotate(45deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            color: #fff;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 500px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5em;
            }

            .hero-section p {
                font-size: 1.2em;
                padding: 0 20px;
            }

            .hero-section .btn {
                font-size: 1.1em;
                padding: 10px 25px;
            }

            .features {
                flex-direction: column;
                gap: 20px;
            }

            .analytics-cards {
                flex-direction: column;
                align-items: center;
            }

            .analytics-card {
                width: 80%;
            }
        }

        /* TBI Section */
        .tbi-section {
            background-color: #1e1e1e;
            padding: 80px 20px;
            text-align: center;
        }

        .tbi-section h2 {
            color: #ea580c;
            margin-bottom: 40px;
            font-size: 2.5em;
        }

        .tbi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .tbi-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
        }

        .tbi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .tbi-logo {
            height: 120px;
            width: 100%;
            margin: 0 auto 30px;
            object-fit: contain;
            max-width: 300px;
        }

        .tbi-card h3 {
            color: #000000;
            margin: 15px 0;
            font-size: 1.8em;
            font-weight: 600;
        }

        .tbi-card p {
            color: #333333;
            font-size: 1em;
            line-height: 1.6;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .tbi-grid {
                grid-template-columns: 1fr;
                padding: 0 10px;
            }

            .tbi-card {
                padding: 30px;
                min-height: auto;
            }

            .tbi-logo {
                height: 100px;
                margin-bottom: 20px;
            }
        }

        /* Floating Waitlist Button */
        .floating-waitlist-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #ea580c;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 16px 24px;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(234, 88, 12, 0.4);
            transition: all 0.3s ease;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .floating-waitlist-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(234, 88, 12, 0.6);
            background: #d14d06;
        }

        @media (max-width: 768px) {
            .floating-waitlist-btn {
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                font-size: 0.9em;
                         }
         }

        /* Waitlist Modal Styles */
        .waitlist-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .waitlist-modal-content {
            background: #21242a;
            margin: 2% auto;
            padding: 0;
            border-radius: 24px;
            width: 95%;
            max-width: 1000px;
            max-height: 96vh;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: row;
            min-height: 600px;
        }

        .waitlist-close {
            color: #aaa;
            position: absolute;
            right: 24px;
            top: 24px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .waitlist-close:hover {
            color: #ea580c;
        }

        .waitlist-left {
            flex: 1;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #21242a;
        }

        .waitlist-right {
            flex: 1;
            padding: 48px;
            background: #21242a;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .waitlist-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .waitlist-brand svg {
            height: 180px;
            width: auto;
        }

        .waitlist-subtitle {
            color: #ea580c;
            font-size: 1em;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .waitlist-title {
            color: #fff;
            font-size: 2.2em;
            font-weight: 600;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .waitlist-description {
            color: #bdbdbd;
            font-size: 1.1em;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .waitlist-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            color: #fff;
            font-size: 1em;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 16px;
            border: 1px solid #333;
            border-radius: 12px;
            font-size: 16px;
            color: #fff;
            background: #1a1a1a;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.1);
        }

        .form-group input[type="text"]::placeholder,
        .form-group input[type="email"]::placeholder {
            color: #666;
        }

        .radio-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            color: #fff;
            font-size: 1em;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .radio-option:hover {
            background-color: rgba(234, 88, 12, 0.1);
        }

        .radio-option input[type="radio"] {
            display: none;
        }

        .radio-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #555;
            border-radius: 50%;
            position: relative;
            transition: all 0.3s ease;
            background: #21242a;
            flex-shrink: 0;
            display: inline-block;
        }

        .radio-option:hover .radio-custom {
            border-color: #ea580c;
        }

        .radio-option input[type="radio"]:checked + .radio-custom {
            border-color: #ea580c;
            background: #ea580c;
        }

        .radio-option input[type="radio"]:checked + .radio-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
        }

        .waitlist-submit-btn {
            background: #ea580c;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 18px 32px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 24px;
        }

        .waitlist-submit-btn:hover {
            background: #d14d06;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
        }

        .waitlist-submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .waitlist-contact {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #333;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ea580c;
            font-size: 1em;
        }

        .contact-item i {
            font-size: 1.2em;
        }

        .waitlist-message {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            display: none;
        }

        .waitlist-message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .waitlist-message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .waitlist-modal-content {
                width: 95%;
                margin: 5% auto;
                flex-direction: column;
                max-height: 90vh;
                overflow-y: auto;
            }

            .waitlist-left,
            .waitlist-right {
                padding: 24px;
            }

            .waitlist-title {
                font-size: 1.8em;
            }

            .radio-group {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <!-- Include Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section (Figma style, with background image) -->
    <section class="relative min-h-[100vh] flex items-center justify-center py-16 md:py-20 lg:py-24" 
             style="background: url('imgs/newbg.webp') no-repeat center center/cover;">
        <div class="container-responsive flex flex-wrap max-w-7xl mx-auto gap-6 sm:gap-8 lg:gap-12 items-center justify-between relative z-10">
            <!-- Left: Headline, subheadline, supported by -->
            <div class="flex-1 min-w-[320px] max-w-lg xs:max-w-xl animate-fade-in-up pt-16 sm:pt-20">
                <h1 class="text-white text-2xl xs:text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
                    Connecting Innovation<br class="hidden sm:block">
                    <span class="sm:hidden"> </span>in the Cordillera Region
                </h1>
                <div class="text-gray-300 text-base xs:text-lg sm:text-xl mb-6 sm:mb-8 max-w-md sm:max-w-lg">
                    A startup ecosystem platform bridging entrepreneurs, investors, and talent to build the future together.
                </div>
                
                <div class="mb-2 sm:mb-3 text-gray-400 text-sm sm:text-base">Supported by</div>
                <div class="flex gap-3 sm:gap-4 lg:gap-5 items-center flex-wrap">
                    <img src="imgs/tarakisvg.svg" alt="TARAKI Logo" 
                         class="h-8 sm:h-9 lg:h-10 bg-white rounded-lg p-1 sm:p-2 transition-transform hover:scale-105">
                    <img src="imgs/TBIs/inTTO.svg" alt="InTTO Logo" 
                         class="h-8 sm:h-9 lg:h-10 bg-white rounded-lg p-1 sm:p-2 transition-transform hover:scale-105">
                </div>
            </div>
        </div>
        <!-- Overlay for better text visibility -->
        <div class="absolute inset-0 bg-black bg-opacity-50 z-0"></div>
    </section>

    <!-- Hero Section: Orange Stats Bar with Analytics Functionality -->
    <section class="bg-brand-orange py-8 md:py-12">
        <div class="container-responsive max-w-5xl mx-auto">
            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 justify-items-center">
                <div class="bg-white text-gray-900 rounded-2xl w-full max-w-[200px] h-24 sm:h-28 flex flex-col items-center justify-center shadow-lg transform transition-transform hover:scale-105 animate-slide-in-right">
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1">
                        <?php 
                            include 'db_connection.php';
                            $active_startups = 0; 
                            $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM Startups WHERE approval_status = 'approved'");
                            if ($row = mysqli_fetch_assoc($result)) {
                                $active_startups = $row['count'];
                            }
                            echo $active_startups; 
                        ?>
                    </div>
                    <div class="text-sm sm:text-base text-center">Active Startups</div>
                </div>
                <div class="bg-white text-gray-900 rounded-2xl w-full max-w-[200px] h-24 sm:h-28 flex flex-col items-center justify-center shadow-lg transform transition-transform hover:scale-105 animate-slide-in-right" style="animation-delay: 0.1s;">
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1">
                        <?php 
                            $result = mysqli_query($conn, "SELECT COUNT(DISTINCT investor_id) as count FROM Matches");
                            if ($row = mysqli_fetch_assoc($result)) {
                                $matched_investors = $row['count'];
                            }
                            echo $matched_investors; 
                        ?>
                    </div>
                    <div class="text-sm sm:text-base text-center">Connected Investors</div>
                </div>
                <div class="bg-white text-gray-900 rounded-2xl w-full max-w-[200px] h-24 sm:h-28 flex flex-col items-center justify-center shadow-lg transform transition-transform hover:scale-105 animate-slide-in-right xs:col-span-2 lg:col-span-1" style="animation-delay: 0.2s;">
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1">
                        <?php 
                            $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM job_seekers");
                            if ($row = mysqli_fetch_assoc($result)) {
                                $hired_job_seekers = $row['count'];
                            }
                            echo $hired_job_seekers; 
                        ?>
                    </div>
                    <div class="text-sm sm:text-base text-center">Active Job Seekers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- What we do Section -->
    <section class="bg-dark-main py-14 md:py-20">
        <div class="container-responsive max-w-6xl mx-auto">
            <h2 class="text-white text-2xl sm:text-3xl md:text-4xl font-bold mb-8 md:mb-12 animate-fade-in-up">What we do</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <div class="card-dark bg-gray-800 rounded-2xl p-6 sm:p-8 flex flex-col items-center text-center transform transition-all duration-300 hover:scale-105 hover:shadow-glow animate-fade-in-up">
                    <!-- Fixed Rocket SVG, 64x64, centered -->
                    <div class="mb-6 flex justify-center items-center h-16 w-16">
                        <svg width="64" height="64" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="block">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M37.4115 58.6457L21.3544 42.5886C23.1201 39.6343 24.8801 36.5771 26.6229 33.5371C32.4287 23.4457 38.0972 13.5943 43.4115 8.46855C57.8344 -5.95431 77.5887 2.4114 77.5887 2.4114C77.5887 2.4114 85.9487 22.1657 71.5315 36.5885C66.4458 41.8571 56.7487 47.4457 46.7315 53.2057C43.6115 55.0057 40.4629 56.8171 37.4115 58.6457ZM49.6401 21.32C49.6401 18.9224 50.5925 16.6231 52.2878 14.9277C53.9832 13.2324 56.2825 12.28 58.6801 12.28C61.0777 12.28 63.377 13.2324 65.0723 14.9277C66.7677 16.6231 67.7201 18.9224 67.7201 21.32C67.6608 23.6774 66.6827 25.9184 64.9943 27.5648C63.306 29.2112 61.0411 30.1327 58.6829 30.1327C56.3247 30.1327 54.0599 29.2112 52.3716 27.5648C50.6832 25.9184 49.6994 23.6774 49.6401 21.32ZM26.2687 19.9943C18.1658 17.84 10.5601 21.8285 3.81152 28.0171C3.50031 28.3103 3.26195 28.6722 3.11545 29.0738C2.96896 29.4755 2.9184 29.9059 2.96781 30.3306C3.01722 30.7553 3.16523 31.1625 3.40002 31.5198C3.63481 31.8772 3.94989 32.1747 4.3201 32.3885L15.2058 38.9486L15.2172 38.9257C16.7887 36.2971 18.543 33.2514 20.3258 30.1657C22.3544 26.64 24.4172 23.0628 26.2687 19.9943ZM41.0515 64.7943L47.6115 75.68C47.8259 76.0498 48.1238 76.3644 48.4814 76.5986C48.8389 76.8329 49.2463 76.9803 49.671 77.0291C50.0956 77.078 50.5258 77.0269 50.9272 76.88C51.3287 76.733 51.6902 76.4943 51.983 76.1828C58.1715 69.44 62.1658 61.8286 60.0058 53.7257C57.023 55.52 53.8915 57.3257 50.7887 59.1143L50.3144 59.3885C47.183 61.1943 44.0801 62.9771 41.0801 64.7771L41.0515 64.7943ZM14.983 53.3886C17.2925 53.3476 19.5602 54.0075 21.4872 55.2812C23.4142 56.5549 24.9101 58.3826 25.7775 60.5234C26.645 62.6642 26.8434 65.0177 26.3465 67.2735C25.8497 69.5293 24.681 71.5817 22.9944 73.16C21.7258 74.3714 19.743 75.32 17.9087 76.0514C15.8508 76.8431 13.7526 77.5259 11.623 78.0971C9.52581 78.6686 7.52581 79.1257 6.01152 79.4286C5.37485 79.5591 4.73459 79.6715 4.09152 79.7657L3.33152 79.84C2.94977 79.8744 2.56499 79.8316 2.2001 79.7143C1.66378 79.5581 1.18499 79.2482 0.822953 78.8228C0.512017 78.4627 0.295956 78.0305 0.194381 77.5657C0.122097 77.2357 0.0989906 76.8968 0.12581 76.56C0.142953 76.3314 0.177238 76.0686 0.211524 75.8286C0.28581 75.3257 0.400096 74.6686 0.554381 73.92C0.857238 72.4114 1.32581 70.4228 1.89724 68.3371C2.46867 66.2571 3.16581 64.0286 3.94867 62.08C4.6801 60.2457 5.62867 58.2686 6.8401 57.0057C7.88601 55.8882 9.14572 54.9922 10.5445 54.3709C11.9433 53.7495 13.4526 53.4155 14.983 53.3886Z" fill="white"/>
                        </svg>
                    </div>
                    <div class="font-bold text-lg sm:text-xl mb-2 text-white">Showcase your Startup</div>
                    <div class="text-gray-400 text-sm sm:text-base">Present your innovative ideas and connect with potential investors.</div>
                </div>
                <div style="background: #181818; border-radius: 16px; padding: 32px 24px; flex: 1 1 220px; min-width: 220px; max-width: 320px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Briefcase SVG -->
                    <div style="margin-bottom: 22px; display: flex; justify-content: center; align-items: center; height: 64px; width: 64px;">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
                            <rect x="8" y="16" width="32" height="20" rx="3" fill="#fff"/>
                            <rect x="16" y="12" width="16" height="8" rx="2" fill="#fff"/>
                        </svg>
                    </div>
                    <div style="font-weight: 700; font-size: 1.2em; margin-bottom: 8px; color: #fff; text-align: center;">Find Opportunities</div>
                    <div style="color: #bdbdbd; text-align: center; font-size: 1em;">Discover exciting startups and investment opportunities in the region.</div>
                </div>
                <div style="background: #181818; border-radius: 16px; padding: 32px 24px; flex: 1 1 220px; min-width: 220px; max-width: 320px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Team/Group SVG -->
                    <div style="margin-bottom: 22px; display: flex; justify-content: center; align-items: center; height: 64px; width: 64px;">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
                            <circle cx="24" cy="18" r="6" fill="#fff"/>
                            <circle cx="12" cy="22" r="4" fill="#fff"/>
                            <circle cx="36" cy="22" r="4" fill="#fff"/>
                            <rect x="8" y="30" width="32" height="10" rx="5" fill="#fff"/>
                        </svg>
                    </div>
                    <div style="font-weight: 700; font-size: 1.2em; margin-bottom: 8px; color: #fff; text-align: center;">Join Startup Teams</div>
                    <div style="color: #bdbdbd; text-align: center; font-size: 1em;">Find exciting career opportunities in innovative startups.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partnership Section -->
    <section style="background: #181818; padding: 56px 0 32px 0;">
        <div class="container" style="max-width: 1100px; margin: 0 auto;">
            <h2 style="color: #fff; font-size: 2em; font-weight: 700; margin-bottom: 16px;">In Partnership with TARAKI</h2>
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 32px;">
                <div style="flex: 1 1 350px; min-width: 300px; max-width: 500px;">
                    <p style="color: #bdbdbd; font-size: 1.1em; margin-bottom: 18px;">TARAKI is a strong organization dedicated to fostering innovation and entrepreneurship in the Cordillera Administrative Region (CAR). As a catalyst for regional development, TARAKI provides essential support, mentorship, and resources to emerging startups and entrepreneurs.</p>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="https://taraki.vercel.app" style="background: #ea580c; color: #fff; border-radius: 8px; padding: 12px 28px; font-weight: 600; text-decoration: none; font-size: 1em; display: inline-block;">Learn More About TARAKI</a>
                        <button onclick="openWaitlistModal()" style="background: transparent; color: #ea580c; border: 2px solid #ea580c; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 1em; cursor: pointer; transition: all 0.3s ease;">Join Waitlist</button>
                    </div>
                </div>
                <div style="flex: 1 1 00px; min-width: 300px; display: flex; justify-content: center;">
                    <img src="imgs/tarakiteam.svg" alt="Taraki Group" style="width: 100%; max-width: 500px; border-radius: 18px; object-fit: cover; box-shadow: 0 4px 16px rgba(234,88,12,0.15);">
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Business Incubator Section (restored content, modern styling) -->
    <section style="background: #111; padding: 56px 0 56px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2 style="color: #fff; font-size: 2em; font-weight: 700; margin-bottom: 32px;">Technological Business Incubators</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
                <a href="https://taraki.vercel.app/tbi/intto" class="tbi-card" target="_blank" style="background: #fff; border-radius: 18px; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit;">
                    <img src="imgs/TBIs/inTTO.svg" alt="InTTO Logo" style="margin-bottom: 18px; border-radius: 8px; height: 80px; width: auto;">
                    <div style="font-weight: 700; font-size: 1.2em; color: #181818; margin-bottom: 8px;">InTTO</div>
                    <div style="color: #232323; font-size: 1em; text-align: center; margin-bottom: 18px;">The Innovation and Technology Transfer Office (InTTO) fosters innovation by offering business and technology transfer opportunities to faculty, students, alumni, and the community through its two specialized units.</div>
                    <span style="background: #ea580c; color: #fff; border-radius: 8px; padding: 10px 22px; font-weight: 600; font-size: 1em; margin-top: auto;">Learn More</span>
                </a>
                <a href="https://upbsilbi.com/" class="tbi-card" target="_blank" style="background: #fff; border-radius: 18px; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit;">
                    <img src="imgs/TBIs/SILBI_TBI.svg" alt="SILBI Logo" style="margin-bottom: 18px; border-radius: 8px; height: 80px; width: auto;">
                    <div style="font-weight: 700; font-size: 1.2em; color: #181818; margin-bottom: 8px;">SILBI</div>
                    <div style="color: #232323; font-size: 1em; text-align: center; margin-bottom: 18px;">Silbi, meaning "service" in Filipino, reflects UP Baguio's dedication to community service. The SILBI Center drives transformation in Cordillera and Northern Luzon through research and innovation, fostering public service initiatives.</div>
                    <span style="background: #ea580c; color: #fff; border-radius: 8px; padding: 10px 22px; font-weight: 600; font-size: 1em; margin-top: auto;">Learn More</span>
                </a>
                <a href="https://www.slu.edu.ph/sirib-center/" class="tbi-card" target="_blank" style="background: #fff; border-radius: 18px; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit;">
                    <img src="imgs/TBIs/slu.webp" alt="ConRes Logo" style="margin-bottom: 18px; border-radius: 8px; height: 80px; width: auto;">
                    <div style="font-weight: 700; font-size: 1.2em; color: #181818; margin-bottom: 8px;">ConRes</div>
                    <div style="color: #232323; font-size: 1em; text-align: center; margin-bottom: 18px;">Established in 2017 with CHED funding, the SIRIB Center created a Technology Hub and Co-Working Space. It launched "Technopreneurship 101" to integrate entrepreneurship into engineering education, fostering tech-savvy entrepreneurs.</div>
                    <span style="background: #ea580c; color: #fff; border-radius: 8px; padding: 10px 22px; font-weight: 600; font-size: 1em; margin-top: auto;">Learn More</span>
                </a>
                <a href="https://www.facebook.com/BenguetStateUniversity/" class="tbi-card" target="_blank" style="background: #fff; border-radius: 18px; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit;">
                    <img src="imgs/TBIs/bsu.svg" alt="ATBI/IC Logo" style="margin-bottom: 18px; border-radius: 8px; height: 80px; width: auto;">
                    <div style="font-weight: 700; font-size: 1.2em; color: #181818; margin-bottom: 8px;">ATBI/IC</div>
                    <div style="color: #232323; font-size: 1em; text-align: center; margin-bottom: 18px;">Founded under BOR Resolution No. 1939, s. 2010, the Agri-based Technology Business Incubator/Innovation Center supports start-ups and micro businesses in agricultural technology, offering professional services to help them grow.</div>
                    <span style="background: #ea580c; color: #fff; border-radius: 8px; padding: 10px 22px; font-weight: 600; font-size: 1em; margin-top: auto;">Learn More</span>
                </a>
                <a href="https://www.facebook.com/ifugaostateuniversity/" class="tbi-card" target="_blank" style="background: #fff; border-radius: 18px; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit;">
                    <img src="imgs/TBIs/IFSU-TBI.svg" alt="IFSU IPTBM Logo" style="margin-bottom: 18px; border-radius: 8px; height: 80px; width: auto;">
                    <div style="font-weight: 700; font-size: 1.2em; color: #181818; margin-bottom: 8px;">IFSU IPTBM</div>
                    <div style="color: #232323; font-size: 1em; text-align: center; margin-bottom: 18px;">Founded under BOR Resolution No. 1939, s. 2010, the Agri-based Technology Business Incubator/Innovation Center supports start-ups and micro businesses in agricultural technology, offering professional services to help them grow.</div>
                    <span style="background: #ea580c; color: #fff; border-radius: 8px; padding: 10px 22px; font-weight: 600; font-size: 1em; margin-top: auto;">Learn More</span>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">What is Kapital?</div>
                    <div class="faq-answer">
                        Kapital is a startup ecosystem platform designed to connect entrepreneurs, investors, and job seekers in the Cordillera region. We provide a space for startups to showcase their ideas, investors to discover opportunities, and talent to find exciting career prospects.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How can I join as an entrepreneur?</div>
                    <div class="faq-answer">
                        Simply sign up and select 'Entrepreneur' as your role. You can then create your startup profile, showcase your projects, and connect with potential investors and talent.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What benefits do investors get?</div>
                    <div class="faq-answer">
                        Investors gain access to a curated selection of regional startups, detailed project information, and direct communication channels with entrepreneurs. This helps in making informed investment decisions.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How does job seeking work on Kapital?</div>
                    <div class="faq-answer">
                        Job seekers can create profiles highlighting their skills and experience, browse startup job postings, and directly apply to positions. This creates a direct connection between talent and growing startups.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What is TARAKI?</div>
                    <div class="faq-answer">
                        TARAKI is a pioneering organization dedicated to fostering innovation and entrepreneurship in the Cordillera Administrative Region (CAR). As a catalyst for regional development, TARAKI provides essential support, mentorship, and resources to emerging startups and entrepreneurs.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How can I get involved with TARAKI?</div>
                    <div class="faq-answer">
                        Stay connected with us through our vibrant community on Facebook and Instagram. Explore tailored events and initiatives designed just for you.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Who can join TARAKI's programs and initiatives?</div>
                    <div class="faq-answer">
                        Everyone with a spark of innovation is invited! Whether you're a startup founder, an enthusiast, or simply curious about the startup ecosystem, TARAKI welcomes you with open arms.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Does TARAKI offer resources for startups?</div>
                    <div class="faq-answer">
                        Absolutely! Dive into a wealth of resources tailored for startups: from personalized mentorship sessions to enlightening seminars, workshops, and engaging talks by industry experts at our innovation-driven events.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How can TARAKI support my startup?</div>
                    <div class="faq-answer">
                        Let TARAKI fuel your startup journey with our acceleration program and a plethora of events specially curated for Cordilleran startups. Stay informed by following our dynamic updates on our Facebook Page.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'user_details_modal.php'; ?>

    <!-- Waitlist Modal -->
    <div id="waitlistModal" class="waitlist-modal">
        <div class="waitlist-modal-content">
            <span class="waitlist-close" onclick="closeWaitlistModal()">&times;</span>
            
            <!-- Left Side - Branding & Info -->
            <div class="waitlist-left">
                <div class="waitlist-brand">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1280 719.94" style="enable-background:new 0 0 1280 719.94; height: 180px; width: auto;" xml:space="preserve">
                        <style type="text/css">
                            .st4{fill:none;stroke:#FFFFFF;stroke-width:23;stroke-miterlimit:10;}
                            .st7{fill:#DDDDDD;stroke:#FFFFFF;stroke-width:23;stroke-miterlimit:10;}
                        </style>
                        <g>
                            <polyline class="st4" points="338.02,454.8 404.42,326.49 487.88,490.89 533.31,490.89"/>
                            <line class="st4" x1="198.11" y1="502.74" x2="198.11" y2="267.49"/>
                            <line class="st4" x1="198.11" y1="267.49" x2="198.11" y2="502.74"/>
                            <path class="st7" d="M326.37,300.63L195.88,413.45L326.37,300.63z"/>
                            <polyline class="st4" points="762.38,491.09 807.89,491.09 890.53,331.8 972.58,491.09"/>
                            <polyline class="st4" points="513.15,468.33 513.15,276.34 629.23,352.72 514.99,425.15"/>
                            <line class="st4" x1="640.66" y1="388.11" x2="640.66" y2="502.74"/>
                            <line class="st4" x1="733.66" y1="360.74" x2="733.66" y2="502.74"/>
                            <path class="st4" d="M625.63,300.26c63.08,52.97,153.85,51.31,214.76,0"/>
                            <circle class="st4" cx="733.01" cy="275.13" r="32.43"/>
                            <polyline class="st4" points="1002.46,285.79 1002.46,490.43 1084.91,490.43"/>
                            <path class="st7" d="M336.5,482.93l-137.33-76.85L336.5,482.93z"/>
                        </g>
                    </svg>
                </div>
                <div class="waitlist-subtitle">Join our Waitlist</div>
                <h2 class="waitlist-title">Get Early Access to the Cordillera Startup Ecosystem</h2>
                <p class="waitlist-description">Join our early waitlist and get priority access to Kapital — your platform for connecting with startups, investors, and opportunities in the Cordillera region.</p>
            </div>

            <!-- Right Side - Form -->
            <div class="waitlist-right">
                <div id="waitlistMessage" class="waitlist-message"></div>

                <form id="waitlistForm" class="waitlist-form">
                    <div>
                        <div class="form-group">
                            <label for="waitlist-name">Name</label>
                            <input type="text" id="waitlist-name" name="name" placeholder="Enter your Name" required>
                        </div>

                        <div class="form-group">
                            <label for="waitlist-email">Email Address</label>
                            <input type="email" id="waitlist-email" name="email" placeholder="Enter your Email Address" required>
                        </div>

                        <div class="form-group">
                            <label>Are you a:</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="user_type" value="entrepreneur" required>
                                    <span class="radio-custom"></span>
                                    Entrepreneur
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="user_type" value="investor" required>
                                    <span class="radio-custom"></span>
                                    Investor
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="user_type" value="job_seeker" required>
                                    <span class="radio-custom"></span>
                                    Job Seeker
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="waitlist-submit-btn">
                            Join the Waitlist
                        </button>

                        <div class="waitlist-contact">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>startup.kapital@gmail.com</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script>
        // FAQ Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    // Toggle active class on question
                    question.classList.toggle('active');
                    
                    // Toggle active class on answer
                    const answer = question.nextElementSibling;
                    answer.classList.toggle('active');
                    
                    // Close other open FAQs
                    faqQuestions.forEach(otherQuestion => {
                        if (otherQuestion !== question) {
                            otherQuestion.classList.remove('active');
                            otherQuestion.nextElementSibling.classList.remove('active');
                        }
                    });
                });
            });
        });

        // Waitlist Modal Functions
        function openWaitlistModal() {
            document.getElementById('waitlistModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeWaitlistModal() {
            document.getElementById('waitlistModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showWaitlistMessage(message, type) {
            const messageDiv = document.getElementById('waitlistMessage');
            messageDiv.textContent = message;
            messageDiv.className = `waitlist-message ${type}`;
            messageDiv.style.display = 'block';
        }

        function hideWaitlistMessage() {
            document.getElementById('waitlistMessage').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('waitlistModal');
            if (event.target === modal) {
                closeWaitlistModal();
            }
        }

        // Handle waitlist form submission
        document.getElementById('waitlistForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.querySelector('.waitlist-submit-btn');
            const originalText = submitBtn.textContent;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Joining...';
            hideWaitlistMessage();
            
            // Get form data
            const formData = new FormData(this);
            const data = {
                waitlist_id: '29639',
                email: formData.get('email'),
                name: formData.get('name'),
                user_type: formData.get('user_type')
            };
            
            try {
                // Submit to GetWaitlist.com API
                const response = await fetch('https://api.getwaitlist.com/api/v1/signup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    showWaitlistMessage('Thanks for joining! We\'ll notify you when we launch.', 'success');
                    this.reset();
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        closeWaitlistModal();
                    }, 2000);
                } else {
                    const errorData = await response.json();
                    showWaitlistMessage(errorData.message || 'Something went wrong. Please try again.', 'error');
                }
            } catch (error) {
                showWaitlistMessage('Something went wrong. Please try again.', 'error');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    </script>

    <!-- Include Footer (if any) 
    <?php include 'footer.php'; ?>
    -->

</body>

</html>
