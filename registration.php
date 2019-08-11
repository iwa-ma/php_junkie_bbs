<?php require('base.php');?>
<?php
    $now_time =date("Y-m-d H:i:s");

    @session_start();
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];

    $image_up_st = 0;

    // 接続
    $link = new mysqli($host , $username, $passwd, $dbname);
    $posted_puts_sql = $link->prepare( "SELECT * FROM posted where status = 1");

    $posted_puts_sql->execute();
    $result = $posted_puts_sql->get_result();
    $posted_puts = $result->fetch_all(MYSQLI_ASSOC);

    if(strlen($_FILES['img_deta']['name'])>0){
       $filename="image/".$_FILES["img_deta"]["name"];
       if(! move_uploaded_file($_FILES["img_deta"]["tmp_name"],$filename) )
       {
        print "name=";
        print $filename;
        print '<div class="comment">画像アップロードに失敗しました</div>';
        exit;
       }else{
        $image_up_st = 1;
       }
    }

    //登録済みデータによって、新規投稿orコメント追記を分岐
    if(count($posted_puts) == 0){
      $posted_INSERT_sql = $link->prepare( "INSERT INTO `posted` (no,post_no,poster_name,poster_id,create_date,last_update,title,content_text,image_st,image_pass,status)
      VALUES (NULL, '1' , ? ,? ,? ,? ,? ,? ,? ,?,'1')");

      //新規登録
      $posted_INSERT_sql->bind_param("ssssssis",$_POST['poster_name'],$_SESSION['user_id'],$now_time,$now_time,
      $_POST['title'],$_POST['content_text'],$image_up_st,$filename);
    }else{
      //コメント登録
      $post_no_next = (count($posted_puts) +1);
      $posted_INSERT_sql = $link->prepare( "INSERT INTO `posted` (no,post_no,poster_name,poster_id,create_date,last_update,title,content_text,image_st,image_pass,status)
      VALUES (NULL, $post_no_next , ? ,? ,? ,? ,? ,? ,? ,?,'1')");

      //新規登録
      $posted_INSERT_sql->bind_param("ssssssis",$_POST['poster_name'],$_SESSION['user_id'],$now_time,$now_time,
      $_POST['title'],$_POST['content_text'],$image_up_st,$filename);
    }
      switch ($posted_INSERT_sql->execute()) {
        case true:
          //echo '<BR><BR>登録が成功しました。ログイン画面に戻ってログインして下さい。';
          header('Location: /bbs/thread_list.php');
          break;
      case false:
          echo '<div class="comment">登録に失敗しました。管理者に連絡して下さい。</div>';
          break;
       }
?>