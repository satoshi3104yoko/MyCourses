<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//データベース接続
require_once("db.php");
$pdo = db_connect();

//エラーメッセージの初期化
$errors = array();

// 表示形式の変換
function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$account = $_SESSION["account"];
$password = $_SESSION["password"];

if(empty($account) || empty($password)){
	header("Location: registration_form.php");
	exit();
}else{
	$password_hide = str_repeat('*', strlen($password));
}




//エラーが無ければセッションに登録
if(count($errors) === 0){

  try{
		$sql = "SELECT account FROM member";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	    $account_all[] = $row["account"];
		}
	}catch(Exception $e){
		$errors['error'] = "もう一度やりなおして下さい。";
		echo 'Error:'.$e->getMessage();
	}

  foreach($account_all as $accounts){
		if($account != $accounts){
			$_SESSION['account'] = $account;
			$_SESSION['password'] = $password;
  	}else{
			$errors["acoount"] = "そのユーザー名は既に使われています";
		}
	}
}
//データベース接続切断
$pdo = null;

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses アカウント作成</title>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<div class="cp_cont">
	<?php if(isset($account)): ?>
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
	<?php else :?>
		<div class="cp_offcm01">
			<input type="checkbox" id="cp_toggle01">
			<label for="cp_toggle01"><span></span></label>
			<div class="cp_menu">
				<ul>
				<li><a href="index.php">トップ</a></li>
				<li><a href="registration_form.php">アカウント作成</a></li>
				<li><a href="course_search.php">科目掲示板</a></li>
				<li><a href="user_search.php">ユーザー検索</a></li>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	
	<div class="cp_contents">
	  <?php require_once("header.php"); ?>
			<h1 class="registration_comfirm_top">ユーザー情報確認</h1>
	  </div>
		<?php if (count($errors) === 0): ?>
		  <form action="registration_insert.php" method="post">
				<div class="registration_form">
				  <input type="text" name="account" class="registration" value="<?=h($account)?>" size="50" readonly="readonly"><br>
					<i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
				</div>
				<div class="registration_form">
				  <input type="text" name="password" class="registration" value="<?=$password_hide?>" size="50" readonly="readonly"><br>
					<i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
				</div>
				<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
			  <!-- <input type="button" value="戻る" onClick="history.back()" class="back"> -->
			  <input type="submit" name="registration" value="次へ" class="next">
		  </form>
		<?php else: ?>
        <?php foreach($errors as $value): ?>
          <?=$value ?><br>
        <?php endforeach ?>
		   <input type="button" value="戻る" onClick="history.back()" class="next">
		<?php endif; ?>
  </div>
</body>
</html>
