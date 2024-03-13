<?php
session_start();

// データベースへの接続情報
$dsn = 'mysql:host=localhost;dbname=shopping;charset=utf8';
$username_db = 'root';
$password_db = '';

// バリデーションエラーメッセージ
$error_message = '';

// フォームの送信があった場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームから入力されたデータを取得
    $email = $_POST["email"];
    $password = $_POST["password"];
    $username = isset($_POST["username"]) ? $_POST["username"] : ''; // ユーザー名の入力をチェックし、空欄の場合は空文字列にする

    // ユーザー名のバリデーション
    if (empty($username)) {
        $error_message .= "Please enter your username.<br>";
    }

    // メールアドレスのバリデーション
    if (empty($email)) {
        $error_message .= "Please enter your email.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message .= "Please enter a valid email address.<br>";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error_message .= "Please enter a Gmail address.<br>";
    }

    // パスワードのバリデーション
    if (empty($password)) {
        $error_message .= "Please enter your password.<br>";
    } elseif (strlen($password) < 4 || !preg_match('/[A-Z]+/', $password) || !preg_match('/[a-z]+/', $password) || !preg_match('/[0-9]+/', $password) || !preg_match('/[!@#$%^&*()\-_=+{};:,<.>ยง?]+/', $password)) {
        $error_message .= "Password must be at least 4 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.<br>";
    }

    // バリデーションエラーがない場合、データベースを検索してログインを試みる
    if (empty($error_message)) {
        try {
            $pdo = new PDO($dsn, $username_db, $password_db);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 入力されたメールアドレスに対応するユーザーを取得
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
            $stmt->execute(array(':email' => $email));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ユーザーが存在し、パスワードが一致するか確認
            if ($user && password_verify($password, $user['password'])) {
                // ログイン成功時の処理
            $_SESSION['user_id'] = $user['id']; // ユーザーIDをセッションに保存
            $_SESSION['username'] = $user['username']; // ユーザー名をセッションに保存
            header('Location: main.php'); // ログイン後のページにリダイレクト
            exit();
        } else {
            // ログイン失敗時の処理
            $error_message = "Incorrect email or password.";
        }
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="styles.css"> <!-- CSSファイルをリンク -->
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .error { color: red; }
        .invalid-input { border: 1px solid red; }
    </style>
</head>
<body>
<div class="container">
    <h2>User Login</h2>
    <?php if (!empty($error_message)): ?>
        <div id="error-container" class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form id="loginForm" method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <?php if (!empty($username_error_message)): ?>
                <div class="error"><?php echo $username_error_message; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <?php if (!empty($email_error_message)): ?>
                <div class="error"><?php echo $email_error_message; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="checkbox" onclick="togglePassword()"> Show Password
            <span id="passwordError" class="error"></span>
            <?php if (!empty($password_error_message)): ?>
                <div class="error"><?php echo $password_error_message; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <button type="submit" name="login">Login</button>
        </div>
    </form>
    <p>New user? <a href="register.php">Register here</a></p>
</div>

        

<script>
    //パスワード表示切替
        function togglePassword() {
            var passwordField = document.getElementById('password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
            } else {
                passwordField.type = 'password';
            }
        }

        // JavaScriptによるリアルタイムのアラート表示
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

// ユーザー名が空白かどうかをチェック
usernameInput.addEventListener('input', function() {
    validateUsername();
});

// メールがgmailの形式に準拠しているかをチェック
emailInput.addEventListener('input', function() {
    validateEmail();
});

// パスワードの条件をチェック
passwordInput.addEventListener('input', function() {
    validatePassword();
});

function validateUsername() {
    if (usernameInput.value.trim() === '') {
        showError('Please enter your username.');
    } else {
        clearError();
    }
}

function validateEmail() {
    const emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
    if (!emailPattern.test(emailInput.value)) {
        emailError.textContent = 'Please enter a valid Gmail address.';
    } else {
        emailError.textContent = '';
    }
}

function validatePassword() {
        const password = passwordInput.value;
        if (password.length < 4 || !/[A-Z]+/.test(password) || !/[a-z]+/.test(password) || !/[0-9]+/.test(password) || !/[!@#$%^&*()\-_=+{};:,<.>ยง?]+/.test(password)) {
            passwordError.textContent = 'Password must be at least 4 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        } else {
            passwordError.textContent = '';
        }
    }
function showError(message) {
    errorContainer.textContent = message;
}

function clearError() {
    errorContainer.textContent = '';
}
</script>

</body>
</html>