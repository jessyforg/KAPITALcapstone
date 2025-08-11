<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(45deg, #ea580c, #000000);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1400px;
            display: flex;
            flex-direction: row;
            gap: 25px;
            align-items: flex-start;
        }

        .logo-section {
            width: 40%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .logos-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .kapital-logo {
            display: block;
            margin-bottom: 10px;
        }

        .kapital-logo-img {
            width: 160px;
            height: auto;
            transition: all 0.3s ease;
        }

        .kapital-logo:hover .kapital-logo-img {
            transform: scale(1.1);
            filter: brightness(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .partnership {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }
        
        .partnership span {
            font-size: 1rem;
            color: #000;
            font-weight: 500;
        }

        .partnership .kapital-logo-img {
            width: 100px;
            height: auto;
        }

        .taraki-logo {
            width: 80px;
            height: auto;
        }

        .gallery-section {
            width: 100%;
            position: relative;
            margin-top: 20px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .gallery-item {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
        }

        .gallery-item.active {
            opacity: 1;
            position: relative;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .gallery-item img.loaded {
            opacity: 1;
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0 16px;
            font-size: 3rem;
            text-align: center;
            font-weight: 700;
            height: 20%; /* Reduced overlay height */
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .slideshow-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            z-index: 2;
            pointer-events: none;
        }

        .slideshow-controls button {
            background: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #000;
            transition: all 0.3s ease;
            pointer-events: auto;
        }

        .slideshow-controls button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }

        .slideshow-controls button:active {
            transform: scale(0.95);
        }

        .login-section {
            margin-top: 20px;
            text-align: center;
        }

        .form-container {
            width: 60%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 1.6rem;
            margin-bottom: 15px;
            color: #000000;
            font-weight: 600;
            text-align: center;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        form label[for="name"],
        form label[for="email"],
        form label[for="password"],
        form label[for="retype_password"],
        form label[for="role"],
        form input#name,
        form input#email,
        form input#password,
        form input#retype_password,
        form select#role,
        form .terms-container,
        form button[type="submit"] {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 4px 0;
            width: 100%;
            text-align: left;
        }

        input,
        select {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: "Poppins", sans-serif;
            width: 100%;
            background-color: #f9fafb;
            color: #374151;
            transition: all 0.2s ease;
        }

        input:focus,
        select:focus {
            border-color: #ea580c;
            outline: none;
            background-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1);
        }

        input::placeholder,
        select::placeholder {
            color: #9ca3af;
        }

        button {
            background-color: #ea580c;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: none;
            font-size: 1rem;
            padding: 10px 0;
            margin-top: 5px;
        }

        button:hover {
            background-color: #c44a0a;
        }

        #jobSeekerFields {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 5px;
        }

        #jobSeekerFields label[for="resume"],
        #jobSeekerFields input#resume,
        #jobSeekerFields small {
            grid-column: 1 / -1;
        }

        #jobSeekerFields small {
            font-size: 0.75rem;
            color: #666;
            margin-top: -2px;
        }

        .terms-container {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .terms-container input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .terms-container label {
            font-size: 0.8rem;
            margin: 0;
        }

        a {
            color: #ea580c;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
        }

        a:hover {
            text-decoration: underline;
        }

        .login-link {
            color: #ea580c;
            font-weight: 500;
            font-size: 0.9em;
        }

        .login-link:hover {
            color: #c44a0a;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }

        .close:hover {
            color: #000;
        }

        .terms-content {
            margin-top: 15px;
            text-align: left;
        }

        .terms-content h4 {
            color: #000;
            margin: 12px 0 4px 0;
            font-size: 0.95rem;
        }

        .terms-content p {
            color: #666;
            line-height: 1.4;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        @media (max-width: 1024px) {
            .container {
                max-width: 900px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 420px;
                padding: 15px;
            }

            .logo-section,
            .form-container {
                width: 100%;
            }

            .gallery-section {
                grid-template-columns: 1fr;
            }

            form {
                grid-template-columns: 1fr;
            }

            #jobSeekerFields {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script>
        function toggleRoleFields() {
            var role = document.getElementById("role").value;
            var investorFields = document.getElementById("investorFields");
            var jobSeekerFields = document.getElementById("jobSeekerFields");
            var adminNotice = document.getElementById("adminNotice");
            investorFields.style.display = "none";
            jobSeekerFields.style.display = "none";
            adminNotice.style.display = "none";
            if (role === "investor") {
                investorFields.style.display = "block";
            } else if (role === "job_seeker") {
                jobSeekerFields.style.display = "block";
            }
        }

        function showError(message) {
            const modal = document.getElementById('errorModal');
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('errorModal');
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('errorModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <div class="logos-container">
                <div class="partnership">
                    <img src="imgs/kapitalblackorange.svg" alt="Kapital Logo" class="kapital-logo-img" style="width:118px;height:75px;vertical-align:middle;">
                    <span>×</span>
                    <img src="imgs/tarakilogoblck1.png" alt="Taraki Logo" class="taraki-logo">
                </div>
            </div>
            <div class="gallery-section">
                <div class="gallery-item active">
                    <img src="imgs/gallery/1.webp" alt="Entrepreneur" loading="lazy" data-src="imgs/gallery/1.webp">
                    <div class="gallery-caption">Start Your Journey</div>
                </div>
                <div class="gallery-item">
                    <img src="imgs/gallery/2.webp" alt="Investor" loading="lazy" data-src="imgs/gallery/2.webp">
                    <div class="gallery-caption">Grow Your Portfolio</div>
                </div>
                <div class="gallery-item">
                    <img src="imgs/gallery/3.webp" alt="Job Seeker" loading="lazy" data-src="imgs/gallery/3.webp">
                    <div class="gallery-caption">Find Your Path</div>
                </div>
                <div class="gallery-item">
                    <img src="imgs/gallery/4.webp" alt="Community" loading="lazy" data-src="imgs/gallery/4.webp">
                    <div class="gallery-caption">Join Our Community</div>
                </div>
                <div class="slideshow-controls">
                    <button onclick="prevSlide()" aria-label="Previous slide">❮</button>
                    <button onclick="nextSlide()" aria-label="Next slide">❯</button>
                </div>
            </div>
        </div>
        <div class="form-container">
            <h2>Sign Up</h2>
            <form method="POST" action="signup_process.php" enctype="multipart/form-data">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <label for="retype_password">Retype Password</label>
                <input type="password" id="retype_password" name="retype_password" required>
                <label for="role">Role</label>
                <select id="role" name="role" onchange="toggleRoleFields()" required>
                    <option value="entrepreneur">Entrepreneur</option>
                    <option value="investor">Investor</option>
                    <option value="job_seeker">Job Seeker</option>
                </select>

                <!-- Job Seeker Fields -->
                <div id="jobSeekerFields" style="display: none;">
                    <label for="resume">Resume (Optional)</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                    <small>Supported formats: PDF, DOC, DOCX (Max size: 5MB)</small>
                    
                    <label for="skills">Skills (Optional)</label>
                    <input type="text" id="skills" name="skills" placeholder="Enter skills separated by commas">
                    
                    <label for="experience_level">Experience Level</label>
                    <select id="experience_level" name="experience_level" required>
                        <option value="entry">Entry Level</option>
                        <option value="mid">Mid Level</option>
                        <option value="senior">Senior Level</option>
                    </select>
                    
                    <label for="desired_role">Desired Role (Optional)</label>
                    <input type="text" id="desired_role" name="desired_role" placeholder="e.g., Software Developer">
                    
                    <label for="location_preference">Preferred Location (Optional)</label>
                    <input type="text" id="location_preference" name="location_preference" placeholder="e.g., New York">
                </div>

                <div class="terms-container">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#" onclick="showTerms()">Terms and Conditions</a></label>
                </div>

                <button type="submit">Sign Up</button>
            </form>
            <div class="login-section">
                <p>Already have an account? <a href="sign_in.php" class="login-link">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Error</h3>
            <p id="errorMessage"></p>
        </div>
    </div>

    <!-- Terms Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeTerms()">&times;</span>
            <h3>Terms and Conditions</h3>
            <div class="terms-content">
                <h4>1. Acceptance of Terms</h4>
                <p>By accessing and using Kapital, you agree to be bound by these Terms and Conditions.</p>
                
                <h4>2. User Responsibilities</h4>
                <p>You are responsible for maintaining the confidentiality of your account and password.</p>
                
                <h4>3. Privacy Policy</h4>
                <p>Your use of Kapital is also governed by our Privacy Policy.</p>
                
                <h4>4. User Content</h4>
                <p>You retain all rights to any content you submit, post, or display on the platform.</p>
                
                <h4>5. Platform Rules</h4>
                <p>You agree not to use the platform for any illegal or unauthorized purpose.</p>
                
                <h4>6. Modifications</h4>
                <p>We reserve the right to modify these terms at any time.</p>
            </div>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.gallery-item');
        let slideInterval;

        function loadImage(img) {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.onload = function() {
                    img.classList.add('loaded');
                };
            }
        }

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
            
            // Load the current slide's image
            const currentImg = slides[index].querySelector('img');
            loadImage(currentImg);
            
            // Preload next image
            const nextIndex = (index + 1) % slides.length;
            const nextImg = slides[nextIndex].querySelector('img');
            loadImage(nextImg);
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }

        function startSlideshow() {
            slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
        }

        function stopSlideshow() {
            clearInterval(slideInterval);
        }

        // Start slideshow when page loads
        window.onload = function() {
            // Load the first slide immediately
            showSlide(0);
            startSlideshow();
            
            // Check for error message in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error) {
                showError(decodeURIComponent(error));
            }
        }

        // Pause slideshow when hovering over gallery
        document.querySelector('.gallery-section').addEventListener('mouseenter', stopSlideshow);
        document.querySelector('.gallery-section').addEventListener('mouseleave', startSlideshow);

        function showTerms() {
            const modal = document.getElementById('termsModal');
            modal.style.display = 'block';
        }

        function closeTerms() {
            const modal = document.getElementById('termsModal');
            modal.style.display = 'none';
        }
    </script>
</body>
</html>