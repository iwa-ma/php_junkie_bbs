<?php
	class Charge {
		private $host     = null;
		private $username = null;
		private $passwd   = null;
		private $dbname   = null;
		private $mysqli   = null;
		private $message  = '';

		public function __construct() {
			require_once('./base.php');
			$this->host     = $DB_acces['host'];
			$this->username = $DB_acces['username'];
			$this->passwd   = $DB_acces['passwd'];
			$this->dbname   = $DB_acces['dbname'];
			$this->message  = '<pre><br>';
			// 接続
			$this->mysqli = new mysqli($this->host , $this->username, $this->passwd, $this->dbname);
			if($this->mysqli->connect_error) {
				$this->message .= $this->mysqli->connect_error.'<br>';
				exit();
			}
			$this->mysqli->set_charset('utf8');
		}

		public function __destruct() {
			if($this->mysqli !== null) $this->mysqli->close();
			$this->mysqli = null;
			$this->message .= '</pre>';
		}

		// userテーブルにsubscription['id'], start_date, update_date を登録
		public function addSubscriptionId($DB_acces, $user_id, $plan, $id, $start_date, $update_date) {
			if($this->mysqli == null) {
				$this->message .= 'ERROR ChargeClass.php::addSubscriptionId() <br>';
				exit();
			}
			$sql = 'UPDATE user SET plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?';
			$email = '';
			if($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param('isssi', $p, $i, $sd, $ud, $u_id);
				$p = $plan;
				$i = $id;
				$sd = date('Y-m-d', $start_date);
				$ud = date('Y-m-d', $update_date);
				$u_id = $user_id;
				if($stmt->execute()) {
					$this->message .= 'ERROR ChargeClass.php::addSubscriptionId() sqlの実行に失敗<br>';
				}
				$stmt->close();
			} else {
				$this->message .= 'ERROR ChargeClass.php::addSubscriptionId() prepareの実行に失敗<br>';
			}
		}

		// 定期課金更新
		function updateSubscription($DB_acces, $user_id, $plan = null, $subscription_id = null, $start_date = null, $update_date = null) {
			if($this->mysqli == null) {
				$this->message .= 'ERROR ChargeClass.php::updateSubscription() <br>';
				exit();
			}
			$mysqli->set_charset('utf8');
			$sql = 'UPDATE user SET plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?';
			$email = '';
			if($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param('isssi', $p, $i, $sd, $ud, $u_id);
				$p = $plan;
				$i = $subscription_id;
				$sd = date('Y-m-d', $start_date);
				$ud = date('Y-m-d', $update_date);
				$u_id = $user_id;
				if(!$stmt->execute()) {
					$this->message .= 'ERROR ChargeClass.php::updateSubscription() sqlの実行に失敗<br>';
				}
				$stmt->close();
			} else {
				$this->message .= 'ERROR ChargeClass.php::updateSubscription() prepareの実行に失敗<br>';
			}
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

			$mysqli->set_charset('utf8');
			$sql = 'UPDATE user SET status = ?, plan = ?, subscription_id = ?, start_date = ?, update_date = ? WHERE no = ?';
			$email = '';
			if($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param('iisssi', $s, $p, $i, $sd, $ud, $u_id);
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

			$mysqli->set_charset('utf8');
			$sql = 'SELECT subscription_id FROM user WHERE no = ?';
			$subscription_id = '';
			if($stmt = $mysqli->prepare($sql)) {
				$stmt->bind_param('i', $u_id);
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
	}
?>
