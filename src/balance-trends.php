<?php
  require_once('functions.php');
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
    <title>统计结果</title>
  </head>
  <body>
		<div>
			<a href="index.php">首页</a>|<a href="import.php">导入</a>|<a href="statistics.php">统计</a>|结果
			<hr>
		<div>
<?php
	if(count($_POST)==0) {
		die("没有提交需要统计的信息。");
	}
	$class_condition=array();
	$classes=array();
	foreach(array_keys($_POST) as $condition) {
		$condition=explode("=", $condition);
		if($condition[0]=="class") {
			$class_condition[]='class="'.$condition[1].'"';
			$classes[]=$condition[1];
		}
	}
	$where_condition="";
	if(count($class_condition)>0) {
		$class_condition=implode(" or ", $class_condition);
		$where_condition=$class_condition;
	}
	if(!$dblnk=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true)) {
		die('无法连接数据库服务器：' . mysql_error());
	}
	mysql_query('set names "utf8"', $dblnk);
	mysql_select_db(DB_NAME, $dblnk);
	$student_optional_credits=array();
	$student_required_credits=array();
	$student_names=array();
	$terms=array();
	if(strlen($where_condition)>0) {
		$where_condition=' where '.$where_condition;
	}
	$result=mysql_query("select * from `score`" . $where_condition, $dblnk);
	if(!$result) {
		die("无效的请求：" . mysql_error());
	}
	if(mysql_num_rows($result)==0) {
		die("没有返回结果。");
	}
	while($entry=mysql_fetch_assoc($result)) {
		$entry['term']=term2num($entry['term']);
		if(!in_array($entry['term'], $terms)) {
			$terms[]=$entry['term'];
		}
		if(!array_key_exists($entry['term'], $student_optional_credits)) {
			$student_optional_credits[$entry['term']]=array();
			$student_required_credits[$entry['term']]=array();
		}
		if(!array_key_exists($entry['student_id'], $student_names)) {
			$student_names[$entry['student_id']]=strval($entry['student_name']);
		}
		if(!array_key_exists($entry['student_id'], $student_optional_credits)) {
			if($entry['attribute']=='任选') {
				$student_optional_credits[$entry['term']][$entry['student_id']]=floatval($entry['credit']);
			}else {
				$student_required_credits[$entry['term']][$entry['student_id']]=floatval($entry['credit']);
			}
		}else {
			if($entry['attribute']=='任选') {
				$student_optional_credits[$entry['term']][$entry['student_id']]=$student_optional_credits[$entry['term']][$entry['student_id']]+floatval($entry['credit']);
			}else {
				$student_required_credits[$entry['term']][$entry['student_id']]=$student_required_credits[$entry['term']][$entry['student_id']]+floatval($entry['credit']);
			}
		}
	}
	mysql_close($dblnk);
?>
<?php
	$student_ids=array_keys($student_names);
	sort($student_ids);
	$student_optional_ratios=array();
	$student_allterms_optional_ratios=array();
	$student_allterms_optional_credits=array();
	$student_allterms_required_credits=array();
	foreach($student_ids as $id) {
		$student_allterms_optional_credits[$id]=0;
		$student_allterms_required_credits[$id]=0;
		foreach($terms as $term) {
			$total_credit=$student_optional_credits[$term][$id]+$student_required_credits[$term][$id];
			if($total_credit>0) {
				$student_optional_ratios[$term][$id]=$student_optional_credits[$term][$id]/$total_credit;
			}else {
				$student_optional_ratios[$term][$id]=0;
			}
			$student_allterms_optional_credits[$id]=$student_allterms_optional_credits[$id]+$student_optional_credits[$term][$id];
			$student_allterms_required_credits[$id]=$student_allterms_required_credits[$id]+$student_required_credits[$term][$id];
		}
		$total_credit=$student_allterms_optional_credits[$id]+$student_allterms_required_credits[$id];
		if($total_credit>0) {
			$student_allterms_optional_ratios[$id]=$student_allterms_optional_credits[$id]/$total_credit;
		}else {
			$student_allterms_optional_ratios[$id]=0;
		}
	}
?>
<?php
	$title="<h1>".implode("、", $classes)."学生任选课比例动态</h1>";
	echo $title;
?>
<table border="1"><tbody><tr>
<th rowspan="2">学号<th rowspan="2">姓名<th colspan="<?php echo count($terms)+1;?>">任选课学分比例<tr>
<?php
	sort($terms);
	foreach($terms as $term) {
		echo '<td>'.num2shortterm($term);
	}
	echo '<td>所有学期';
	foreach($student_ids as $id) {
		echo '<tr><td>'.$id.'<td>'.$student_names[$id];
		foreach($terms as $term) {
			echo '<td>'.number_format(100*$student_optional_ratios[$term][$id], 2, '.', '').'%';
		}
		echo '<td>'.number_format(100*$student_allterms_optional_ratios[$id], 2, '.', '').'%';
	}
?>
<tr><td colspan="2" align=right>总体比例：
<?php
	foreach($terms as $term) {
		echo '<td>'.number_format(100*mean($student_optional_ratios[$term]), 2, '.', '').'%';
	}
	echo '<td>'.number_format(100*mean($student_allterms_optional_ratios), 2, '.', '').'%';
?>
</tbody></table>
<hr>
注：总体比例一项为平均值。
	</body>
</html>