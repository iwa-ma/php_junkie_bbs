<?php require(dirname(__FILE__) ."/temp/header.html"); ?>

    <div class="title">連絡掲示板(ログイン処理）</div>
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
    }
       //入力されたe-mailアドレスが登録済みかチェック
       $link->set_charset('utf8');
       $user_select_sql = $link->prepare( "SELECT * FROM user where email = ? ");
       $input_id = $_POST['id'];
       $user_select_sql->bind_param("s",$input_id);
       $user_select_sql->execute();
       $result = $user_select_sql->get_result();
       $user_select_all = $result->fetch_all(MYSQLI_ASSOC);

       if(count($user_select_all)>=1){
           $email_check = true;
       }else{
           $email_check = false;
       }

       //入力されたe-mailアドレスが登録済みandパスワード認証ok
       if($email_check==true){
            if(password_verify($_POST['password'],$user_select_all[0]['password'])){
                $password_check = true;
                session_start();
                $_SESSION['user_id'] = $input_id;
                header('Location: /bbs/thread_list.php');
            }else{
                $password_check = false;
            }
        }

       if($email_check==false or $password_check == false){
                echo "<BR>";
                echo '<div class="comment">ログインに失敗。id又はパスワードが間違っています。</div>';
                echo "<BR>";
                echo '<div class="comment"><a href="index.php">ログイン画面に戻る</a></div>';
                echo "</ul>";
        }
?>

