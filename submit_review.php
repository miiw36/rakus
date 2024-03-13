<?php
// データベース接続情報
$host = 'localhost'; // ホスト名
$dbname = 'shopping'; // データベース名
$username = 'root'; // ユーザー名
$password = ''; // パスワード（空白）

// レビューの投稿情報を取得
$product_id = $_POST['product_id'];
$customer_id = $_POST['user_id'];
$title = $_POST['title'];
$review = $_POST['review'];
$rating = $_POST['rating'];

try {
    // PDOインスタンスを作成
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // エラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 既存のレビューを確認するためのクエリ
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = :product_id AND customer_id = :customer_id");
    $stmt->execute(array(':product_id' => $product_id, ':customer_id' => $customer_id));
    $existing_review = $stmt->fetch(PDO::FETCH_ASSOC);

    // 既存のレビューがある場合
    if ($existing_review) {
        // レビューを更新する
        $stmt_update = $pdo->prepare("UPDATE reviews SET title = :title, review = :review, rating = :rating WHERE product_id = :product_id AND customer_id = :customer_id");
        $stmt_update->bindParam(':title', $title);
        $stmt_update->bindParam(':review', $review);
        $stmt_update->bindParam(':rating', $rating);
        $stmt_update->bindParam(':product_id', $product_id);
        $stmt_update->bindParam(':customer_id', $customer_id);
        $stmt_update->execute();
        echo "レビューが更新されました。";
    } else {
        // 新しいレビューを挿入する
        $stmt_insert = $pdo->prepare("INSERT INTO reviews (product_id, customer_id, title, review, rating) VALUES (:product_id, :customer_id, :title, :review, :rating)");
        $stmt_insert->bindParam(':product_id', $product_id);
        $stmt_insert->bindParam(':customer_id', $customer_id);
        $stmt_insert->bindParam(':title', $title);
        $stmt_insert->bindParam(':review', $review);
        $stmt_insert->bindParam(':rating', $rating);
        $stmt_insert->execute();
        echo "レビューが投稿されました。";
    }

    // 平均評価値を再計算して更新する関数を呼び出す
    update_average_rating($pdo, $product_id);

} catch(PDOException $e) {
    // エラーメッセージを表示
    echo "エラー: " . $e->getMessage();
}

// データベース接続を閉じる
$pdo = null;

// 平均評価値を再計算して更新する関数
function update_average_rating($pdo, $product_id) {
    // 商品に関連付けられた全てのレビューの平均評価値を再計算するクエリを実行
    $stmt = $pdo->prepare("SELECT AVG(rating) AS average_rating FROM reviews WHERE product_id = :product_id");
    $stmt->execute(array(':product_id' => $product_id));
    $average_rating = $stmt->fetch(PDO::FETCH_ASSOC)['average_rating'];

    // 平均評価値をproductsテーブルに保存するクエリを実行
    $stmt = $pdo->prepare("UPDATE products SET average_rating = :average_rating WHERE id = :product_id");
    $stmt->execute(array(':average_rating' => $average_rating, ':product_id' => $product_id));
}

?>