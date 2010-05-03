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
    <title>统计结果</title>
  </head>
  <body>
		<div>
			<a href="index.php">首页</a>|<a href="import.php">导入</a>|<a href="statistics.php">统计</a>|结果|<a href="logout.php">退出</a>
			<hr>
		<div>
<?php
	if(count($_POST)==0) {
		die("没有提交需要统计的信息。");
	}
	$attribute_condition=array();
	$class_condition=array();
	$term_condition=array();
	$terms=array();
	$attributes=array();
	$classes=array();
	foreach(array_keys($_POST) as $condition) {
		$condition=explode("=", $condition);
		switch($condition[0]) {
			case "attribute":
				$attribute_condition[]='attribute="'.$condition[1].'"';
				$attributes[]=$condition[1];
				break;
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
	if(count($attribute_condition)>0) {
		$attribute_condition=implode(" or ", $attribute_condition);
		$where_condition="(".$attribute_condition.")";
	}
	if(count($class_condition)>0) {
		$class_condition=implode(" or ", $class_condition);
		if(strlen($where_condition)==0) {
			$where_condition="(".$class_condition.")";
		}else {
			$where_condition=$where_condition." and (".$class_condition.")";
		}
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
	$student_scores=array();
	$student_credits=array();
	$student_names=array();
	$student_failures=array();
	$student_fail_credits=array();
	$student_classes=array();
	$student_majors=array();
	$course_ids=array();
	$course_sns=array();
	$course_titles=array();
	$course_teacher_names=array();
	$course_teacher_ids=array();
	$course_scores=array();
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
		if(!array_key_exists($entry['student_id'], $student_scores)) {
			$student_scores[$entry['student_id']]=floatval($entry['score'])*floatval($entry['credit']);
			$student_credits[$entry['student_id']]=floatval($entry['credit']);
			$student_names[$entry['student_id']]=strval($entry['student_name']);
			$student_classes[$entry['student_id']]=strval($entry['class']);
			$student_majors[$entry['student_id']]=strval($entry['major']);
			if($entry['score']<60) {
				$student_failures[$entry['student_id']]=1;
				$student_fail_credits[$entry['student_id']]=floatval($entry['credit']);
			}else {
				$student_failures[$entry['student_id']]=0;
				$student_fail_credits[$entry['student_id']]=0;
			}
		}else {
			if($entry['score']<60) {
				$student_failures[$entry['student_id']]=$student_failures[$entry['student_id']]+1;
				$student_fail_credits[$entry['student_id']]=$student_fail_credits[$entry['student_id']]+floatval($entry['credit']);
			}
			$student_scores[$entry['student_id']]=$student_scores[$entry['student_id']]+floatval($entry['score'])*floatval($entry['credit']);
			$student_credits[$entry['student_id']]=$student_credits[$entry['student_id']]+floatval($entry['credit']);
		}
		if(!array_key_exists($entry['course_id'].$entry['course_sn'], $course_ids)) {
			$course_ids[$entry['course_id'].$entry['course_sn']]=$entry['course_id'];
			$course_sns[$entry['course_id'].$entry['course_sn']]=$entry['course_sn'];
			$course_titles[$entry['course_id'].$entry['course_sn']]=$entry['course_title'];
			$course_teacher_names[$entry['course_id'].$entry['course_sn']]=$entry['teacher_name'];
			$course_teacher_ids[$entry['course_id'].$entry['course_sn']]=$entry['teacher_id'];
			$course_scores[$entry['course_id'].$entry['course_sn']]=array();
			$course_scores[$entry['course_id'].$entry['course_sn']][]=floatval($entry['score']);
		}else {
			$course_scores[$entry['course_id'].$entry['course_sn']][]=floatval($entry['score']);
		}
	}
	mysql_close($dblnk);
?>
<?php
	$student_gpas=array();
	$student_ids=array_keys($student_names);
	foreach($student_ids as $id) {
		if($student_credits[$id]>0) {
			$student_gpas[$id]=$student_scores[$id]/$student_credits[$id];
		}else {
			$student_gpas[$id]=0;
		}
	}
	if(!arsort($student_gpas, SORT_NUMERIC)) {
		die("排序失败。");
	}
	$student_ids=array_keys($student_gpas);
?>
<?php
	$title="<h1>".implode("、", $classes)."学生成绩统计结果</h1>";
	echo $title;
?>
<table border="1"><tbody>
<tr><th>名次<th>学号<th>姓名<th>学分绩<th>总学分<th>不及格课程<th>不及格学分<th>班级<th>专业
<?php
	$order=1;
	foreach($student_ids as $id) {
		echo '<tr><td>'.$order.'<td>'.$id.'<td>'.$student_names[$id].'<td>'.number_format($student_gpas[$id], 2, '.', '').'<td>'.$student_credits[$id].'<td>'.$student_failures[$id].'<td>'.$student_fail_credits[$id].'<td>'.$student_classes[$id].'<td>'.$student_majors[$id];
		$order=$order+1;
	}
?>
</tbody></table><hr>
<table border="1"><tbody>
<tr><th>课程号<th>课序号<th>课程名<th>教师名<th>教师号<th>上课人数<th>最高分<th>最低分<th>平均分<th>方差
<?php
	$course_true_ids=array_keys($course_ids);
	$course_means=array();
	$course_vars=array();
	foreach($course_true_ids as $id) {
		$course_vars[$id]=std($course_scores[$id]);
		$course_means[$id]=mean($course_scores[$id]);
	}
	if(!arsort($course_means, SORT_NUMERIC)) {
		die("排序失败。");
	}
	$course_true_ids=array_keys($course_means);
	foreach($course_true_ids as $id) {
		if($course_teacher_names[$id]=='""') {
			var_dump($course_teacher_names[$id]);
			die("");
		}
		echo '<tr><td>'.$course_ids[$id].'<td>'.$course_sns[$id].'<td>'.$course_titles[$id].'<td>'.$course_teacher_names[$id].'<td>'.$course_teacher_ids[$id].'<td>'.count($course_scores[$id]).'<td>'.max($course_scores[$id]).'<td>'.min($course_scores[$id]).'<td>'.number_format($course_means[$id], 2, '.', '').'<td>'.number_format($course_vars[$id], 1, '.', '');
	}
?>
</tbody></table><hr>
人数：<?php echo count($student_gpas) . '<br>';?>
学期：<?php
	if(count($terms)>0) {
		echo implode("、", $terms);
	}else {
		echo "所有学期";
	}
?>
<br>
课程属性：<?php
	if(count($attributes)>0) {
		echo implode("、", $attributes);
	}else {
		echo "全部课程";
	}
?>
<br>
学分绩最大值：<?php echo number_format(max($student_gpas), 2, '.', '') . '<br>';?>
学分绩最小值：<?php echo number_format(min($student_gpas), 2, '.', '') . '<br>';?>
学分绩均值：<?php echo number_format(mean($student_gpas), 2, '.', '') . '<br>';?>
学分绩方差：<?php echo number_format(std($student_gpas), 1, '.', '') . '<br>';?>
总学分最大值：<?php echo max($student_credits) . '<br>';?>
总学分最小值：<?php echo min($student_credits) . '<br>';?>
总学分均值：<?php echo number_format(mean($student_credits), 1, '.', '') . '<br>';?>
总学分方差：<?php echo number_format(std($student_credits), 1, '.', '') . '<br>';?>
	<hr>
	<div>
	<p>统计完成
	</body>
</html>