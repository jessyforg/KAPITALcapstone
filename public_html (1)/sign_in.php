    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign In - Kapital</title>
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
                max-width: 1000px;
                display: flex;
                flex-direction: row;
                gap: 25px;
                align-items: center;
            }

            .logo-section {
                width: 30%;
                display: flex;
                flex-direction: column;
                align-items: center;
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
                flex-direction: column;
                align-items: center;
            }

            .partnership span {
                font-size: 0.8rem;
                color: #000;
                margin-bottom: 5px;
            }

            .taraki-logo {
                width: 80px;
                height: auto;
            }

            .form-container {
                width: 70%;
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

            .signin-form {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            label {
                font-size: 0.9rem;
                font-weight: 600;
                margin: 4px 0;
                width: 100%;
                text-align: left;
            }

            input {
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
                font-size: 0.9rem;
                font-family: "Poppins", sans-serif;
                width: 100%;
                background-color: #f9fafb;
                color: #374151;
                transition: all 0.2s ease;
            }

            input:focus {
                border-color: #ea580c;
                outline: none;
                background-color: #ffffff;
                box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1);
            }

            input::placeholder {
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
                border-radius: 5px;
            }

            button:hover {
                background-color: #c44a0a;
            }

            .error-message {
                color: #dc2626;
                font-size: 0.85rem;
                margin-bottom: 10px;
                text-align: center;
            }

            a {
                color: #ea580c;
                text-decoration: none;
                font-family: "Poppins", sans-serif;
            }

            a:hover {
                text-decoration: underline;
            }

            .sign-up-btn {
                color: #ea580c;
                font-weight: 500;
                font-size: 0.9em;
            }

            .sign-up-btn:hover {
                color: #c44a0a;
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

                .signin-form {
                    gap: 8px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo-section">
                <div class="logos-container">
                    <a href="index.php" class="kapital-logo">
                        <img src="imgs/kapitalblackorange.svg" alt="Kapital Logo" class="kapital-logo-img" style="width:125px;height:75px;vertical-align:middle;">
                    </a>
                    <div class="partnership">
                        <span>in partnership with</span>
                        <img src="imgs/tarakilogoblck1.png" alt="Taraki Logo" class="taraki-logo">
                    </div>
                </div>
            </div>
            <div class="form-container">
                <h2>Sign In</h2>
                <?php if (isset($_GET['error'])): ?>
                    <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>
                <form action="signin_process.php" method="POST" class="signin-form">
                    <label>Email</label>
                    <input type="email" name="email" id="email" required>
                    <label>Password</label>
                    <input type="password" name="password" id="password" required>
                    <button type="submit">Sign In</button>
                </form>
                <p>Don't have an account? <a href="sign_up.php" class="sign-up-btn">Sign Up</a></p>
            </div>
        </div>
    </body>
    </html>