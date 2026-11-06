<?php
// 1. データベース接続情報（環境に合わせて書き換えてください）
$host   = 'mysql';
$dbname = 'data_master';
$user   = 'root';
$pass   = 'p@ssword';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo '接続エラー: ' . $e->getMessage();
    exit;
}

// 画面の状態を管理する変数（'top', 'new_user', 'load'）
$mode = 'top';
$message = '';
$user_list = [];

// 送信されたフォームの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'go_new':
                $mode = 'new_user';
                break;
                
            case 'create':
                // 新規ユーザー作成処理
                $name = trim($_POST['name'] ?? '');
                if ($name !== '') {
                    try {
                        // プリペアドステートメントで安全にINSERT（idは自動追加されるため、nameのみ指定）
                        $stmt = $pdo->prepare('INSERT INTO user (name) VALUES (:name)');
                        $stmt->execute([':name' => $name]);
                        $message = 'ユーザー「' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '」を作成しました！';
                    } catch (PDOException $e) {
                        $message = 'エラー：作成に失敗しました。';
                    }
                } else {
                    $message = '名前を入力してください。';
                    $mode = 'new_user'; // 入力エラー時は新規作成画面にとどまる
                }
                break;
                
            case 'go_load':
                $mode = 'load';
                // ロード画面用にユーザー一覧を取得
                $stmt = $pdo->query('select * from user');
                $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'cancel':
            default:
                $mode = 'top';
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー管理システム</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f9f9f9; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        .btn { padding: 10px 20px; font-size: 16px; margin: 5px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #f0f0f0; }
        .btn-primary { background: #007bff; color: white; border: none; }
        .btn-danger { background: #dc3545; color: white; border: none; }
        .input-text { padding: 10px; font-size: 16px; width: 80px; width: calc(100% - 24px); margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        .message { color: #28a745; font-weight: bold; margin-bottom: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
        .star-rating { color: #ffcc00; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">

    <!-- メッセージ表示（作成成功時など） -->
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- ------------------ 1. 初期トップ画面 ------------------ -->
    <?php if ($mode === 'top'): ?>
        <h2>メニュー</h2>
        <form method="POST">
            <button type="submit" name="action" value="go_new" class="btn btn-primary">ニューユーザー</button>
            <button type="submit" name="action" value="go_load" class="btn">ロード</button>
        </form>

    <!-- ------------------ 2. ニューユーザー作成画面 ------------------ -->
    <?php elseif ($mode === 'new_user'): ?>
        <h2>新規ユーザー作成</h2>
        <form method="POST">
            <input type="text" name="name" class="input-text" placeholder="名前を入力してください" autocomplete="off">
            <br>
            <button type="submit" name="action" value="create" class="btn btn-primary">作成</button>
            <button type="submit" name="action" value="cancel" class="btn btn-danger">キャンセル</button>
        </form>

    <!-- ------------------ 3. ロード（一覧表示）画面 ------------------ -->
    <?php elseif ($mode === 'load'): ?>
        <h2>ユーザーをロード</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($user_list)): ?>
                    <?php foreach ($user_list as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <!-- ロード後の処理用ボタン（必要に応じてリンク先やアクションを設定してください） -->
                                <button type="button" class="btn" onclick="alert('ID: <?php echo $user['id']; ?> をロードしました')">選択</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">ユーザーが登録されていません。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <form method="POST">
            <button type="submit" name="action" value="cancel" class="btn">戻る</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>
