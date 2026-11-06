<?php

require_once 'header.php';

// 1. データベース接続情報
$host   = 'mysql';
$dbname = 'data_master';
$user   = 'root';
$pass   = 'p@ssword'; // 環境に合わせて設定

try {
    // PDO接続
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. SQLの作成と実行
    $sql = 'select * from card';
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '接続エラー: ' . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>MySQLデータ一覧表示</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>

<h1>カード一覧</h1>

<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>ランク</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <!-- XSS対策として htmlspecialchars を使用 -->
                    <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    
                    <td>
                        <span class="star-rating">
                            <?php 
                            // データベースの数値（例: 3）を取得
                            $rankValue = (int)$row['card_rank']; 
                            
                            // str_repeat関数を使って、数値の回数分「★」を繰り返す
                            echo str_repeat('★', $rankValue); 
                            ?>
                        </span>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">データがありません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
