<?php
// セッションを開始
session_start();

// ユーザー名を取得
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $id = $_SESSION['user_id'];
} else {
    $username = ""; // ユーザー名が保存されていない場合は空の文字列を設定
}

//セッションでidを引っ張ってきてクエリを実行

// データベースに接続するための情報
$dsn = 'mysql:host=localhost;dbname=shopping;charset=utf8';
$username_db = 'root';
$password_db = '';

// データベースに接続
try {
    $pdo = new PDO($dsn, $username_db, $password_db);
    // PDOエラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 商品情報を取得するクエリ
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(array(':id' => $_GET['id']));
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // データベースに接続できない場合のエラーメッセージ
    echo "Failed to connect to the database.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品詳細ページ</title>
    <style>
    body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            color: #333;
            line-height: 1.6;
            text-align: center;
        }

        h1, h2 {
            color: #444;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 40px 20px;
        }

        .review {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .user-info {
            margin-bottom: 10px;
        }

        .user-name {
            font-weight: bold;
            color: #555;
        }

        .rating {
            color: #DCF3FA;
        }

        .star-filled {
            color: #D6CEF0;
        }

        .review-title {
            font-size: 18px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .post-date {
            color: #888;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .review-content {
            font-size: 16px;
            line-height: 1.5;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .image-container {
        position: relative;
        margin-top: 30px;
        }

        .image-container::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: calc(100% - 700px);
        height: calc(100% - 20px);
        border-image-source: repeating-linear-gradient(-45deg, #FAF6CD 0, #FAF6CD 3px, rgba(0, 0, 0, 0) 0, rgba(0, 0, 0, 0) 6px);
        border-width: 20px;
        border-image-slice: 20;
        border-image-repeat: round;
        border-style: solid;
        z-index: 1;
        }

        /* リンクにマウスが乗ったときのスタイル */
        a:hover {
        color: #ff6666; /* マウスが乗ったときのリンクのテキストカラーを薄赤色に設定 */
        }

        .rating {
        color: #ccc; /* 評価平均値の星マークの色 */
        }

        .star-filled {
        color: #fdd835; /* 評価された星マークの色 */    
        }

        .review-star-filled {
        color: #A7E126; /* レビューされた星の色 */
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>商品詳細ページ</h1>
    <!-- トップに戻るリンク -->
    <a href="main.php">トップに戻る</a><br>
    
    <!-- 商品画像コンテナ -->
    <div class="image-container">
    <!-- 商品画像 -->
    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['product_name']; ?>">
    </div>

    <?php if ($product['average_rating'] !== null) : ?>
    <p>評価平均値: 
    <?php
    // 平均評価値に応じて評価を表示
    $average_rating = round($product['average_rating'], 2);
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $average_rating) {
            echo "<span class='star-filled'>&#9733;</span>"; // ★を表示
        } else {
            echo "<span class='star'>&#9733;</span>"; // ★を表示
        }
    }
    ?>
    </p>
    <?php else : ?>
        <p>評価はまだありません</p>
    <?php endif; ?>

    

    <!-- 購入フォーム -->
    <form action="add_to_cart.php" method="post">
        <label for="quantity">数量:</label>
        <input type="number" id="quantity" name="quantity" min="1" value="1" required><br>
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
    
        <!-- カートに追加して購入 purchase.phpへ-->
        <input type="submit" name="action" value="カートに追加して購入ページへ">
        <!-- カートに追加して買い物を続ける detail.phpへリダイレクト -->
        <input type="submit" name="action" value="カートに追加して買い物を続ける">
    </form>

    <!-- 商品に関する説明を表示 -->
    <p><?php echo $product['description']; ?></p>

    <!-- レビュー投稿フォーム -->
    <h2>レビュー投稿フォーム</h2>
    <form action="submit_review.php" method="post">
        <!-- ユーザー名を隠しフィールドとして追加 -->
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <input type="hidden" name="user_id" value="<?php echo $id; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <label for="title">タイトル:</label>
        <input type="text" id="title" name="title" required><br>
        <label for="review">レビュー内容:</label><br>
        <textarea id="review" name="review" rows="4" cols="50" required></textarea><br>
        <label for="rating">評価（5段階）:</label>
        <input type="number" id="rating" name="rating" min="1" max="5" required><br>
        
        <input type="submit" value="投稿">
    </form>

    <!-- レビュー投稿内容の表示欄 -->
    <h2>レビュー投稿内容</h2>
    <?php
    // レビュー情報を取得するクエリ
    $stmt = $pdo->prepare("SELECT r.*, c.username FROM reviews r LEFT JOIN customers c ON r.customer_id = c.id WHERE r.product_id = :product_id ORDER BY r.post_date DESC");
    $stmt->execute(array(':product_id' => $product['id']));
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // レビューがある場合、それぞれのレビューを表示
    if ($reviews) {
    foreach ($reviews as $review) {
        echo "<div class='review'>";
        echo "<div class='user-info'>";
        // ユーザー名を改行して表示
        echo "<div class='user-name'>ユーザー名：" . $review['username'] . "<br></div>"; // ユーザー名の後に <br> タグを挿入
        echo "<div class='rating'>";
        // 星マークで評価を表示する部分
        $rating = $review['rating'];
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                echo "<span class='review-star-filled'>&#9733;</span>"; // ★を表示
            } else {
                echo "<span class='review-star'>&#9733;</span>"; // ★を表示
            }
        }
        echo "</div>";
        echo "<p class='review-title'>" . $review['title'] . "</p>"; // レビュータイトルを表示
        echo "</div>";
        echo "<p class='post-date'>投稿日時: " . $review['post_date'] . "</p>";
        echo "<p class='review-content'>" . $review['review'] . "</p>";
        echo "</div>";
        }
    } else {
    echo "<p>レビューはまだありません。</p>";
    }
?>
    </div>
</body>
</html>