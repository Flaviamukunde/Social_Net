<?php
session_start();
include "connection.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $input    = trim($_POST["p_uemail"]);
    $password = trim($_POST["passcode"]);

    $input    = mysqli_real_escape_string($conn, $input);
    $password = mysqli_real_escape_string($conn, $password);

    if (empty($input) || empty($password)) {
        $errors[] = "All fields are required.";
    } else {

        $query = mysqli_query(
            $conn,
            "SELECT * FROM users 
             WHERE username='$input' OR email='$input' OR phone='$input'
             LIMIT 1"
        );

        if (mysqli_num_rows($query) === 1) {
            $user = mysqli_fetch_assoc($query);

            if (password_verify($password, $user["password"])) {

                $_SESSION["userLogged"] = $user["id"];
                $_SESSION["message"] = "Welcome " . $user["full_name"];

                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "User not found.";
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
    <title>Instagram Log In</title>
    <link rel="stylesheet" href="login_style.css">
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
        }

        .main-container {
            width: 350px;
        }

        .login-box {
            background-color: #000;
            border: 1px solid #262626;
            padding: 10px 40px;
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
            margin: 22px 0 30px 0;
        }

        .login-form {
            width: 100%;
        }

        .login-form input {
            width: calc(100% - 22px);
            padding: 9px 10px 7px 10px;
            margin-bottom: 6px;
            background-color: #121212;
            border: 1px solid #262626;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
        }

        .login-form input::placeholder {
            color: #a8a8a8;
        }

        .login-btn {
            background-color: #0095f66b;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }

        .or-divider {
            display: flex;
            align-items: center;
            width: 100%;
            margin: 18px 0;
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

        .facebook-login-link {
            color: #0095f6;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .facebook-login-link i {
            margin-right: 8px;
            font-size: 16px;
        }

        .forgot-password-link {
            color: #a8a8a8;
            font-size: 12px;
            font-weight: 400;
            margin-bottom: 20px;
        }

        .signup-link-box {
            background-color: #000;
            border: 1px solid #262626;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }

        .signup-link-box a {
            color: #0095f6;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="login-box">
            <h1 class="logo">Instagram</h1>
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

                unset($_SESSION['err']); // Clear errors after showing them
            }
            ?>

            <form class="login-form" action="" method="POST">
                <input type="text" placeholder="Phone number, username, or email" name="p_uemail">
                <input type="password" placeholder="Password" name="passcode">

                <button type="submit" class="login-btn">Log in</button>
            </form>

            <div class="or-divider">
                <span class="line"></span>
                <span class="text">OR</span>
                <span class="line"></span>
            </div>

            <a href="#" class="facebook-login-link">
                <i class="fab fa-facebook-square"></i> Log in with Facebook
            </a>

            <a href="#" class="forgot-password-link">Forgot password?</a>
        </div>

        <div class="signup-link-box">
            <p>Don't have an account? <a href="create.php">Sign up</a></p>
        </div>

    </div>

</body>

</html>