<?php
  require_once('functions.php');
	session_start();
?>
<!doctype HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
			body {
				font-size: 0.8em;
				width: auto;
				white-space: nowrap;
			}
			table {border-collapse: collapse;}
		</style>
    <script src="jquery.js"></script>
    <script src="md5.js"></script>
    <?php
      if(array_key_exists('name',$_POST)) {
        if($_POST['name']===ADMIN_NAME && $_POST['password']===ADMIN_PASSWORD) {
          $_SESSION=array();
          $_SESSION['login']=true;
        }
      }
      if(!array_key_exists('login',$_SESSION)) {
				$_SESSION['login']=false;
			}
			if(!$_SESSION['login']) {
				echo <<<EOT
<script type="text/javascript">
	$(document).ready(function() {
		$("form").submit(function() {
			$("input[name='name']").val(hex_md5($("input[name='display-name']").val()));
			$("input[name='password']").val(hex_md5($("input[name='display-password']").val()));
		});
	});
</script>
<title>学生成绩统计——首页</title>
</head><body><form action="index.php" method="post"><table><tbody><tr>
<td align=right>用户名：<td><input type="text" name="display-name"><tr>
<td align=right>密码：<td><input type="password" name="display-password"></tbody></table>
<input type="hidden" name="name"><input type="hidden" name="password">
<input type="submit" value="登录"><input type="reset" value="重置">
</form></body></html>
EOT;
				exit();
			}
    ?>
    <title>学生成绩统计——导入</title>
  </head>
  <body>
		<div>
			<a href="index.php">首页</a>|导入|<a href="statistics.php">统计</a>|<a href="logout.php">退出</a>
			<hr>
		<div>
    <form enctype="multipart/form-data" action="import.php" method="post">
			<label>导入逗号分割文件：</label><br>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_upload_max_filesize();?>">
			<input type="file" name="score" size="60">
			<input type="submit" value="导入文件">
    </form>
<?php
if(array_key_exists('score', $_FILES)) {
	echo '<hr>';
	if(is_uploaded_file($_FILES['score']['tmp_name'])) {
		echo '导入文件成功。<br>';
		$filename=UPLOAD_DIR.$_FILES['score']['name'];
		move_uploaded_file($_FILES['score']['tmp_name'], $filename);
		if(!$fhandle=fopen($filename, "rb")) {
			die("无法打开导入的文件。<br>");
		}
		$entries=explode(chr(13), str_replace('""', '', str_replace(chr(10), '', fread($fhandle, filesize($filename)))));
		if(!$dblnk=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true)) {
			die('无法连接数据库服务器：' . mysql_error());
		}
		mysql_query('set names "utf8"', $dblnk);
		mysql_select_db(DB_NAME, $dblnk);
		mysql_query('truncate `score`', $dblnk);
		foreach($entries as $entry) {
			$entry=explode(',', iconv("GB18030", "UTF-8", $entry));
			if(count($entry)==15) {
				if(is_numeric($entry[6]) && is_numeric($entry[7])) {
					$result=mysql_query("insert into `score` (`student_id`, `student_name`, `course_id`, `course_title`, `course_sn`, `score`, `credit`, `teacher_id`, `teacher_name`, `date`, `make-up`, `attribute`, `class`, `major`, `term`) values ('".$entry[1]."', '".$entry[2]."', '".$entry[3]."', '".$entry[4]."', '".$entry[5]."', ".$entry[6].", ".$entry[7].", '".$entry[8]."', '".$entry[9]."', '".$entry[10]."', '".$entry[11]."', '".$entry[12]."', '".$entry[13]."', '".$entry[14]."', '".date2term($entry[10])."')", $dblnk);
					if(!$result) {
						die('无效的请求：' . mysql_error());
					}
				}
			}
		}
		mysql_close($dblnk);
		fclose($fhandle);
	}else {
		echo '导入文件失败。<br>';
	}
}
?>
	</body>
</html>