<?php
session_start();
include "connection.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $input     = trim($_POST["input"]);
    $password  = trim($_POST["pass"]);
    $fullname  = trim($_POST["fname"]);
    $username  = trim($_POST["uname"]);

    $input     = mysqli_real_escape_string($conn, $input);
    $password  = mysqli_real_escape_string($conn, $password);
    $fullname  = mysqli_real_escape_string($conn, $fullname);
    $username  = mysqli_real_escape_string($conn, $username);

    // Checking if inputs are valid
    if (empty($input) || empty($password) || empty($fullname) || empty($username)) {
        $errors[] = "All fields are required.";
    }

    if (strpos($input, "@") !== false && !filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Check if user exist in database
    $check = mysqli_query(
        $conn,
        "SELECT * FROM users 
         WHERE username='$username' OR email='$input' OR phone='$input'
         LIMIT 1"
    );

    if (mysqli_num_rows($check) > 0) {
        $errors[] = "Email, phone, or username already exists.";
    }

    // Continue if no error
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Decide if input is email or phone
        if (strpos($input, "@") !== false) {
            $email = $input;
            $phone = null;
        } else {
            $email = null;
            $phone = $input;
        }

        $insert = mysqli_query(
            $conn,
            "INSERT INTO users (phone, email, password, full_name, username)
             VALUES('$phone', '$email', '$hashed', '$fullname', '$username')"
        );
        if ($insert) {
            $_SESSION["message"] = "Account created successfully. Please log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Database error.";
        }
    }

    $_SESSION["err"] = $errors;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }

        a {
            color: #0095f6;
            text-decoration: none;
            font-weight: 600;
        }

        .main-container {
            width: 350px;
            margin-bottom: 20px;
        }

        .signup-box {
            background-color: #000;
            border: 1px solid #262626;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
            text-align: center;
        }

        .logo {
            font-family: 'Brush Script MT', 'cursive', sans-serif;
            font-size: 52px;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        .tagline {
            font-size: 17px;
            color: #a8a8a8;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .facebook-login-btn {
            background-color: #0095f6;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .or-divider {
            display: flex;
            align-items: center;
            width: 100%;
            margin: 10px 0 18px 0;
        }

        .or-divider .line {
            flex-grow: 1;
            height: 1px;
            background-color: #262626;
        }

        .or-divider .text {
            margin: 0 18px;
            color: #a8a8a8;
            font-size: 13px;
            font-weight: 600;
        }

        .signup-form {
            width: 100%;
        }

        .signup-form input {
            width: calc(100% - 22px);
            padding: 9px 10px 7px 10px;
            margin-bottom: 6px;
            background-color: #121212;
            border: 1px solid #262626;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
        }

        .signup-form input::placeholder {
            color: #a8a8a8;
        }

        .info-text,
        .terms-text {
            color: #a8a8a8;
            font-size: 12px;
            line-height: 1.3;
            margin: 10px 0;
        }

        .info-text {
            margin-top: 20px;
        }

        .signup-btn {
            background-color: #0095f6;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-box {
            background-color: #000;
            border: 1px solid #262626;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }

        .footer {
            width: 90%;
            max-width: 935px;
            padding: 24px 0;
            text-align: center;
        }

        .footer nav a {
            color: #a8a8a8;
            font-size: 12px;
            margin: 0 8px;
            line-height: 1.5;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="signup-box">
            <h1 class="logo">Instagram</h1>
            <p class="tagline">Sign up to see photos and videos from your friends.</p>
            <?php
            if (isset($_SESSION["err"]) && count($_SESSION["err"]) > 0) {
                echo '<div style="
                            background:#ff4d4d;
                            padding:10px;
                            border-radius:5px;
                            color:white;
                            width:92%;
                            font-size:13px;
                            text-align:left;
                            margin-bottom:15px;
                        ">';

                foreach ($_SESSION["err"] as $e) {
                    echo "" . $e . "<br>";
                }

                echo "</div>";

                unset($_SESSION['err']); // Clear errors after displaying
            }
            ?>

            <button class="facebook-login-btn">
                Log in with Facebook
            </button>

            <div class="or-divider">
                <span class="line"></span>
                <span class="text">OR</span>
                <span class="line"></span>
            </div>

            <form class="signup-form" action="" method="POST">
                <input type="text" placeholder="Mobile Number or Email" name="input">
                <input type="password" placeholder="Password" name="pass">
                <input type="text" placeholder="Full Name" name="fname">
                <input type="text" placeholder="Username" name="uname">

                <p class="info-text">
                    People who use our service may have uploaded your contact information to Instagram. <a href="#">Learn More</a>
                </p>

                <p class="terms-text">
                    By signing up, you agree to our <a href="#">Terms</a>, <a href="#">Privacy Policy</a> and <a href="#">Cookies Policy</a>.
                </p>

                <button type="submit" class="signup-btn">Sign up</button>
            </form>
        </div>

        <div class="login-box">
            <p>Have an account? <a href="login.php">Log in</a></p>
        </div>

    </div>

    <footer class="footer">
        <nav>
            <a href="#">Meta</a>
            <a href="#">About</a>
            <a href="#">Blog</a>
            <a href="#">Jobs</a>
            <a href="#">Help</a>
            <a href="#">API</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Locations</a>
            <a href="#">Instagram Lite</a>
            <a href="#">Meta AI</a>
            <a href="#">Meta AI Articles</a>
            <a href="#">Threads</a>
            <a href="#">Contact Uploading & Non-Users</a>
            <a href="#">Meta Verified</a>
        </nav>
    </footer>
</body>

</html>