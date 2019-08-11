<?php
require('base.php');
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $link = new mysqli($host , $username, $passwd, $dbname);

    $post_del_del = $link->prepare( "UPDATE `posted` SET status= 0 WHERE no = ?");
    $post_del_del->bind_param("i",$_GET['no']);
    $post_del_del->execute();

    header('Location: /bbs/thread_list.php');
    exit;

?>