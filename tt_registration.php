<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//CSRF対策
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
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];
$tt_name_insert = $_POST["tt_name"];

// 科目の時間割への追加
if(!empty($_POST["tt_name"]) && !empty($_POST["insert"])){

		try{
			//例外処理を投げる（スロー）ようにする
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//トランザクション開始
			$pdo->beginTransaction();

			// 同じ時間割名があるかを確認。
			$sql ="SELECT * FROM timetable WHERE user_id=:user_id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $tt_name[$row["tt_name"]] = $row["tt_name"];
			}

			//timetableテーブルに本登録する
			if(empty($tt_name[$tt_name_insert])){

				$sql = "INSERT INTO timetable (course,course_id,instructor,day,period,user_id,tt_name) VALUES (:course,:course_id,:instructor,:day,:period,:user_id,:tt_name)";
				$stmt = $pdo->prepare($sql);
				//プレースホルダへ実際の値を設定する
				$stmt->bindValue(':course', 0, PDO::PARAM_STR);
				$stmt->bindValue(':course_id', 0, PDO::PARAM_STR);
				$stmt->bindValue(':instructor', 0, PDO::PARAM_STR);
				$stmt->bindValue(':day', 0, PDO::PARAM_STR);
				$stmt->bindValue(':period', 0, PDO::PARAM_STR);
				$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
				$stmt->bindValue(':tt_name', $tt_name_insert, PDO::PARAM_STR);
				$stmt->execute();
				// トランザクション完了（コミット）
				$pdo->commit();
				$res_insert = $tt_name_insert."を作成しました。<br>" ;
			}else {
				$res_insert = $tt_name_insert."は既に存在しています<br>";
			}
		}catch(Exception $e){
			$errors['error'] = "もう一度やりなおして下さい。";
			echo 'Error:'.$e->getMessage();
		}
	}
	//データベース接続切断
	$pdo = null;
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
		  <h1 class="account_edit_top">時間割の追加</h1>
      <form action="tt_registration.php" method="post">
				<div class="tt_edit_form">
					<input type="text" name="tt_name" placeholder="時間割名">
				</div>
        <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
  			<input type="submit" name="insert" value="追加" class="next">
	    </form>
			<h1 class="newtt_comp"><?= $res_insert ?></h1>
      <?php if(!empty($res_insert)): ?>
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
		<!-- <input type="button" value="戻る" onClick="history.back()"> -->
	<?php endif; ?>

</body>
</html>
