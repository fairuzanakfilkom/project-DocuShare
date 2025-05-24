<?php
session_start();

// Pastikan response header HTML
header('Content-Type: text/html; charset=UTF-8');

function getUsers() {
    $file = 'users.json';
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([])); // buat file kosong kalau belum ada
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi sederhana email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid.';
    } else {
        $users = getUsers();
        $found = false;

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $found = true;
                if (password_verify($password, $user['password'])) {
                    // Simpan session
                    $_SESSION['email'] = $email;
                    $_SESSION['username'] = $user['username'];

                    // Redirect ke dashboard
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $message = 'Password salah.';
                }
                break;
            }
        }

        if (!$found) {
            $message = 'Email tidak ditemukan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DocuShare Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" />
  <style>
    /* Style kamu sama saja, aku langsung copy */
    * { margin:0; padding:0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body {
      background: #745cb0; display: flex; align-items: center; justify-content: center; height: 100vh;
    }
    .container {
      display: flex; background: white; width: 900px; height: 520px; border-radius: 20px; overflow: hidden;
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }
    .left {
      flex: 1; background: #6ec1ff; display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .left img {
      width: 90%; max-width: 400px;
    }
    .right {
      flex: 1; background: #ffe082; display: flex; flex-direction: column; justify-content: center; padding: 50px;
    }
    .logo {
      display: flex; align-items: center; font-size: 28px; font-weight: 600; color: #2d2c52; margin-bottom: 15px;
    }
    .logo i {
      font-size: 28px; margin-right: 10px; color: #2d2c52;
    }
    h2 {
      font-size: 22px; margin-bottom: 30px; color: #2d2c52;
    }
    .form-group {
      position: relative; margin-bottom: 20px;
    }
    .form-group i {
      position: absolute; top: 13px; left: 15px; color: #888;
    }
    .form-group input {
      width: 100%; padding: 12px 15px 12px 40px; border: none; border-radius: 10px; font-size: 15px; background-color: #f6f6f6;
    }
    .form-group input:focus {
      outline: none; box-shadow: 0 0 0 3px rgba(100,100,255,0.3);
    }
    .login-btn {
      width: 100%; padding: 12px; font-size: 16px; font-weight: 600; color: white; background: #e74c3c;
      border: none; border-radius: 10px; cursor: pointer; transition: background 0.3s ease;
    }
    .login-btn:hover {
      background: #c0392b;
    }
    .message {
      margin-bottom: 20px; font-weight: 600; color: red;
    }
    @media (max-width: 768px) {
      .container {
        flex-direction: column; height: auto; width: 95%;
      }
      .left, .right {
        width: 100%;
      }
      .right {
        padding: 30px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="434bcdb0-9938-413c-99b9-19377b17eb4b.png" alt="DocuShare Illustration" />
    </div>
    <div class="right">
      <div class="logo"><i class="ri-file-text-line"></i> DocuShare</div>
      <h2>Login</h2>
      <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form action="" method="POST">
        <div class="form-group">
          <i class="ri-mail-line"></i>
          <input type="email" name="email" placeholder="Email address" required />
        </div>
        <div class="form-group">
          <i class="ri-lock-line"></i>
          <input type="password" name="password" placeholder="Password" required />
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
      <div class="signup">
        Don’t have an account? <a href="register.php">Sign up</a>
      </div>
    </div>
  </div>
</body>
</html>
