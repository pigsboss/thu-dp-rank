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
    <title>学生成绩统计——统计</title>
  </head>
  <body>
		<div>
		<a href="index.php">首页</a>|<a href="import.php">导入</a>|统计|<a href="logout.php">退出</a>
		<hr>
<?php
	if(!$dblnk=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true)) {
		die('无法连接数据库服务器：' . mysql_error());
	}
	mysql_query('set names "utf8"', $dblnk);
	mysql_select_db(DB_NAME, $dblnk);
	$classes=array();
	$attributes=array();
	$terms=array();
	$result=mysql_query("select * from `score`", $dblnk);
	if(!$result) {
		die("无效的请求：" . mysql_error());
	}
	if(mysql_num_rows($result)==0) {
		die("没有返回结果。");
	}
	while($entry=mysql_fetch_assoc($result)) {
		if(!in_array($entry['class'], $classes)) {
			$classes[]=$entry['class'];
		}
		if(!in_array($entry['attribute'], $attributes)) {
			$attributes[]=$entry['attribute'];
		}
		if(!in_array($entry['term'], $terms)) {
			$terms[]=$entry['term'];
		}
	}
	if(!sort($classes)) {
		die('排序失败。');
	}
	if(!sort($terms)) {
		die('排序失败。');
	}
?>
	<div>
	<h2>静态信息统计</h2>
	<form action="statistics-result.php" method="post">
		<table border="1"><tbody><tr>
			<th>班级<th>学期<th>课程属性<tr>
			<td align=left valign=top>
<?php
	foreach($classes as $class) {
		echo '<input type="checkbox" name="class='.$class.'" value="'.$class.'">'.$class.'<br>';
	}
?>
			<td align=left valign=top>
<?php
	foreach($terms as $term) {
		echo '<input type="checkbox" name="term='.$term.'" value="'.$term.'">'.$term.'<br>';
	}
?>
			<td align=left valign=top>
<?php
	foreach($attributes as $attribute) {
		echo '<input type="checkbox" name="attribute='.$attribute.'" value="'.$attribute.'">'.$attribute.'<br>';
	}
?>
		</tbody></table>
		<input type="hidden" name="post" value="true">
		<input type="submit" value="统计"><input type="reset" value="重置">
	</form>
	<hr>
	<h2>学生成绩动态</h2>
	<form action="gpa-trends.php" method="post">
		<table border="1"><tbody><tr>
			<th>班级<th>课程属性<tr>
			<td align=left valign=top>
<?php
	foreach($classes as $class) {
		echo '<input type="checkbox" name="class='.$class.'" value="'.$class.'">'.$class.'<br>';
	}
?>
			<td align=left valign=top>
<?php
	foreach($attributes as $attribute) {
		echo '<input type="checkbox" name="attribute='.$attribute.'" value="'.$attribute.'">'.$attribute.'<br>';
	}
?>
		</tbody></table>
		<input type="hidden" name="post" value="true">
		<input type="submit" value="统计"><input type="reset" value="重置">
	</form>
	<hr>
	<h2>任选课比例动态</h2>
	<form action="balance-trends.php" method="post">
		<table border="1"><tbody><tr>
			<th>班级<tr>
			<td align=left valign=top>
<?php
	foreach($classes as $class) {
		echo '<input type="checkbox" name="class='.$class.'" value="'.$class.'">'.$class.'<br>';
	}
?>
		</tbody></table>
		<input type="hidden" name="post" value="true">
		<input type="submit" value="统计"><input type="reset" value="重置">
	</form>
	<hr>
	<h2>课表相似性度量</h2>
	<form action="similarity-measure.php" method="post">
		<table border="1"><tbody><tr>
			<th>班级<th>学期<tr>
			<td align=left valign=top>
<?php
	foreach($classes as $class) {
		echo '<input type="checkbox" name="class='.$class.'" value="'.$class.'">'.$class.'<br>';
	}
?>
			<td align=left valign=top>
<?php
	foreach($terms as $term) {
		echo '<input type="checkbox" name="term='.$term.'" value="'.$term.'">'.$term.'<br>';
	}
?>
		</tbody></table>
		<input type="hidden" name="post" value="true">
		<input type="submit" value="统计"><input type="reset" value="重置">
	</form>
<?php
	mysql_close($dblnk);
?>
	</body>
</html>