<?php
  session_start();
  date_default_timezone_set('Asia/Tokyo');
  require_once('./../base.php');

  // ログインしてなかったらログインページへ
  $_SESSION['user_id'] = 1;       // TODO フォームからください
  if(!isset($_SESSION['user_id'])) {
    header('Location: ./../login.php');
  }

  // user_id(userテーブルのno)からemail(メールアドレス)を取得する
  $host     = $DB_acces['host'];
  $username = $DB_acces['username'];
  $passwd   = $DB_acces['passwd'];
  $dbname   = $DB_acces['dbname'];
  // 接続
  $mysqli = new mysqli($host , $username, $passwd, $dbname);
  if($mysqli->connect_error) {
    echo $mysqli->connect_error;
    echo '<a href="./../login.php">ログイン画面へ</a>';
    exit();
  }
  $mysqli->set_charset("utf8");
  $sql = "SELECT email FROM user WHERE no = ?";
  $email = "";
  if($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $user_id = $_SESSION['user_id'];
    $stmt->execute();
    $stmt->bind_result($e);
    $stmt->fetch();
    $email = $e;
    $stmt->close();
  } else {
    // クエリに失敗した場合はログイン画面に戻す
    header('Location: ./../login.php');
  }
  $mysqli->close();
 ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>購入画面</title>
    <style type="text/css">
    .stripe-button-el {
      width: 100px;
      height: 200px;
      max-width: 100%;
      margin: 10px;
      display: inline-block !important;
    }
    .stripe-button-el span {
      font-size: 18px;
      padding-top: 15px;
      height: 200px !important;
      text-align: center;
      vertical-align: middle !important;
    }
    form {
      float: left;
    }
    </style>
</head>

<body>
    <form action="./charge.php" method="POST">
        <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="pk_test_zu1TYmzDu3VJGeeuiDY3zvFs00HsW1dXMD"
          data-amount="1000"
          data-email="<?=$email?>"
          data-name="むちょこチーム開発テスト"
          data-locale="auto"
          data-allow-remember-me="false"
          data-label="¥PLAN A ¥1,000/月 Get Started"
          data-currency="jpy">
        </script>
        <input type="hidden" name="price" value="1000">
    </form>
    <form action="./charge.php" method="POST">
      <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="pk_test_zu1TYmzDu3VJGeeuiDY3zvFs00HsW1dXMD"
        data-amount="3000"
        data-email="<?=$email?>"
        data-name="むちょこチーム開発テスト"
        data-locale="auto"
        data-allow-remember-me="false"
        data-label="¥PLAN B ¥3,000/月 Get Started"
        data-currency="jpy">
      </script>
      <input type="hidden" name="price" value="3000">
    </form>
    <form action="./charge.php" method="POST">
      <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="pk_test_zu1TYmzDu3VJGeeuiDY3zvFs00HsW1dXMD"
        data-amount="5000"
        data-email="<?=$email?>"
        data-name="むちょこチーム開発テスト"
        data-locale="auto"
        data-allow-remember-me="false"
        data-label="¥PLAN C ¥5,000/月 Get Started"
        data-currency="jpy">
      </script>
      <input type="hidden" name="price" value="5000">
    </form>
    <form action="./charge.php" method="POST">
      <button type="submit" name="cancel" value="退会" class="stripe-button-el"><span style="display:block; min-height:30px;">退会</span></button>
    </form>
    <p>メールアドレス：<?=$email?></p>
    <p>カード番号：4242 4242 4242 4242</p>
</body>
</html>
