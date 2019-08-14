<?php require(dirname(__FILE__) ."/temp/header.html"); ?>

    <div class="title">連絡掲示板(id登録処理）</div>
<?php
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $link = new mysqli($host , $username, $passwd, $dbname);

    if ($link->connect_error) {
        echo $link->connect_error;
        exit();
    } else {
        $link->set_charset("utf8");

        //入力されたe-mailアドレスが登録済みか、無効になっているかチェック
            $link->set_charset('utf8');
            $user_select_sql = $link->prepare( "SELECT * FROM user where email = ? ");
            $input_id = $_POST['id'];
            $user_select_sql->bind_param("s",$input_id);
            $user_select_sql->execute();
            $result = $user_select_sql->get_result();
            $user_select_all = $result->fetch_all(MYSQLI_ASSOC);

            if(count($user_select_all)==0){
        //未登録の場合
                $email_check = true;
                $add_type    = "insert";
            }else{

            $link->set_charset('utf8');
            $user_select2_sql = $link->prepare( "SELECT * FROM user where email = ? AND  status= 0 ");
            $input_id = $_POST['id'];
            $user_select2_sql->bind_param("s",$input_id);
            $user_select2_sql->execute();
            $result = $user_select2_sql->get_result();
            $user_select2_all = $result->fetch_all(MYSQLI_ASSOC);
        
                if (count($user_select2_all)==1) :
                    //登録済みで無効の場合　
                    $email_check = true;
                    $add_type    = "update";
                else :
                    //登録済みで有効の場合　
                    $email_check = false;
                    $add_type    = false;
                endif;
            }
    }
?>

<?php if($email_check==true){?>
<?php
    //登録処理実行(事前チェックの結果でupdateかinsertか分岐)
      $password_text = password_hash($_POST['password1'], PASSWORD_DEFAULT);

       if ($add_type == "insert") :
            $user_add_sql = $link->prepare( "INSERT INTO user (no,email,password,status)VALUES (NULL, ? , ? ,'1')");
            $user_add_sql->bind_param("ss",$_POST['id'],$password_text);
        elseif ($add_type == "update") :
            $user_add_sql = $link->prepare( "UPDATE `user` SET status= 1,password = ? WHERE email = ?");
            $user_add_sql->bind_param("ss",$password_text,$_POST['id']);
        endif;

      switch ($user_add_sql->execute()) {
        case true:
          echo '<div class="comment">登録が完了しました。ログイン画面に戻ってログインして下さい。</div>';
          break;
      case false:
          echo '<div class="comment">登録に失敗しました。管理者に連絡して下さい。</div>';
          break;
       }
?>
<?php }else{ ?>
       <div class="comment">入力されたidは登録済みです。</div>
<?php }//if ($email_check==true){ ?>

<?php  $link->close();  ?>
        <ul>
            <BR>
            <li><a href="index.php">ログイン画面に戻る</a></li>
        </ul>

<?php require(dirname(__FILE__) ."/temp/footer.html"); ?>