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
    header("Location: {$_SERVER['REQUEST_URI']}");
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

// 表示形式の変換
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//エラーメッセージの初期化
$errors = array();

// 変数定義
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];
$course_id = $_GET["course_id"];
if(isset($account)){
  $name = $account;
}elseif(!empty($_POST["name"])){
  $name = $_POST["name"];
}else{
  $name = "名無しさん";
}
;
$posttime = date("Y/m/d H:i:s");
$comment = $_POST["comment"];

// 講義情報取得
try{
	$sql = "SELECT * FROM courses WHERE course_id = :course_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':course_id', $course_id, PDO::PARAM_STR);
	$stmt->execute();
	 while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$course = $row["course"];
	}
}catch(Exception $e){
	$errors['error'] = "もう一度やりなおして下さい。";
}

// 投稿機能
if(!empty($_POST["exe"]) && !empty($_POST["comment"])){
  try{
    //例外処理を投げる（スロー）ようにする
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //トランザクション開始
    $pdo->beginTransaction();
    // 投稿番号
    $sql = "SELECT COUNT(*) FROM course_bbs WHERE course_id = :course_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt -> fetchColumn() +1;
    //course_bbsテーブルに本登録する
    $sql = "INSERT INTO course_bbs (post_id,post_name,posttime,comment,user_id,course_id) VALUES (:post_id,:post_name,:posttime,:comment,:user_id,:course_id)";
    $stmt = $pdo->prepare($sql);
    //プレースホルダへ実際の値を設定する
    $stmt->bindValue(':post_id', $count, PDO::PARAM_STR);
    $stmt->bindValue(':post_name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':posttime', $posttime, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_STR);
    $stmt->execute();
    // トランザクション完了（コミット）
    $pdo->commit();

  }catch(Exception $e){
    $errors['error'] = "もう一度やりなおして下さい。";
  }
}
  // レコードの消去
  if(!empty($_POST["delete"]) && !empty($_POST["deleteID"])){
    try{
      $sql ="DELETE FROM data WHERE post_id = :id";
      $stmt = $pdo->prepare($sql);
      $res = $stmt->execute(array($_POST["deleteID"]));
      if($res){
        $sql ="SELECT * FROM data ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(null);
        $res = "";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          $res .= $row[id].":".$row[name]." ".$row[comment]." ".$row[postTime]."<br>";
        }
      }
    }catch(Exception $e){
      $errors['error'] = "もう一度やりなおして下さい。";
    }
  }

  // レコードの編集選択
  if(!empty($_POST["edit"]) && !empty($_POST["editID"]) && !empty($_POST["password2"]) && (($_POST["password2"]) == $password0)){
    try{
      $sql ="SELECT * FROM data WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array($_POST["editID"]));
      $selectEditName = "";
      $selectEditComment = "";
      $selectEditID = "";
      while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $selectEditName .= $row[name];
        $selectEditComment .= $row[comment];
        $selectEditID .= $row[id];
      }
    }catch(Exception $e){
      $errors['error'] = "もう一度やりなおして下さい。";
    }
  }

  // レコードの編集
  if(!empty($_POST["exe"]) && !empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password0"]) && !empty($_POST["flagID"])){
    try{
      $sql = "UPDATE data SET name = ?, comment = ?, password = ? WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $array = array($_POST["name"], $_POST["comment"], $_POST["password0"], $_POST["flagID"]);
      $res = $stmt->execute($array);
      if($res){
        $sql ="SELECT * FROM data ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(null);
        $res = "";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          $res .= $row[id].":".$row[name]." ".$row[comment]." ".$row[postTime]."<br>";
        }
      }
    }catch(Exception $e){
      $errors['error'] = "もう一度やりなおして下さい。";
    }
  }
  // 投稿表示
  try{
    $sql = "SELECT * FROM course_bbs WHERE course_id=:course_id ORDER BY post_id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_STR);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

      if($row["user_id"] != null){
      $res .='<p class="bbs_post">'.$row["post_id"].'.'.'<a href="userpage.php?user_id='.$row["user_id"].'">'.$row["post_name"].'</a>'.' '.$row["posttime"].'<br><span class="comment">'.$row["comment"].'</span></p>';
      }else{
      $res .='<p class="bbs_post">'.$row["post_id"].'.'.$row["post_name"].' '.$row["posttime"].'<br><span class="comment">'.$row["comment"].'</span></p>';
      }
    }
  }catch(Exception $e){
    $errors['error'] = "もう一度やりなおして下さい。";
  }
?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 科目掲示板</title>
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
    <div class="page_top">
      <h1 class="bbs_name"><?= $course ?></h1>
    </div>
    <!-- フォーム機能 -->
    <div class="post_form">
      <form action="" method="post">
        <!-- 入力フォーム -->
        <?php if(empty($account)): ?>
          <input type="text" name="name" placeholder="名前" value="<?php if(isset($selectEditName)){echo htmlspecialchars($selectEditName);} ?>" class="bbs_name_post"><br>
        <?php endif ?>
        <textarea name="comment" rows="5" cols="30" class="textarea" placeholder="コメント"></textarea>
        <input type="hidden" name="flagID" value="<?php if(isset($selectEditID)){echo htmlspecialchars($selectEditID);}?>">
        <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
        <input type="submit" name="exe" value="送信" class="post_btn"><br><br>
      </form>
    </div>
    <!-- 消去フォーム   -->
    <!-- <input type="text" name="deleteID" placeholder="消去対象番号"><br>
    <input type="submit" name="delete" value="消去"><br><br> -->

    <!-- 編集フォーム   -->
    <!-- <input type="text" name="editID" placeholder="編集対象番号"><br>
    <input type="submit" name="edit" value="編集">
  </form> -->

  <!-- データの出力 -->
    <div class="bbs_comment">
      <?php echo $res; ?>
    </div>
  </div>
</div>
</body>
</html>
