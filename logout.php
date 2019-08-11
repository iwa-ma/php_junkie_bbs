<?php

    // セッション開始
    session_start();
    // セッション変数を全て削除
    $_SESSION = array();

    // セッションの登録データを削除
    session_destroy();
    header('Location: /bbs/thread_list.php');
?>