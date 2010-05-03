<?php
  require_once('functions.php');
	session_start();
	session_destroy();
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
		<script type="text/javascript">
			$(document).ready(function() {
				$("form").submit(function() {
					$("input[name='name']").val(hex_md5($("input[name='display-name']").val()));
					$("input[name='password']").val(hex_md5($("input[name='display-password']").val()));
				});
			});
		</script>
		<title>学生成绩统计——首页</title>
	</head>
	<body>
		<form action="index.php" method="post">
			<table><tbody><tr>
				<td align=right>用户名：<td><input type="text" name="display-name"><tr>
				<td align=right>密码：<td><input type="password" name="display-password"></tbody>
			</table>
			<input type="hidden" name="name"><input type="hidden" name="password">
			<input type="submit" value="登录"><input type="reset" value="重置">
		</form>
	</body>
</html>