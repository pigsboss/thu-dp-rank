<?php
  require_once('functions.php');
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
	$attribute_condition=array();
	$class_condition=array();
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
	$student_allterms_scores=array();
	$student_allterms_credits=array();
	$student_allterms_failures=array();
	$student_allterms_fail_credits=array();
	$student_classes=array();
	$student_majors=array();
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
		if(!array_key_exists($entry['term'], $student_scores)) {
			$student_scores[$entry['term']]=array();
			$student_credits[$entry['term']]=array();
			$student_failures[$entry['term']]=array();
			$student_fail_credits[$entry['term']]=array();
		}
		if(!array_key_exists($entry['student_id'], $student_names)) {
			$student_names[$entry['student_id']]=strval($entry['student_name']);
			$student_classes[$entry['student_id']]=strval($entry['class']);
			$student_majors[$entry['student_id']]=strval($entry['major']);
		}
		if(!array_key_exists($entry['student_id'], $student_scores[$entry['term']])) {
			$student_scores[$entry['term']][$entry['student_id']]=floatval($entry['score'])*floatval($entry['credit']);
			$student_credits[$entry['term']][$entry['student_id']]=floatval($entry['credit']);
			if($entry['score']<60) {
				$student_failures[$entry['term']][$entry['student_id']]=1;
				$student_fail_credits[$entry['term']][$entry['student_id']]=floatval($entry['credit']);
			}else {
				$student_failures[$entry['term']][$entry['student_id']]=0;
				$student_fail_credits[$entry['term']][$entry['student_id']]=0;
			}
		}else {
			$student_scores[$entry['term']][$entry['student_id']]=$student_scores[$entry['term']][$entry['student_id']]+floatval($entry['score'])*floatval($entry['credit']);
			$student_credits[$entry['term']][$entry['student_id']]=$student_credits[$entry['term']][$entry['student_id']]+floatval($entry['credit']);
			if($entry['score']<60) {
				$student_failures[$entry['term']][$entry['student_id']]=$student_failures[$entry['term']][$entry['student_id']]+1;
				$student_fail_credits[$entry['term']][$entry['student_id']]=$student_fail_credits[$entry['term']][$entry['student_id']]+floatval($entry['credit']);
			}
		}
	}
	sort($terms);
	mysql_close($dblnk);
?>
<?php
	$student_gpas=array();
	$student_allterms_gpas=array();
	$student_ranks=array();
	$student_allterms_ranks=array();
	$student_ids=array_keys($student_names);
	foreach($student_ids as $id) {
		$student_allterms_scores[$id]=0;
		$student_allterms_credits[$id]=0;
		$student_allterms_failures[$id]=0;
		$student_allterms_fail_credits[$id]=0;
		foreach($terms as $term) {
			if($student_credits[$term][$id]>0) {
				$student_gpas[$term][$id]=$student_scores[$term][$id]/$student_credits[$term][$id];
			}else {
				$student_gpas[$term][$id]=0;
			}
			$student_allterms_scores[$id]=$student_allterms_scores[$id]+$student_scores[$term][$id];
			$student_allterms_credits[$id]=$student_allterms_credits[$id]+$student_credits[$term][$id];
			$student_allterms_failures[$id]=$student_allterms_failures[$id]+$student_failures[$term][$id];
			$student_allterms_fail_credits[$id]=$student_allterms_fail_credits[$id]+$student_fail_credits[$term][$id];
		}
		if($student_allterms_credits[$id]>0) {
			$student_allterms_gpas[$id]=$student_allterms_scores[$id]/$student_allterms_credits[$id];
		}else {
			$student_allterms_gpas[$id]=0;
		}
	}
	foreach($terms as $term) {
		arsort($student_gpas[$term], SORT_NUMERIC);
		$student_ids=array_keys($student_gpas[$term]);
		$order=1;
		foreach($student_ids as $id) {
			$student_ranks[$term][$id]=$order;
			$order=$order+1;
		}
	}
	arsort($student_allterms_gpas, SORT_NUMERIC);
	$student_ids=array_keys($student_allterms_gpas);
	$order=1;
	foreach($student_ids as $id) {
		$student_allterms_ranks[$id]=$order;
		$order=$order+1;
	}
	sort($student_ids);
?>
<?php
	$title="<h1>".implode("、", $classes)."学生成绩动态</h1>";
	echo $title;
?>
<table border="1"><tbody><tr>
<th rowspan="2">学号<th rowspan="2">姓名<th colspan="<?php echo count($terms)+1;?>">名次<th colspan="<?php echo count($terms)+1;?>">学分绩<th colspan="<?php echo count($terms)+1;?>">总学分<th colspan="<?php echo count($terms)+1;?>">不及格课程<th colspan="<?php echo count($terms)+1;?>">不及格学分<tr>
<?php
	for($i=0; $i<5; $i++) {
		foreach($terms as $term) {
			echo '<td>'.num2shortterm($term);
		}
		echo '<td>所有学期';
	}
	foreach($student_ids as $id) {
		echo '<tr><td>'.$id.'<td>'.$student_names[$id];
		foreach($terms as $term) {
			echo '<td>'.$student_ranks[$term][$id];
		}
		echo '<td>'.$student_allterms_ranks[$id];
		foreach($terms as $term) {
			echo '<td>'.number_format($student_gpas[$term][$id], 2, '.', '');
		}
		echo '<td>'.number_format($student_allterms_gpas[$id], 2, '.', '');
		foreach($terms as $term) {
			echo '<td>'.$student_credits[$term][$id];
		}
		echo '<td>'.$student_allterms_credits[$id];
		foreach($terms as $term) {
			echo '<td>'.$student_failures[$term][$id];
		}
		echo '<td>'.$student_allterms_failures[$id];
		foreach($terms as $term) {
			echo '<td>'.$student_fail_credits[$term][$id];
		}
		echo '<td>'.$student_allterms_fail_credits[$id];
	}
?>
<tr><td colspan="6" align=right>总体成绩：
<?php
	foreach($terms as $term) {
		echo '<td>'.number_format(mean($student_gpas[$term]), 2, '.', '');
	}
	echo '<td>'.number_format(mean($student_allterms_gpas), 2, '.', '');
	foreach($terms as $term) {
		echo '<td>'.number_format(mean($student_credits[$term]), 1, '.', '');
	}
	echo '<td>'.number_format(mean($student_allterms_credits), 1, '.', '');
	foreach($terms as $term) {
		echo '<td>'.array_sum($student_failures[$term]);
	}
	echo '<td>'.array_sum($student_allterms_failures);
	foreach($terms as $term) {
		echo '<td>'.array_sum($student_fail_credits[$term]);
	}
	echo '<td>'.array_sum($student_allterms_fail_credits);
?>
</tbody></table>
<hr>
注：总体成绩一项中学分绩、总学分为平均值，不及格课程、不及格学分为累计值。
	</body>
</html>