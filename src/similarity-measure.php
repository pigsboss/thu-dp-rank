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
	$term_condition=array();
	$terms=array();
	$attributes=array();
	$classes=array();
	foreach(array_keys($_POST) as $condition) {
		$condition=explode("=", $condition);
		switch($condition[0]) {
			case "class":
				$class_condition[]='class="'.$condition[1].'"';
				$classes[]=$condition[1];
				break;
			case "term":
				$term_condition[]='term="'.$condition[1].'"';
				$terms[]=$condition[1];
				break;
		}
	}
	$where_condition="";
	if(count($class_condition)>0) {
		$class_condition=implode(" or ", $class_condition);
		$where_condition="(".$class_condition.")";
	}
	if(count($term_condition)>0) {
		$term_condition=implode(" or ", $term_condition);
		if(strlen($where_condition)==0) {
			$where_condition="(".$term_condition.")";
		}else {
			$where_condition=$where_condition." and (".$term_condition.")";
		}
	}
	if(!$dblnk=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true)) {
		die('无法连接数据库服务器：' . mysql_error());
	}
	mysql_query('set names "utf8"', $dblnk);
	mysql_select_db(DB_NAME, $dblnk);
	$student_courses=array();
	$student_names=array();
	$student_classes=array();
	$student_majors=array();
	$course_titles=array();
	$course_credits=array();
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
		if(!array_key_exists($entry['student_id'], $student_names)) {
			$student_courses[$entry['student_id']]=array();
			$student_names[$entry['student_id']]=strval($entry['student_name']);
			$student_classes[$entry['student_id']]=strval($entry['class']);
			$student_majors[$entry['student_id']]=strval($entry['major']);
		}
		if(!array_key_exists($entry['course_id'], $course_titles)) {
			$course_titles[$entry['course_id']]=$entry['course_title'];
			$course_credits[$entry['course_id']]=$entry['credit'];
		}
		$student_courses[$entry['student_id']][$entry['course_id']]=1;
	}
	mysql_close($dblnk);
?>
<?php
	$student_ids=array_keys($student_names);
	$course_ids=array_keys($course_titles);
	$course_nums_student=array();
	$student_center=array();
	$num_students=count($student_ids);
	$student_dists=array();
	foreach($student_ids as $sid) {
		foreach($course_ids as $cid) {
			if(!array_key_exists($cid, $student_courses[$sid])) {
				$student_courses[$sid][$cid]=0;
			}
		}
	}
	foreach($course_ids as $cid) {
		$course_nums_students[$cid]=0;
		foreach($student_ids as $sid) {
			$course_nums_students[$cid]=$course_nums_students[$cid]+$student_courses[$sid][$cid];
		}
		$student_center[$cid]=$course_nums_students[$cid]/$num_students;
		/*
		if($student_center[$cid]>=0.5) {
			$student_center[$cid]=1;
		}else {
			$student_center[$cid]=0;
		}
		*/
	}
	foreach($student_ids as $sid){
		$student_dists[$sid]=0;
		foreach($course_ids as $cid) {
			$student_dists[$sid]=$student_dists[$sid]+($student_courses[$sid][$cid]-$student_center[$cid])*($student_courses[$sid][$cid]-$student_center[$cid])*$course_credits[$cid]*$course_credits[$cid];
		}
		$student_dists[$sid]=sqrt($student_dists[$sid]);
	}
	if(!arsort($student_dists, SORT_NUMERIC)) {
		die("排序失败。");
	}
	$student_ids=array_keys($student_dists);
	if(!arsort($student_center, SORT_NUMERIC)) {
		die("排序失败。");
	}
	$course_ids=array_keys($student_center);
?>
<?php
	$title="<h1>".implode("、", $classes)."学生课表相似性度量</h1>";
	echo $title;
?>
<h2>课表聚类中心</h2>
<table border="1"><tbody>
<tr><th>课程号<th>课程名<th>学分
<?php
	foreach($course_ids as $id) {
		if($student_center[$id]>=0.5) {
			echo '<tr><td>'.$id.'<td>'.$course_titles[$id].'<td>'.$course_credits[$id];
		}
	}
?>
</tbody></table><hr>
<h2>学生课表与课表聚类中心的差异</h2>
<table border="1"><tbody>
<tr><th>学号<th>姓名<th>差异<th>班级<th>专业
<?php
	foreach($student_ids as $id) {
		echo '<tr><td>'.$id.'<td>'.$student_names[$id].'<td>'.number_format($student_dists[$id], 2, '.', '').'<td>'.$student_classes[$id].'<td>'.$student_majors[$id];
	}
?>
</tbody></table><hr>
<h2>学生选课概率</h2>
<table border="1"><tbody>
<tr><th>课程号<th>课程名<th>选中概率
<?php
	foreach($course_ids as $id) {
		echo '<tr><td>'.$id.'<td>'.$course_titles[$id].'<td>'.number_format(100*$student_center[$id], 2, '.', '').'%';
	}
?>
</tbody></table><hr>
注：课表聚类中心使用最大似然统计计算，差异使用欧氏距离函数计算。
	</body>
</html>