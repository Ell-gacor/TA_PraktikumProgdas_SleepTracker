<?php
session_start();
require "Auth.php";
require "SleepTracker.php";

$auth = new Auth();
$message = "";
$mode = $_GET["mode"] ?? "login"; // login atau register

if($_SERVER["REQUEST_METHOD"]=="POST") {
    $user = $_POST["username"];
    $pass = $_POST["password"];
    
    if($mode == "register") {
        if($auth->userExists($user)) {
            $message = "Username sudah terdaftar";
        } else if(strlen($user) < 3) {
            $message = "Username minimal 3 karakter";
        } else if(strlen($pass) < 3) {
            $message = "Password minimal 3 karakter";
        } else if($auth->register($user, $pass)) {
            $message = "Registrasi berhasil! Silakan login.";
            $mode = "login";
        }
    } else {
        if($auth->login($user, $pass)) {
            $_SESSION["login"] = true;
            $_SESSION["user"] = $user;
            $_SESSION["tracker"] = new SleepTracker($user);
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Username atau Password salah";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Georgia", "Garamond", "Palatino Linotype", "Times New Roman", serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(254deg, #0E1A40, #8D99AE);
        }

        .container {
            background: linear-gradient(127deg, #EFEDEA, #F0ECE3);
            width: 350px;
            padding: 35px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        .container h1 {
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: bold;
            color: #000000ff;
        }

        .box label {
            display: block;
            text-align: left;
            margin-bottom: 6px;
            font-weight: bold;
            color: #0E1A40;
        }

        .box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #98867B;
            font-size: 15px;
            transition: all 0.2s ease;
            font-family: "Georgia", "Garamond", "Palatino Linotype", "Times New Roman", serif;
        }

        .box input:focus {
            border-color: #0E1A40;
            box-shadow: 0 0 6px rgba(83, 77, 64, 0.3);
            outline: none;
        }

        .box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            background: #0E1A40;
            color: #EFEDEA;
            transition: all 0.3s ease;
        }

        .box button:hover {
            transform: translateY(-2px);
            background: #0E1A40;
            color: #ffffffff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.25);
        }

        .container p {
            margin-top: 10px;
            font-size: 14px;
            color: red;
        }

        .toggle-text {
            color: #0E1A40 !important;
            margin-top: 15px;
            text-align: center;
        }

        .toggle-text a {
            color: #0E1A40;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .toggle-text a:hover {
            color: #0E1A40;
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $mode == "login" ? "Login" : "Registrasi" ?></h1>

        <form method="POST" class="box">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit"><?= $mode == "login" ? "Masuk" : "Daftar" ?></button>
        </form>

        <?php if($message): ?>
            <p><?= $message ?></p>
        <?php endif; ?>

        <p class="toggle-text">
            <?php if($mode == "login"): ?>
                Belum punya akun? <a href="?mode=register">Daftar</a>
            <?php else: ?>
                Sudah punya akun? <a href="?mode=login">Login</a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
