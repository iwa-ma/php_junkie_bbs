<?php require(dirname(__FILE__) ."/temp/header.html"); ?>

    <div class="title">連絡掲示板(id登録）</div>

    <form action="/bbs/add_id.php" method="post">
        <ul>
            <li>登録するId(e-mail)、パスワードを入力して下さい。</li>
            <li><label class="new">Id(e-mail)：</label><input type="email" name="id" size="40"  required></li>
            <li><label class="new">パスワード：</label><input type="text" name="password1" id="password1" size="40"  required></li>
            <BR>
            <li><input type="submit" value="送信"><input type="reset" value="リセット"></li>
            <BR>
            <li><a href="index.php">ログイン画面に戻る</a></li>
        </ul>
    </form>

<?php require(dirname(__FILE__) ."/temp/footer.html"); ?>
