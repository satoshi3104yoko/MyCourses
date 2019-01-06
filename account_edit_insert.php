<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']){
	echo "不正アクセスの可能性あり";
	exit();
}


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

$user_id = $_SESSION['user_id'];
$account = $_SESSION['account'];
//パスワードのハッシュ化
$password = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

//ここでデータベースに登録する
try{
	//例外処理を投げる（スロー）ようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	//トランザクション開始
	$pdo->beginTransaction();

	//memberテーブルに登録する
  $sql = "UPDATE member SET account=(:account),password=(:password) WHERE user_id=(:user_id)";
	$stmt = $pdo->prepare($sql);

	//プレースホルダへ実際の値を設定する
	$stmt->bindValue(':account', $account, PDO::PARAM_STR);
	$stmt->bindValue(':password', $password, PDO::PARAM_STR);
  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$stmt->execute();

	// トランザクション完了（コミット）
	$pdo->commit();

	//データベース接続切断
	$pdo = null;

}catch (PDOException $e){
	//トランザクション取り消し（ロールバック）
	$pdo->rollBack();
	$errors['error'] = "もう一度やりなおして下さい。";
	// echo 'Error:'.$e->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses ユーザー情報編集</title>
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

		<?php if (count($errors) === 0): ?>
			<h1 class="account_edit_comp">ユーザー情報の編集が完了しました</h1>
			<form action="mypage.php" method="post">
				<input type="submit" value="マイページ" class="mypage">
			</form>

			<?php if(count($errors) > 0): ?>
        <?php foreach($errors as $value): ?>
          <?=$value ?><br>
        <?php endforeach ?>
		   <input type="button" value="戻る" onClick="history.back()" class="back1">

		  <?php endif; ?>
		<?php endif; ?>
  </div>
</div>
</body>
</html>
