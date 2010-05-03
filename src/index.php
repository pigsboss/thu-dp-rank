<?php
  require_once('functions.php');
	session_start();
?>
<!doctype HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <style type="text/css">
			body {
				font-size: 0.8em;
				width: auto;
				white-space: nowrap;
			}
			table {border-collapse: collapse;}
		</style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
    <title>学生成绩统计——首页</title>
  </head>
  <body>
		<div>
		首页|<a href="import.php">导入</a>|<a href="statistics.php">统计</a>|<a href="logout.php">退出</a>
		<hr>
		<div>
		<h1>院系学生成绩统计系统</h1>
		<h2>开发日志</h2>
		<ul>
			<li><dt>2009年2月23日
				<dd>添加课表聚类以及相似性度量功能，添加用户权限设置。
			<li><dt>2009年2月22日
				<dd>添加计算成绩动态以及统计任选课比例功能。
			<li><dt>2009年2月20日
				<dd>创建系统，可以进行最基本的学分绩计算、排名、统计各课程平均分等操作。
		</ul>
		<hr>
		<div>
		<a href="mailto:huozhuoxi@tsinghua.org.cn">联系作者</a>
	</body>
</html>