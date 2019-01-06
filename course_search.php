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
$courses_per_page_10 = 10;
$courses_per_page_50 = 50;
$courses_per_page_100 = 100;
$page = 1;
$course = $_GET["course"];
$instructor = $_GET["instructor"];
$day = $_GET["day"];
$period = $_GET["period"];
$school = $_GET["school"];
$year = $_GET["year"];
// 科目一覧の表示(検索された場合)
try{
  //例外処理を投げるようにする
	 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	if (preg_match('/^[1-9][0-9]*$/', $_GET['page'])) {
		$page = (int)$_GET['page'];
	} else {
		$page = 1;
	}
	$offset = $courses_per_page_10*($page-1);
	 $sql ="SELECT * FROM courses WHERE course LIKE :course AND instructor LIKE :instructor AND day LIKE :day AND period LIKE :period AND  school LIKE :school AND year LIKE :year ORDER BY course_id ASC LIMIT ${offset},${courses_per_page_10}";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':course', "%".$course."%", PDO::PARAM_STR);
	$stmt->bindValue(':instructor',"%".$instructor."%", PDO::PARAM_STR);
	$stmt->bindValue(':day',"%".$day."%", PDO::PARAM_STR);
	$stmt->bindValue(':period',"%".$period."%", PDO::PARAM_STR);
	$stmt->bindValue(':school',"%".$school."%", PDO::PARAM_STR);
	$stmt->bindValue(':year',"%".$year."%", PDO::PARAM_STR);
	$stmt->execute();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if(isset($account)){
	  	$res .= '<tr height="60px"><td>'.$row["year"].'</td><td><a href =" course_bbs.php?course_id='.$row["course_id"].'">'.$row["course"].'</a></td><td>'.$row["instructor"].'</td><td class="center">'.$row["day"].$row["period"].'</td><td class="center">'.$row["school"].'</td><td>'.$row["room"].'</td><td><input type="checkbox" name="insert_id[]" value='.$row["course_id"].' class="insert_check"></td><tr>';
	  }else{
				$res .= '<tr height="60px"><td>'.$row["year"].'</td><td><a href =" course_bbs.php?course_id='.$row["course_id"].'">'.$row["course"].'</a></td><td>'.$row["instructor"].'</td><td class="center">'.$row["day"].$row["period"].'</td><td class="center">'.$row["school"].'</td><td>'.$row["room"].'</td><tr>';
		}
	}
	// ページング用
	 $sql ="SELECT count(*) FROM courses WHERE course LIKE :course AND instructor LIKE :instructor AND day LIKE :day AND period LIKE :period AND  school LIKE :school AND year LIKE :year";
	 $stmt = $pdo->prepare($sql);
	 $stmt->bindValue(':course', "%".$course."%", PDO::PARAM_STR);
	 $stmt->bindValue(':instructor',"%".$instructor."%", PDO::PARAM_STR);
	 $stmt->bindValue(':day',"%".$day."%", PDO::PARAM_STR);
	 $stmt->bindValue(':period',"%".$period."%", PDO::PARAM_STR);
	 $stmt->bindValue(':school',"%".$school."%", PDO::PARAM_STR);
	 $stmt->bindValue(':year',"%".$year."%", PDO::PARAM_STR);
	 $stmt->execute();
	 $total = $stmt->fetchColumn();
	 $totalPages = ceil($total / $courses_per_page_10);
	 //データベース接続切断
	 $pdo = null;
	}catch(Exception $e){
	  $errors['error'] = "もう一度やりなおして下さい。";
	  // echo 'Error:'.$e->getMessage();
	}
	// 科目の時間割への追加
	if(!empty($_POST["insert"]) && !empty($_POST["insert_id"])){
		//データベース接続
		require_once("db.php");
		$pdo = db_connect();
		$insert_id = $_POST["insert_id"];
		try{
			//例外処理を投げる（スロー）ようにする
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//トランザクション開始
			$pdo->beginTransaction();

			// 時間割に既に保存されているかを確認。
			$sql ="SELECT * FROM timetable WHERE user_id=:user_id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$day_period[$row["day"]][$row["period"]] = $row["day"].$row["period"];
			}

		 // 科目データを取得
			$sql ="SELECT * FROM courses WHERE course_id = :insert_id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(":insert_id", $insert_id,  PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$course_id0 = $row["course_id"];
				$course0 = $row["course"];
				$instructor0 = $row["instructor"];
				$day0 = $row["day"];
				$period0 = $row["period"];
			}


			//timetableテーブルに本登録する
			if(empty($day_period[$day0][$period0]) || $day_period[$day0][$period0] == $day_period["無"]["１"]){
        if($day0 == "無"){
					if(!empty($day_period[$day0][$period0])){
						$period0 = "２";
					}
					if(!empty($day_period[$day0][$period0])){
						$period0 = "３";
					}
					if(!empty($day_period[$day0][$period0])){
						$period0 = "４";
					}
					if(!empty($day_period[$day0][$period0])){
						$period0 = "５";
					}
					if(!empty($day_period[$day0][$period0])){
						$period0 = "６";
					}
					if(!empty($day_period[$day0][$period0])){
						$period0 = "７";
					}
					if(!empty($day_period[$day0][$period0])){
						$res_insert1 = "これ以上オンデマンド授業は追加できません";
					}
				}

				$sql = "INSERT INTO timetable (course,course_id,instructor,day,period,user_id) VALUES (:course,:course_id,:instructor,:day,:period,:user_id)";
				$stmt = $pdo->prepare($sql);
				//プレースホルダへ実際の値を設定する
				$stmt->bindValue(':course', $course0, PDO::PARAM_STR);
				$stmt->bindValue(':course_id', $course_id0, PDO::PARAM_STR);
				$stmt->bindValue(':instructor', $instructor0, PDO::PARAM_STR);
				$stmt->bindValue(':day', $day0, PDO::PARAM_STR);
				$stmt->bindValue(':period', $period0, PDO::PARAM_STR);
				$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
				$stmt->execute();
				// トランザクション完了（コミット）
				$pdo->commit();
				$res_insert = $course0."を時間割に追加しました。" ;
				//データベース接続切断
				$pdo = null;
			}else{
				$res_insert = "その曜日・時限には既に科目が登録されています。";
			}
		}catch(Exception $e){
			$errors['error'] = "もう一度やりなおして下さい。";
			// echo 'Error:'.$e->getMessage();
		}
	}

