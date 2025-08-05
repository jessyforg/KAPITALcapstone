<?php
$page = 'about';
include('navbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1e1e1e;
            color: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            margin-top: 88px;
        }

        .about-hero-section {
            text-align: center;
            padding: 60px 0;
            background: linear-gradient(135deg, rgba(234, 88, 12, 0.1), rgba(194, 65, 12, 0.1));
            border-radius: 20px;
            margin-bottom: 60px;
        }

        .about-hero-section h1 {
            font-size: 3em;
            color: #ea580c;
            margin-bottom: 20px;
        }

        .about-kapital-section {
            margin-bottom: 60px;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 15px;
        }

        .about-kapital-section h2 {
            color: #ea580c;
            margin-bottom: 20px;
        }

        .about-features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .about-feature-item {
            background: rgba(234, 88, 12, 0.1);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(234, 88, 12, 0.2);
        }

        .about-feature-item h3 {
            color: #ea580c;
            margin-bottom: 10px;
        }

        .about-team-section, .about-taraki-team-section {
            margin-bottom: 60px;
        }

        .about-team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .about-team-member {
            text-align: center;
        }

        .about-member-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 3px solid #ea580c;
            overflow: hidden;
            background-color: rgba(234, 88, 12, 0.1);
        }

        .about-member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-member-info h3 {
            color: #ea580c;
            margin-bottom: 10px;
        }

        .about-member-role {
            color: #ea580c;
            font-style: italic;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .about-member-description {
            font-size: 0.9em;
            color: #fff;
            margin-bottom: 15px;
            padding: 0 15px;
        }

        .about-social-link {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .about-social-link:hover {
            color: #ea580c;
        }

        .about-social-icons-member {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .about-social-icons-member a {
            color: #fff;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .about-social-icons-member a:hover {
            color: #ea580c;
        }

        .about-taraki-section {
            margin-bottom: 60px;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }

        .about-taraki-logo {
            width: auto;
            height: 100px;
            margin: 0 auto 20px;
            display: block;
        }

        .about-taraki-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .about-taraki-title h2 {
            font-size: 40px;
            line-height: 1;
            margin: 0;
            color: #ea580c;
        }

        .about-taraki-title img {
            height: 45px;
            width: auto;
        }

        .about-social-media {
            margin-top: 40px;
            text-align: center;
        }

        .about-social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .about-social-icons a {
            color: #fff;
            font-size: 24px;
            transition: color 0.3s ease;
        }

        .about-social-icons a:hover {
            color: #ea580c;
        }

        .about-contact-info {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background: rgba(234, 88, 12, 0.1);
            border-radius: 10px;
        }

        .about-contact-info p {
            margin: 10px 0;
        }

        .about-contact-info i {
            color: #ea580c;
            margin-right: 10px;
        }

        .about-back-button {
            position: fixed;
            top: 44px;
            left: 20px;
            background: rgba(234, 88, 12, 0.1);
            border: 1px solid rgba(234, 88, 12, 0.2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .about-back-button:hover {
            background: rgba(234, 88, 12, 0.2);
            color: #ea580c;
        }

        .about-back-button i {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="about-container">
        <div class="about-hero-section">
            <div class="about-section-title">
                <h1>About</h1>
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
                    <img src="imgs/kapitalwhiteorange.svg" alt="Kapital" style="height: 115px; width: auto;">
                    <img src="imgs/logo.png" alt="TARAKI" style="height: 60px; width: auto;">
                </div>
            </div>
            <p>Empowering Innovation in the Cordillera Region</p>
        </div>

        <div class="about-kapital-section">
            <h2>What is Kapital?</h2>
            <p>Kapital is an innovative startup ecosystem platform designed to connect entrepreneurs, investors, and job seekers in the Cordillera region. Our platform serves as a bridge between ambitious startups and potential investors, while also creating opportunities for talented individuals seeking employment in the startup sector.</p>
            
            <div class="about-features-list">
                <div class="about-feature-item">
                    <h3>Startup Showcase</h3>
                    <p>Platform for entrepreneurs to showcase their innovative startups and connect with potential investors.</p>
                </div>
                <div class="about-feature-item">
                    <h3>Investment Matching</h3>
                    <p>Connecting startups with investors interested in supporting regional innovation and growth.</p>
                </div>
                <div class="about-feature-item">
                    <h3>Job Opportunities</h3>
                    <p>Creating employment opportunities within the startup ecosystem for local talent.</p>
                </div>
                <div class="about-feature-item">
                    <h3>Verification System</h3>
                    <p>Robust verification process ensuring trust and credibility within the platform.</p>
                </div>
            </div>
        </div>

        <div class="about-team-section">
            <h2>Meet Our Team</h2>
            <div class="about-team-grid">
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/jes.png" alt="Jester A. Perez">
                    </div>
                    <div class="about-member-info">
                        <h3>Jester A. Perez</h3>
                        <a href="https://www.facebook.com/jstrprz/" class="about-social-link" target="_blank">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </div>
                </div>
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/troy.png" alt="Troy Benedict L. Ayson">
                    </div>
                    <div class="about-member-info">
                        <h3>Troy Benedict L. Ayson</h3>
                        <a href="https://www.facebook.com/troyxayson" class="about-social-link" target="_blank">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </div>
                </div>
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/eug.png" alt="Eugene Jherico P. Naval">
                    </div>
                    <div class="about-member-info">
                        <h3>Eugene Jherico P. Naval</h3>
                        <a href="https://www.facebook.com/eugenejericho.naval" class="about-social-link" target="_blank">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-taraki-team-section">
            <h2>Meet Our TARAKIs</h2>
            <div class="about-team-grid">
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/thelma.webp" alt="Dr. Thelma D. Palaoag">
                    </div>
                    <div class="about-member-info">
                        <h3>Dr. Thelma D. Palaoag</h3>
                        <div class="about-member-role">Project Leader</div>
                        <div class="about-member-description">20 years of experience in technology and innovation. Her visionary leadership has been instrumental in shaping TARAKI's strategic direction.</div>
                        <div class="about-social-icons-member">
                            <a href="#" target="_blank" title="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/ate-jez.webp" alt="Jezelle Q. Oliva">
                    </div>
                    <div class="about-member-info">
                        <h3>Jezelle Q. Oliva</h3>
                        <div class="about-member-role">Startup Community Enabler</div>
                        <div class="about-member-description">An educator, empowering TARAKI startups, and fosters community growth through spearheading innovative initiatives.</div>
                        <div class="about-social-icons-member">
                            <a href="#" target="_blank" title="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="about-team-member">
                    <div class="about-member-photo">
                        <img src="imgs/ate pia.png" alt="Pia Bernardine T. Capuyan">
                    </div>
                    <div class="about-member-info">
                        <h3>Pia Bernardine T. Capuyan</h3>
                        <div class="about-member-role">Project Assistant of TARAKI</div>
                        <div class="about-member-description">An experienced writer with a background in architecture, combining creativity with technical insight. She helps drive fresh ideas and solutions within TARAKI.</div>
                        <div class="about-social-icons-member">
                            <a href="#" target="_blank" title="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-taraki-section">
            <div class="about-taraki-title">
                <h2>About</h2>
                <img src="imgs/logo.png" alt="TARAKI">
            </div>
            <p>Across the Philippines, there are 19 total consortia (as of 2024) funded by DOST-PCIEERD (Department of Science and Technology-Philippine Council for Industry, Energy and Emerging Technology Research and Development) under the HeIRIT-ReSEED (Higher Education Institution Readiness for Innovation and Technopreneurship-Regional Startup Enablers for Ecosystem Development) Program.</p>
            
            <p>TARAKI-CAR (Technological Consortium for Awareness, Readiness, and Advancement of Knowledge in Innovation-Cordillera Administrative Region) is the startup consortium in the Cordillera Region which started on January 3, 2022. This is being led by the University of the Cordilleras with the regional DOST (Department of Science and Technology), DICT (Department of Information and Communications Technology), DTI (Department of Trade and Industry), and TESDA-CSITE offices; including Technology Business Incubators such as UPB SILBI TBI and UC InTTO.</p>

            <p>The consortium spearheads the development and formalization of the startup ecosystem, acting as a bridge to connect stakeholders. It engages partners and innovators across the Cordillera region to nurture and support startups, making them attractive to investors and government funding opportunities.</p>

            <div class="about-social-media">
                <h3>Connect with TARAKI</h3>
                <div class="about-social-icons">
                    <a href="https://taraki.vercel.app" target="_blank" title="TARAKI Website">
                        <i class="fas fa-globe"></i>
                    </a>
                    <a href="https://www.facebook.com/tarakicar" target="_blank" title="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://www.linkedin.com/company/taraki-car/?originalSubdomain=ph" target="_blank" title="LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="https://www.instagram.com/tarakicar/" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="about-contact-info">
            <h3>Contact Us</h3>
            <p><i class="fas fa-envelope"></i> startup.kapital@gmail.com</p>
        </div>
    </div>
</body>
</html>
