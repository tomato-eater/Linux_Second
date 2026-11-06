<?php
// header.php
// すべてのページの先頭で読み込む共通ヘッダー
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>