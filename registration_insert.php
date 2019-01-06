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

$account = $_SESSION['account'];

//パスワードのハッシュ化
$password_hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

//ここでデータベースに登録する
try{
	//例外処理を投げる（スロー）ようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	//トランザクション開始
	$pdo->beginTransaction();

	//memberテーブルに本登録する
	$sql = "INSERT INTO member (account,password) VALUES (:account,:password_hash)";
	$stmt = $pdo->prepare($sql);
	//プレースホルダへ実際の値を設定する
	$stmt->bindValue(':account', $account, PDO::PARAM_STR);
	$stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
	$stmt->execute();

	//アカウントで検索
	$sql = "SELECT * FROM member WHERE account=(:account)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':account', $account, PDO::PARAM_STR);
	$stmt->execute();
	if($row = $stmt->fetch()){
		$user_id = $row["user_id"];
	}

//時間割を作成する
	$sql = "INSERT INTO timetable (course,course_id,instructor,day,period,user_id,tt_name) VALUES (:course,:course_id,:instructor,:day,:period,:user_id,:tt_name)";
	$stmt = $pdo->prepare($sql);
	//プレースホルダへ実際の値を設定する
	$stmt->bindValue(':course', 0, PDO::PARAM_STR);
	$stmt->bindValue(':course_id', 0, PDO::PARAM_STR);
	$stmt->bindValue(':instructor', 0, PDO::PARAM_STR);
	$stmt->bindValue(':day', 0, PDO::PARAM_STR);
	$stmt->bindValue(':period', 0, PDO::PARAM_STR);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$stmt->bindValue(':tt_name', "時間割", PDO::PARAM_STR);
	$stmt->execute();

	// トランザクション完了（コミット）
	$pdo->commit();

	//データベース接続切断
	$pdo = null;
	//セッション変数を全て解除
	$_SESSION = array();

	//セッションクッキーの削除・sessionidとの関係を探れ。つまりはじめのsesssionidを名前でやる
	if (isset($_COOKIE["PHPSESSID"])) {
    		setcookie("PHPSESSID", '', time() - 1800, '/');
	}

 	//セッションを破棄する
 	session_destroy();


}catch (PDOException $e){
	//トランザクション取り消し（ロールバック）
	$pdo->rollBack();
	echo 'Error:'.$e->getMessage();
}

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
	<div class="cp_contents">
		<?php require_once("header.php"); ?>

		<?php if (count($errors) === 0): ?>
			<h1 class="registration_insert">会員登録が完了しました</h1>
			<form action="index.php" method="post">
				<input type="submit" value="ログイン" class="mypage">
			</form>
		<?php elseif(count($errors) > 0): ?>
			<div class="errors">
				<?php foreach($errors as $value): ?>
					<?=$value ?><br>
				<?php endforeach ?>
			</div>
		<?php endif; ?>
  </div>
</div>
</body>
</html>
