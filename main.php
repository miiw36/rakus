<?php
// セッションを開始
session_start();

// セッションにユーザー名が保存されているか確認し、表示する
if (isset($_SESSION['username'])) {
    $welcome_message = "Welcome, " . $_SESSION['username'];
} else {
    $welcome_message = "Welcome, Guest"; // ユーザーがログインしていない場合はゲストと表示するなど、適切な表示を行う
}

// ユーザーがログインしている場合、セッションにユーザー名を保存
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    $username = ""; // ログインしていない場合は空の文字列を設定
}

// データベースに接続するための情報
$dsn = 'mysql:host=localhost;dbname=shopping;charset=utf8';
$username_db = 'root';
$password_db = '';

// データベースに接続
try {
    $pdo = new PDO($dsn, $username_db, $password_db);
    // PDOエラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    <title>Main Page</title>
    <link rel="stylesheet" href="styles.css"> <!-- CSSファイルをリンク -->
    <style>
        
    </style>
</head>
<body>
<?php
    echo '<div class="container">';
    echo '<h1>Main Page</h1>';
    echo '<p>' . $welcome_message . '</p>';
    
        
    // データベースへの接続情報
    $dsn = 'mysql:host=localhost;dbname=shopping;charset=utf8';
    $username_db = 'root';
    $password_db = '';

        // ここに他の商品情報を表示
    try {
        $pdo = new PDO($dsn, $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 商品情報を取得するクエリ
        $stmt = $pdo->query("SELECT * FROM products");

        // Flexboxの開始
    echo '<div class="products-container">';


        // 商品情報をループして表示
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="product">';
            echo '<div class="product-info-container">';  // ここでループ外に移動
            echo '<a href="detail.php?id=' . $row['id'] . '">';
            echo '<img src="' . $row['image'] . '" width="250" height="250"><br>';
            echo '</a>';
            
            // レビュー情報を取得するクエリ
            $review_stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = :product_id");
            $review_stmt->execute(array(':product_id' => $row['id']));
            
            // 平均評価を表示
            $total_reviews = 0;
            $total_rating = 0;
            while ($review_row = $review_stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_reviews++;
                $total_rating += $review_row['rating'];
            }
            echo '<div class="product-info">';
            echo '<p class="product-name">' . $row['product_name'] . '</p>';
            // 商品情報を表示する部分を修正

// 平均評価値と価格の表示を修正
if ($total_reviews > 0) {
    $average_rating = $total_rating / $total_reviews;
    echo '<p class="average-rating">';
    // 星マークで評価を表示する部分
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $average_rating) {
            echo "<span class='star-filled'>&#9733;</span>"; // ★を表示
        } else {
            echo "<span class='star'>&#9733;</span>"; // ★を表示
        }
    }
    echo ' (' . round($average_rating, 2) . ')'; // 平均評価の数値を表示
    echo '</p>';
} else {
    echo '<p class="average-rating">評価平均値: まだレビューがありません</p>';
}
echo '<p class="price">価格: ' . number_format($row['price'], 0) . '円</p>'; //
echo '</div>'; // .product-info を閉じる
echo '</div>'; // .product-info-container を閉じる
echo '</div>'; // .product を閉じる
        }

        // Flexboxの終了
    echo '</div>'; // .products-container を閉じる
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    echo '</div>'; // .container を閉じる
        ?>
    </div>
    
</body>
</html>