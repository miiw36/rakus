<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css"> <!-- CSSファイルをリンク -->
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <input type="checkbox" onclick="togglePassword('password')"> Show Password
                
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <input type="checkbox" onclick="togglePassword('confirm_password')"> Show Password
            </div>
            <div class="form-group">
                <button type="submit" name="register">Register</button>
            </div>
        </form>
        <?php
        session_start();

        // フォームの送信があった場合の処理
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // フォームから入力されたデータを取得
            $username = $_POST["username"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $confirm_password = $_POST["confirm_password"];

            // データベースへの接続情報
            $dsn = 'mysql:host=localhost;dbname=shopping;charset=utf8';
            $username_db = 'root';
            $password_db = '';

            try {
                // PDOインスタンスを生成
                $pdo = new PDO($dsn, $username_db, $password_db);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // バリデーションエラーメッセージ
            $error_message = '';

            // バリデーションチェック
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error_message .= "Please fill in all fields.<br>";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
                $error_message .= "Please enter a valid Gmail address.<br>";
            }

            if (strlen($password) < 4 || !preg_match("/[A-Z]+/", $password) || !preg_match("/[a-z]+/", $password) || !preg_match("/[0-9]+/", $password) || !preg_match("/[!@#$%^&*()\-_=+{};:,<.>ยง?]+/", $password)) {
                $error_message .= "Password must be at least 4 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.<br>";
            }

            if ($password != $confirm_password) {
                $error_message .= "Passwords do not match.<br>";
            }

            // エラーメッセージがある場合に表示
            if (!empty($error_message)) {
                echo "<div class='error'>$error_message</div>";
            } else {
                // データベースへの処理を行う
                // メールアドレスの重複をチェック
            $check_query = "SELECT * FROM customers WHERE email = :email";
            $check_statement = $pdo->prepare($check_query);
            $check_statement->bindParam(':email', $email, PDO::PARAM_STR);
            $check_statement->execute();

            if ($check_statement->rowCount() > 0) {
                // 重複がある場合の処理
                echo "<div class='error'>Email address already exists.</div>";
            } else {
                // パスワードのハッシュ化
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 新規ユーザーの挿入
                $insert_query = "INSERT INTO customers (username, email, password) VALUES (:username, :email, :password)";
                $insert_statement = $pdo->prepare($insert_query);
                $insert_statement->bindParam(':username', $username, PDO::PARAM_STR);
                $insert_statement->bindParam(':email', $email, PDO::PARAM_STR);
                $insert_statement->bindParam(':password', $hashed_password, PDO::PARAM_STR);

                if ($insert_statement->execute()) {
                    // 挿入が成功した場合の処理
                    echo "<div class='success'>Registration successful. You can now <a href='login.php'>login</a>.</div>";
                    exit;
                } else {
                    // 挿入が失敗した場合の処理
                    echo "<div class='error'>Registration failed. Please try again.</div>";
                }
            }
            }
                } catch (PDOException $e) {
                    // データベース接続エラーの処理
                    echo "Connection failed: " . $e->getMessage();
                    exit;
                }
            }
        ?>
        <script>
            // パスワード表示の切り替え用JavaScript関数
            function togglePassword(fieldId) {
                var passwordField = document.getElementById(fieldId);
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                } else {
                    passwordField.type = 'password';
                }
            }
        </script>
    </div>
</body>
</html>