$from = $offset + 1;
$to = ($offset + $courses_per_page_10) < $total ? ($offset + $courses_per_page_10) : $total;
?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 科目検索</title>
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

	<?php require_once("header.php"); ?>
	<h1 class="course_search_top">科目検索</h1>

	<form action="course_search.php" method="GET">
		<table class="items">
			<tr><td class="item1">科目名：</td><td><input type="text" name="course" value="<?php if(isset($course)){echo h($course);} ?>" class="item_form"></td></tr>
			<tr><td class="item">教員名：</td><td class="item_value"><input type="text" name="instructor" value="<?php if(isset($instructor)){echo h($instructor);} ?>" class="item_form"></td></tr>
			<tr><td class="item">&nbsp;&nbsp;&nbsp;曜日：</td><td class="item_value"><select name="day" class="item_form">
			<option value=""></option>
			<option value="月" <?= $day == "月" ? "selected" : "" ?>>月</option>
			<option value="火" <?= $day == "火" ? "selected" : "" ?>>火</option>
			<option value="水" <?= $day == "水" ? "selected" : "" ?>>水</option>
			<option value="木" <?= $day == "木" ? "selected" : "" ?>>木</option>
			<option value="金" <?= $day == "金" ? "selected" : "" ?>>金</option>
			<option value="土" <?= $day == "土" ? "selected" : "" ?>>土</option>
			<option value="無" <?= $day == "無" ? "selected" : "" ?>>無</option>
		  </select></td></tr>
			<tr><td class="item">&nbsp;&nbsp;&nbsp;時限：</td><td class="item_value"><select name="period" class="item_form">
			<option value=""></option>
			<option value="１" <?= $period == "１" ? "selected" : "" ?>>１</option>
			<option value="２" <?= $period == "２" ? "selected" : "" ?>>２</option>
			<option value="３" <?= $period == "３" ? "selected" : "" ?>>３</option>
			<option value="４" <?= $period == "４" ? "selected" : "" ?>>４</option>
			<option value="５" <?= $period == "５" ? "selected" : "" ?>>５</option>
			<option value="６" <?= $period == "６" ? "selected" : "" ?>>６</option>
			<option value="７" <?= $period == "７" ? "selected" : "" ?>>７</option>
			</select></td></tr>
		  <tr><td class="item">&nbsp;&nbsp;&nbsp;学部：</td><td class="item_value"><select name="school" class="item_form">
			<option value=""></option>
			<option value="社学" <?= $school == "社学" ? "selected" : "" ?>>社学</option>
			<option value="政経" <?= $school == "政経" ? "selected" : "" ?>>政経</option>
			<option value="法学" <?= $school == "法学" ? "selected" : "" ?>>法学</option>
			<option value="教育" <?= $school == "教育" ? "selected" : "" ?>>教育</option>
			<option value="商学" <?= $school == "商学" ? "selected" : "" ?>>商学</option>
			<option value="社学" <?= $school == "社学" ? "selected" : "" ?>>社学</option>
			<option value="人科" <?= $school == "人科" ? "selected" : "" ?>>人科</option>
			<option value="スポーツ" <?= $school == "スポーツ" ? "selected" : "" ?>>スポーツ</option>
			<option value="国際教養" <?= $school == "国際教養" ? "selected" : "" ?>>国際教養</option>
			<option value="文構" <?= $school == "文構" ? "selected" : "" ?>>文構</option>
			<option value="文" <?= $school == "文" ? "selected" : "" ?>>文</option>
			<option value="基幹" <?= $school == "基幹" ? "selected" : "" ?>>基幹</option>
			<option value="創造" <?= $school == "創造" ? "selected" : "" ?>>創造</option>
			<option value="先進" <?= $school == "先進" ? "selected" : "" ?>>先進</option>
		  </select></td></tr>
			<tr><td class="item">&nbsp;&nbsp;&nbsp;年度：</td><td class="item_value"><select name="year" class="item_form">
			<option value=""></option>
			<option value="2018" <?= $year == "2018" ? "selected" : "" ?>>2018</option>
		  </select></td></tr>
	  </table>
		<input type="submit" name="exe" value="検索" class="course_search"><br>
	</form>


		<!-- データの出力 -->
	<div class="courses_main">
	<form action="course_check.php" method="post">
	<?php if (count($errors) === 0 && $total != 0 ): ?>
		<h1 class="course_table_top">科目一覧</h1>
		<p class="total_page">全<?php echo $total; ?>件中、<?php echo $from; ?>件〜<?php echo $to; ?>件を表示しています。
		</p>
			<!-- ログインしていたら科目追加ボタンを表示 -->

			<!-- 科目一覧を表示 -->
			<table border="2" class="course_table">
				<tr>
				<th width="4%">開講年度</th>
				<th width="30%">科目名</th>
				<th width="33%">教員名</th>
				<th width="8%">曜日時限</th>
			  <th width="4%">学部</th>
			  <th width="21%">教室</th>
				<?php if(!empty($account)): ?>
			  	<th width="4%"></th>
			  <?php endif ?>
			  </tr>
				<?php echo $res; ?>
			</table>

			<?php if(isset($account)): ?>
				<input type="submit" name="insert" value="時間割に追加 " class="course_insert_btn">
			<?php endif; ?>

			<!-- ページング -->
		 <div class="page">
			<?php if ($page > 1) : ?>
			  &nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day ?>&period=<?=$period ?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$page-1 ?>">前へ</a>
			<?php else: ?>
				<?="　　"?>
			<?php endif; ?>

			<?php if ($page > 6) : ?>
			  &nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?="1" ?>"><?php echo "1"; ?></a>
				<span>...</span>
			<?php endif; ?>

			<?php for ($i = 1; $i <= $totalPages; $i++) : ?>
				<?php if ($page == $i) : ?>
					<strong>&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>
			    </strong>

				<?php elseif ($page+10 > $i && $page == 1) : ?>
				 	&nbsp; <a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page+9 > $i && $page == 2) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>


				<?php elseif ($page+8 > $i && $page == 3) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page+7 > $i && $page == 4) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page+6 > $i && $page == 5) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page-7 < $i && $page == $totalPages-3) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page-8 < $i && $page == $totalPages-2) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page-9 < $i && $page == $totalPages-1) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page-10 < $i && $page == $totalPages) : ?>
					&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>

				<?php elseif ($page+6 > $i && $i > $page-6 ) : ?>
				 	&nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year  ?>&insert=<?=$insert?>&exe=検索&page=<?=$i ?>"><?php echo $i; ?></a>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($page < $totalPages-6) : ?>
				<span>...</span>
			   &nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$totalPages ?>"><?php echo $totalPages; ?></a>
			<?php endif; ?>

			<?php if ($page < $totalPages) : ?>
				 &nbsp;<a href="?course=<?=$course ?>&instructor=<?=$instructor ?>&day=<?=$day?>&period=<?=$period?>&school=<?=$school ?>&year=<?=$year ?>&insert=<?=$insert?>&exe=検索&page=<?=$page+1 ?>">次へ</a>
			<?php else: ?>
				<?="　　"?>
		  <?php endif; ?>
		</div>
	  </form>
	</div>

	<!-- もしエラーがあった場合は -->
	<?php elseif(count($errors) > 0): ?>
		 <!-- エラー変数に格納された配列を取り出して出力 -->
    <?php foreach($errors as $value): ?>
      <?=$value ?><br>
    <?php endforeach ?>
	<?php else: ?>
		<h4>一致する科目がありません</h4>
	<?php endif; ?>
</div>
</body>
</html>
