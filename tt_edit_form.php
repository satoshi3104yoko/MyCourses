<?php
session_start();

header("Content-type: text/html; charset=utf-8");

// CSRF対策
function setToken() {
  $token = sha1(uniqid(mt_rand(), true));
  $_SESSION['token'] = $token;
}

function checkToken() {
  if (empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
    header("Location: tt_edit.php");
		exit();
    }
}

// POST以外でアクセスされたとき
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
}

//POSTでアクセスされたとき
else{
    checkToken();
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;
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

// 表示形式の変換
function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//エラーメッセージの初期化
$errors = array();

// 変数定義
$account = $_SESSION["account"];
$user_id = $_SESSION["user_id"];
$tt_name = $_POST["tt_name"];
$tt_newname = $_POST["tt_newname"];

if(!empty($_POST["tt_name_edit"]) && !empty($tt_newname)){
	try{
		//例外処理を投げる（スロー）ようにする
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//トランザクション開始
		$pdo->beginTransaction();

		//memberテーブルに登録する
	  $sql = "UPDATE timetable SET tt_name=(:tt_newname) WHERE tt_name=(:tt_name)";
		$stmt = $pdo->prepare($sql);

		//プレースホルダへ実際の値を設定する
		$stmt->bindValue(':tt_newname', $tt_newname, PDO::PARAM_STR);
		$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
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

}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 時間割編集</title>
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
		<!-- データの出力 -->
		<?php if (count($errors) === 0 ): ?>
	    <h1 class="account_edit_top">時間割名の変更</h1>
      <form action="tt_edit_form.php" method="post">
				<div class="tt_edit_form">
					<input type="text" name="tt_newname" value="<?=$tt_name?>">
				</div>
				<input type="hidden" name="tt_name" value="<?=$tt_name?>">
        <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
  			<input type="submit" name="tt_name_edit" value="変更" class="next">
	    </form>

		<?php if(!empty($tt_newname)): ?>
			<h3 class="tt_edit_comp"><?=$tt_name?>を<?=$tt_newname?>に変更しました</h3>
      <form action="tt_edit.php" method="post">
          <input type="submit" name="insert" value="時間割編集" class="mypage">
      </form>
		<?php endif; ?>
			<!-- もしエラーがあった場合は -->
	  <?php elseif(count($errors) > 0): ?>

			<!-- エラー変数に格納された配列を取り出して出力 -->
			<?php foreach($errors as $value): ?>
				<?=$value ?><br>
			<?php endforeach ?>

			<input type="button" value="戻る" onClick="history.back()">

		<?php endif; ?>
  </div>
</div>
</body>
</html>
