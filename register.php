<?php
session_start();

function getUsers() {
    $file = 'users.json';
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function saveUsers($users) {
    $file = 'users.json';
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $message = "Password dan konfirmasi password tidak sama.";
    } else {
        $users = getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $message = "Email sudah terdaftar.";
                break;
            }
        }
        if ($message === '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $users[] = [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword
            ];
            saveUsers($users);
            header('Location: login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DocuShare Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" />
  <style>
    /* ... (style sama persis dengan login.php, ganti warna sesuai kebutuhan) ... */
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
    .register-btn {
      width: 100%; padding: 12px; font-size: 16px; font-weight: 600; color: white; background: #27ae60;
      border: none; border-radius: 10px; cursor: pointer; transition: background 0.3s ease;
    }
    .register-btn:hover {
      background: #219150;
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
      <h2>Register</h2>
      <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      <form action="" method="POST">
        <div class="form-group">
          <i class="ri-user-line"></i>
          <input type="text" name="username" placeholder="Username" required />
        </div>
        <div class="form-group">
          <i class="ri-mail-line"></i>
          <input type="email" name="email" placeholder="Email address" required />
        </div>
        <div class="form-group">
          <i class="ri-lock-line"></i>
          <input type="password" name="password" placeholder="Password" required />
        </div>
        <div class="form-group">
          <i class="ri-lock-password-line"></i>
          <input type="password" name="password_confirm" placeholder="Confirm Password" required />
        </div>
        <button type="submit" class="register-btn">Sign Up</button>
      </form>
      <div class="login">
        Already have an account? <a href="login.php">Log in</a>
      </div>
    </div>
  </div>
</body>
</html>
