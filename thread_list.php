<?php require(dirname(__FILE__) ."/temp/header.html"); ?>

    <div class="title">連絡掲示板(投稿表示）</div>    
    <?php
        // セッション開始
        @session_start();
        if (isset($_SESSION['user_id'])) {
        }elseif (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            //認証完了していない場合、index.phpを表示
            //ログイン無しアクセスを防ぐ
        }
    ?>

<?php if (isset($_SESSION['user_id'])) {?>
    <!-- 認証済みの場合、掲示板を表示 -->
    <div class="session">
        <div class="session_id">
            ログインid:<?php echo $_SESSION['user_id'];?>
        </div>
        <div class="session_logout">
            <a href="logout.php">ログアウト</a>
        </div>
    </div>
<?php
    $host     = $DB_acces['host'];
    $username = $DB_acces['username'];
    $passwd   = $DB_acces['passwd'];
    $dbname   = $DB_acces['dbname'];
    // 接続
    $link = new mysqli($host , $username, $passwd, $dbname);
    $posted_puts_sql = $link->prepare( "SELECT * FROM posted where status = 1");

    $posted_puts_sql->execute();
    $result = $posted_puts_sql->get_result();
    $posted_puts = $result->fetch_all(MYSQLI_ASSOC);

  //登録済み投稿数をカウント
    $post_num = count($posted_puts);
  //最大ページ数を算出
    $max_page = ceil($post_num / ARTICLE_MAX_NUM);
  if(!isset($_GET['page'])){
  //初期化（初めて訪れた時にはpageが設定されていない）
    $now_page = FIRST_VISIT_PAGE;
  }else if(preg_match("/^[1-9][0-9]*$/",$_GET['page'])){
  //有効な数値かチェックして、念のためエスケープして$_POSTを格納する
    $now_page = htmlspecialchars($_GET['page'],ENT_QUOTES,'UTF-8');
  }else{
    echo "pageが正しく設定されていなかったので".FIRST_VISIT_PAGE."に設定しました!<br>";
    $now_page = FIRST_VISIT_PAGE;
  }

  /** 記事表示 **/
  //表示対象ページ数から、表示する記事Noを算出
  $start = ($now_page - 1) * ARTICLE_MAX_NUM;
  //$start番目からARTICLE_MAX_NUM個の配列を取得
  $output_work = array_slice($posted_puts,$start,ARTICLE_MAX_NUM);
  $output_count =count($output_work);

   if(!isset($_GET['next_no']) or $_GET['next_no'] == "1" ){
    $page_no_result = page_no_calculation("0",$output_count,"0");
   }else{
    $page_no_result = page_no_calculation($_GET['next_no'],$output_count,$_GET['pagetype']);
   }
    $start_count = $page_no_result['start_count'] ;
    $end_count = $page_no_result['end_count'];

    if(count($output_work)>=1)
    {
?>
   <div class="comment">コメントを追記しましょう。良く確認してから投稿を押してね。</div>
    <form action="/bbs/registration.php" method="post"  enctype="multipart/form-data">
        <ul>
            <li><label>『投稿タイトル』※必須入力</label>
                <BR>
                <input type="text" name="title" size="70" maxlength="50" required>
            </li>
            <li><label>『投稿者名』※必須入力</label>
                <BR>
                <input type="text" name="poster_name" size="40" maxlength="10" required>
            </li>

            <li><label>『投稿内容』※必須入力</label>
                <BR>
                <textarea name="content_text" rows="4" cols="50" maxlength="140" required></textarea>
            </li>
            <li><label >『画像』※サイズ制限4MB</label>
                <BR>
                <input type="file" name="img_deta">
            </li>
                <BR>
            <li><input type="submit" value="投稿"><input type="reset" value="リセット"></li>
            <BR>
        </ul>
    </form>

    <div class="comment">合計投稿数:<?php echo $post_num; ?></div>
    <div class="comment">合計<?php echo $max_page; ?>ページ中<?php echo $now_page; ?>ページ目表示中</div>
    <div class="paging">
<?php if($now_page > 1){ ?>
        <div class="pager-back">
            <a href=thread_list.php?next_no=<?php echo ($start_count-1) ?>&page=<?php echo ($now_page-1) ?>&pagetype=back> <-前のページへ</a></div>
<?php  }  ?>
<?php if($now_page < $max_page){ ?>
        <div class="pager-next">
            <a href=thread_list.php?next_no=<?php echo ($end_count+1) ?>&page=<?php echo ($now_page+1) ?>&pagetype=next> 次のページへ-></a></div>
<?php  }  ?>
    </div><!-- <div class="paging"> -->
<?php  foreach($output_work as $output){ ?>
        <div class="posted-container">
            <label class="posted-item-1row">No.<?php echo $output['post_no']; ?></label>
            <label class="posted-item-1row">投稿日時:<?php echo $output['create_date']; ?></label>
            <label class="posted-item-1row">投稿者:<?php echo $output['poster_name']; ?></label>
            <label class="posted-item">投稿タイトル:<?php echo $output['title']; ?></label>
            <label class="posted-item">投稿内容:<BR><?php echo $output['content_text']; ?></label>
            <?php if ($output['image_st']==1) {?>
                <img src=<?php echo $output['image_pass']; ?> class="posted">
            <?php }else{    ?>
            <?php } ?>
            <?php if ($output['poster_id']==$_SESSION['user_id']) {?>
            <label class="posted-item">
              <a href=post_del.php?no=<?php echo $output['no']?>&post_no=<?php echo $output['post_no']?>>削除する</a>
            </label>
            <?php }else{    ?>
            <label class="posted-item"></label>
            <?php } ?>
        </div>       
 <?php
        }
        unset($output);
?>

    <div class="comment">合計投稿数:<?php echo $post_num; ?></div>
    <div class="comment">合計<?php echo $max_page; ?>ページ中<?php echo $now_page; ?>ページ目表示中</div>
    <div class="paging">
<?php if($now_page > 1){ ?>
        <div class="pager-back">
            <a href=thread_list.php?next_no=<?php echo ($start_count-1) ?>&page=<?php echo ($now_page-1) ?>&pagetype=back> <-前のページへ</a></div>
<?php  }  ?>
<?php if($now_page < $max_page){ ?>
        <div class="pager-next">
            <a href=thread_list.php?next_no=<?php echo ($end_count+1) ?>&page=<?php echo ($now_page+1) ?>&pagetype=next> 次のページへ-></a></div>
<?php  }  ?>
    </div><!-- <div class="paging"> -->

    <div class="session_footer">
        <div class="unsubscribe">
            <a href="unsubscribe.php">退会する場合はこちら</a>
        </div>
    </div>

<?php
    }else{
?>

    <div class="comment">投稿数:0です。新規登録しましょう。良く確認してから投稿を押してね。</div>
    <form action="/bbs/registration.php" method="post"  enctype="multipart/form-data">
        <ul>
            <li><label>『投稿タイトル』※必須入力</label>
                <BR>
                <input type="text" name="title" size="70" maxlength="50" required>
            </li>
            <li><label>『投稿者名』※必須入力</label>
                <BR>
                <input type="text" name="poster_name" size="40" maxlength="10" required>
            </li>

            <li><label>『投稿内容』※必須入力</label>
                <BR>
                <textarea name="content_text" rows="4" cols="50" maxlength="140" required></textarea>
            </li>
            <li><label >『画像』※サイズ制限4MB</label>
                <BR>
                <input type="file" name="img_deta">
            </li>
                <BR>
            <li><input type="submit" value="投稿"><input type="reset" value="リセット"></li>
            <BR>
        </ul>
    </form>
<?php
    }//  if(count($posted_output)>=1)
?>
<?php } //if (isset($_SESSION['user_id'])) ?>

<?php require(dirname(__FILE__) ."/temp/footer.html"); ?>