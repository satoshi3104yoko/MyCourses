<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// 表示形式の変換
function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//データベース接続
require_once("db.php");
$pdo = db_connect();

//エラーメッセージの初期化
$errors = array();

// 変数定義
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];
$user = $_GET["user"];
$course = $_GET["course"];
$instructor = $_GET["instructor"];
$day = $_GET["day"];
$period = $_GET["period"];
// 科目一覧の表示(検索された場合)
try{
  //例外処理を投げるようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql ="SELECT * FROM member JOIN timetable ON member.user_id = timetable.user_id WHERE member.account LIKE :user AND timetable.course LIKE :course AND timetable.instructor LIKE :instructor AND timetable.day LIKE :day AND timetable.period LIKE :period ORDER BY member.user_id ASC ";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':user', "%".$user."%", PDO::PARAM_STR);
	$stmt->bindValue(':course', "%".$course."%", PDO::PARAM_STR);
	$stmt->bindValue(':instructor',"%".$instructor."%", PDO::PARAM_STR);
	$stmt->bindValue(':day',"%".$day."%", PDO::PARAM_STR);
	$stmt->bindValue(':period',"%".$period."%", PDO::PARAM_STR);
	$stmt->execute();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if(strpos($check,"user_id=".$row["user_id"]) === false){
	  	$res .= "・".'<a href="userpage.php?user_id='.$row["user_id"].'">'.$row["account"].'<br>';
			$check .="user_id=".$row["user_id"];
	  }
	}
 }catch(Exception $e){
	 $errors['error'] = "もう一度やりなおして下さい。";
	  // echo 'Error:'.$e->getMessage();
 }

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses ユーザー検索</title>
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
			<?php if(isset($account)): ?>
	      <ul>
	        <li><a href="mypage.php">マイページ</a></li>
	        <li><a href="course_search.php">科目掲示板</a></li>
	        <li><a href="user_search.php">ユーザー検索</a></li>
	        <li><a href="logout.php">ログアウト</a></li>
	      </ul>
	    <?php else: ?>
				<ul>
	  			<li><a href="index.php">トップ</a></li>
	  			<li><a href="registration_form.php">アカウント作成</a></li>
	  			<li><a href="course_search.php">科目掲示板</a></li>
	  			<li><a href="user_search.php">ユーザー検索</a></li>
			  </ul>
	    <?php endif; ?>
		</div>
  </div>

	<div class="cp_contents">
		<?php require_once("header.php"); ?>
		<h1 class="user_search_top">ユーザー検索</h1>

		<form action="user_search.php" method="GET">
			<table class="items1">
			<tr><td class="item10">ユーザーネーム:</td><td><input type="text" name="user" value="<?php if(isset($user)){echo h($user);} ?>" class="user_form"></td></tr>
			<tr><td class="item0">科目名：</td><td class="item_value0"><input type="text" name="course"  value="<?php if(isset($course)){echo h($course);} ?>" class="user_form"></td></tr>
			<tr><td class="item0">教員名：</td><td class="item_value0"><input type="text" name="instructor" value="<?php if(isset($instructor)){echo h($instructor);} ?>" class="user_form"></td></tr>
		</table>
			<input type="submit" name="exe" value="検索" class="user_search_btn"><br>
		</form>

		<!-- データの出力 -->
		<?php if (count($errors) === 0): ?>
			<h3 class="user_search">ユーザー一覧</h3>
			<p class="users"><?php echo $res; ?></p>
      <?php if (empty($res)): ?>
			 <h4 class="no_users">一致するユーザーがません</h4>
      <?php endif; ?>
			<!-- もしエラーがあった場合は -->
		<?php elseif(count($errors) > 0): ?>
			<?php foreach($errors as $value): ?>
	      <?=$value ?><br>
	    <?php endforeach ?>
		<?php endif; ?>

  </div>
</div>
</body>
</html>
