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

// ログイン状態のチェック
if (!isset($_SESSION["account"])) {
	header("Location: index.php");
	exit();
}

//エラーメッセージの初期化
$errors = array();

// 変数定義
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];

	// 科目の時間割への追加
if(!empty($_POST["insert"]) && !empty($_POST["insert_id"])){

	//データベース接続
	require_once("db.php");
	$pdo = db_connect();

	try{
		$sql ="SELECT DISTINCT tt_name FROM timetable WHERE user_id=:user_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$tt_select .="<option value=".$row['tt_name'].">".$row['tt_name']."</option>\r\n";
			$tt_dselect[] = $row["tt_name"];
		}
		if (empty($tt_select)) {
			$tt_select = "<option value='時間割'>時間割</option>";
		}
	}catch(Exception $e){
		$errors['error'] = "もう一度やりなおして下さい。";
		echo 'Error:'.$e->getMessage();
	}

	$tt_name = $tt_dselect[0];

	foreach($_POST['insert_id'] as $insert_id ){
		try{
			//例外処理を投げる（スロー）ようにする
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//トランザクション開始
			$pdo->beginTransaction();

		 // 科目データを取得
			$sql ="SELECT * FROM courses WHERE course_id = :insert_id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(":insert_id", $insert_id,  PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$course_id = $row["course_id"];
				$course = $row["course"];
				$instructor = $row["instructor"];
				$school = $row["school"];
				$year = $row["year"];
				$day = $row["day"];
				$period = $row["period"];
				$room = $row["room"];

        $res_insert .= '<tr height="60px"><td>'.$year.'</td><td><a href =" course_bbs.php?course_id='.$course_id.'">'.$course.'</a></td><td>'.$instructor.'</td><td>'.$day.$period.'</td><td>'.$room.'</td><td>'.$school.'</td><td><select name="tt'.$course_id.'" class="tt_select_form">'.$tt_select.'</select></td><td><select name="color'.$course_id.'"class="tt_select_form">
				<option value="#a9a9a9">灰</option>
				<option value="#7fbfff">青</option>
				<option value="#ff7f7f">赤</option>
				<option value="#ffff84">黄</option>
				<option value="#7fff7f">緑</option>
				</select></td></tr></td><tr>';

				$insert_check .= "<input type='hidden' name='insert_id[]' value=".$course_id.">\r\n";
			}
			// トランザクション完了（コミット）
			$pdo->commit();

		}catch(Exception $e){
			$errors['error'] = "もう一度やりなおして下さい。";
			echo 'Error:'.$e->getMessage();
    }
	}
	//データベース接続切断
	$pdo = null;
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 科目追加</title>
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
		<h1 class="course_check_top">追加科目確認</h1>

		<!-- データの出力 -->
		<?php if (count($errors) === 0): ?>
			<?php if ($res_insert != null or $res_no_insert != null): ?>
				<form action="course_insert.php" method="post" class="form_display">
					<table border="2" class="course_check_tt">
						<tr>
							<th width="6%">開講年度</font></th>
							<th width="29%">科目名</font></th>
							<th width="29%">教員名</font></th>
							<th width="4%">曜日時限</font></th>
							<th width="20%">教室</font></th>
							<th width="4%">学部</font></th>
							<th width="14%">時間割</font></th>
							<th width="2%">色</font></th>
						</tr>
						<?=$res_insert ?></h3>
						<?php echo $res; ?>
					</table>
					<h3><?=$res_no_insert ?></h3>
					<h3><?=$res_insert1 ?></h3>
					<!-- <input type="button" class="back" value="戻る" onClick="history.back()"> -->
					<?=$insert_check ?>
					<input type="submit" name="insert" value="科目を追加" class="mypage">
				</form>
			<?php else: ?>
				<h4 class="course_check0">科目が選択されてません</h4>
				<!-- <input type="button" class="back" value="戻る" onClick="history.back()"> -->
			<?php endif; ?>

			<!-- もしエラーがあった場合は -->
		<?php elseif(count($errors) > 0): ?>

			<!-- エラー変数に格納された配列を取り出して出力 -->
			<?php foreach($errors as $value): ?>
				<?=$value ?><br>
			<?php endforeach ?>
			<!-- <input type="button" class="back" value="戻る" onClick="history.back()"> -->
		<?php else: ?>
			<h4 class="course_check0">科目が選択されてません</h4>
			<!-- <input type="button" class="back" value="戻る" onClick="history.back()"> -->
		<?php endif; ?>

  </div>
</div>
</body>
</html>
