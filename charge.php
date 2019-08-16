<?php
  session_start();
  require_once('./stripe/init.php');
  require_once('./../base.php');
  date_default_timezone_set('Asia/Tokyo');
  // APIのシークレットキー
  \Stripe\Stripe::setApiKey('sk_test_gIqVgfVFoQ0UtljO7IdQzZLw00QkM8FWWj');

  // 退会処理
  if(isset($_POST['cancel'])) {
    $subscription_id = getSubscriptionId($DB_acces, $_SESSION['user_id']);
    if($subscription_id !== null) {
      try {
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        $subscription->cancel();
      } catch(Exception $e) {
        // エラーの表示
        echo "ERORR:" . $e->getMessage();
        exit;
      }
      unsubscribeUser($DB_acces, $_SESSION['user_id']);
    }
    header('Location: ./../login.php');
    exit();
  }

  // priceが送信されてなければログイン画面に戻す TODO ログアウトも一緒にしたほうがいい
  if(!isset($_POST['price'])) {
    header('Location: /../login.php');
  }

  $charge_id = null;
  $amount = [
              "1000" => 1000,
              "3000" => 3000,
              "5000" => 5000,
            ];
  $price = htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8');
  // 無効な値が送られてきているためログイン画面に戻す　TODO ログアウトも一緒にしたほうがいい
  if(!array_key_exists($price, $amount)) {
    header('Location: /../login.php');
  }

  $token  = $_POST['stripeToken'];
  $email  = $_POST['stripeEmail'];

  try {
    // プロダクトの作成
    $product = \Stripe\Product::create([
      'name' => 'むちょこ架空請求',
      'type' => 'service',
    ]);

    // プランの作成
    $plan = \Stripe\Plan::create([
      'currency' => 'jpy',
      'interval' => 'month',
      'product' => $product['id'],
      'nickname' => '月額'.$amount[$price].'円プラン',
      'amount' => $amount[$price],
      'usage_type' => 'licensed',
    ]);

    // プランの変更
    if(!is_null($subscription_id = getSubscriptionId($DB_acces, $_SESSION['user_id']))) {
      $subscription = \Stripe\Subscription::retrieve($subscription_id);
      if($subscription !== "") {
        $subscription = \Stripe\Subscription::update($subscription_id, [
          'cancel_at_period_end' => false,
          'items' => [
            [
              'id' => $subscription['items']['data'][0]['id'],
              'plan' => $plan['id'],
            ],
          ],
        ]);
        updateSubscription($DB_acces, $_SESSION['user_id'], $amount[$price], $subscription['id'], $subscription['start_date'], $subscription['current_period_end']);
        header("Location: ./../index.php");
        exit;
      }
    }

    // 顧客を作成
    $customer = \Stripe\Customer::create([
        'email' => $email,
        'source'  => $token,
    ]);

    // サブスクリプションの作成
    $subscription = \Stripe\Subscription::create([
    	'customer' => $customer['id'],
    	'items' => [
          ['plan' => $plan['id']],
        ],
    ]);

    // キャンセル・変更をするためにDBにsubscription['id']を登録しておく
    addSubscriptionId($DB_acces, $_SESSION['user_id'], $amount[$price], $subscription['id'], $subscription['start_date'], $subscription['current_period_end']);

    // 購入完了画面にリダイレクト
    header("Location: ./../index.php");
    exit;
  } catch(Exception $e) {
    if($charge_id !== null){
      \Stripe\Refund::create([
          'charge' => $charge_id,
      ]);
    }

    // エラーの表示
    echo "ERORR:" . $e->getMessage();
    exit;
  }
?>


<?php
  // userテーブルにsubscription['id'], start_date, update_date を登録
  function addSubscriptionId($DB_acces, $user_id, $plan, $id, $start_date, $update_date) {
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $mysqli = new mysqli($host , $username, $passwd, $dbname);
    if($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    }

    $mysqli->set_charset("utf8");
    $sql = "UPDATE user SET plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?";
    $email = "";
    if($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param("isssi", $p, $i, $sd, $ud, $u_id);
      $p = $plan;
      $i = $id;
      $sd = date('Y-m-d', $start_date);
      $ud = date('Y-m-d', $update_date);
      $u_id = $user_id;

      $stmt->execute();
      $stmt->close();
    }
    $mysqli->close();
  }

  // 定期課金更新
  function updateSubscription($DB_acces, $user_id, $plan = null, $subscription_id = null, $start_date = null, $update_date = null) {
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $mysqli = new mysqli($host , $username, $passwd, $dbname);
    if($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    }

    $mysqli->set_charset("utf8");
    $sql = "UPDATE user SET plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?";
    $email = "";
    if($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param("isssi", $p, $i, $sd, $ud, $u_id);
      $p = $plan;
      $i = $subscription_id;
      $sd = date('Y-m-d', $start_date);
      $ud = date('Y-m-d', $update_date);
      $u_id = $user_id;
      $stmt->execute();
      $stmt->close();
    } else {
      // クエリに失敗した場合はログイン画面に戻す
      // echo 'クエリにしっぱい';
      // header('Location: ./../login.php');
    }

    $mysqli->close();
  }

  // 退会処理
  function unsubscribeUser($DB_acces, $user_id) {
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $mysqli = new mysqli($host , $username, $passwd, $dbname);
    if($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    }

    $mysqli->set_charset("utf8");
    $sql = "UPDATE user SET status = ?, plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?";
    $email = "";
    if($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param("iisssi", $s, $p, $i, $sd, $ud, $u_id);
      $s = 0;
      $p = null;
      $i = null;
      $sd = null;
      $ud = null;
      $u_id = $user_id;
      $stmt->execute();
      $stmt->close();
    } else {
      // クエリに失敗した場合はログイン画面に戻す
      // echo 'クエリにしっぱい';
      // header('Location: ./../login.php');
    }

    $mysqli->close();
  }

  // subscription_id を取得する
  function getSubscriptionId($DB_acces, $user_id) {
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $mysqli = new mysqli($host , $username, $passwd, $dbname);
    if($mysqli->connect_error) {
      echo $mysqli->connect_error;
      exit();
    }

    $mysqli->set_charset("utf8");
    $sql = "SELECT subscription_id FROM user WHERE no = ?";
    $subscription_id = "";
    if($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param("i", $u_id);
      $u_id = $user_id;
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      $subscription_id = $id;
      $stmt->close();
    } else {
      // クエリに失敗した場合はログイン画面に戻す
      // echo 'クエリにしっぱい';
      // header('Location: ./../login.php');
    }
    $mysqli->close();
    return $subscription_id;
  }

  //
  function testDrawData($product, $plan, $custmer, $subscription) {
    echo '<pre>';
    var_dump($_SESSION);
    echo '</pre>';
    echo '<pre>';
    var_dump($_POST);
    echo '</pre>';
    echo '<pre>';
    var_dump($product);
    echo '</pre>';
    echo '<pre>';
    var_dump($plan);
    echo '</pre>';
    echo '<pre>';
    var_dump($custmer);
    echo '</pre>';
    echo '<pre>';
    var_dump($subscription);
    echo '</pre>';
  }
 ?>
