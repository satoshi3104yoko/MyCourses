<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//データベース接続
require_once("db.php");
$pdo = db_connect();

// ログイン状態のチェック
if (!isset($_SESSION["account"])) {
	header("Location: index.php");
	exit();
}

//エラーメッセージの初期化
$errors = array();

// 表示形式の変換
function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$account = $_SESSION["account"];
$password = $_SESSION["password"];

if(empty($account) || empty($password)){
	header("Location: account_edit_form.php");
	exit();
}else{
  $password_hide = str_repeat('*', strlen($password));
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCoursess ユーザー情報編集</title>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<div class="cp_cont">
	<div class="cp_offcm01">
		<input type="checkbox" id="cp_toggle01">
		<label for="cp_toggle01"><span></span></label>
		<div class="cp_menu">
			<ul>
			<li><a href="mypage.php">マイページ</a></li>
			<li><a href="course_search.php">科目掲示板</a></li>
			<li><a href="user_search.php">ユーザー検索</a></li>
			<li><a href="logout.php">ログアウト</a></li>
		  </ul>
		</div>
	</div>
	<div class="cp_contents">
	  <?php require_once("header.php"); ?>
			<h1 class="account_edit_top">ユーザー情報確認</h1>
	  </div>
		<?php if (count($errors) === 0): ?>
		  <form action="account_edit_insert.php" method="post">
				<div class="registration_form">
				  <input type="text" name="account" class="registration" value="<?=h($account)?>" size="50" readonly="readonly"><br>
					<i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
				</div>
				<div class="registration_form">
				  <input type="text" name="password" class="registration" value="<?=$password_hide?>" size="50" readonly="readonly"><br>
					<i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
				</div>
				<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
			  <!-- <input type="button" value="戻る" onClick="history.back()" class="back1"> -->
			  <input type="submit" name="registration" value="次へ" class="next">
		  </form>
		  <?php if(count($errors) > 0): ?>
        <?php foreach($errors as $value): ?>
          <?=$value ?><br>
        <?php endforeach ?>
		   <input type="button" value="戻る" onClick="history.back()" class="back1">

		  <?php endif; ?>
		<?php endif; ?>
  </div>
</body>
</html>
