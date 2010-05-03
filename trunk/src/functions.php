<?php
  require_once("config.php");
  function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }

    return $val;
  }
	function get_upload_max_filesize() {
		$memory_limit=return_bytes(ini_get('memory_limit'));
		$post_max_size=return_bytes(ini_get('post_max_size'));
		$upload_max_filesize=return_bytes(ini_get('upload_max_filesize'));
		return min($memory_limit,$post_max_size,$upload_max_filesize);
	}
	function mean($vals) {
		return floatval(array_sum($vals))/floatval(count($vals));
	}
	function std($vals) {
		$var=0;
		$mean=mean($vals);
		foreach($vals as $val) {
			$var=$var+($val-$mean)*($val-$mean);
		}
		return sqrt($var/floatval(count($vals)));
	}
	function date2term($date) {
		if(strlen($date)<6) {
			return "";
		}
		$year=substr($date, 0, 4);
		$month=substr($date, 4, 2);
		if(!(is_numeric($year) && is_numeric($month))) {
			return "";
		}
		if(floatval($month)>=1 && floatval($month)<2) {
			$season="秋季学期";
			$year=strval(floatval($year)-1).'-'.$year.'学年度';
		}
		if(floatval($month)>2 && floatval($month)<8) {
			$season="春季学期";
			$year=strval(floatval($year)-1).'-'.$year.'学年度';
		}
		if(floatval($month)>8 && floatval($month)<=12) {
			$season="秋季学期";
			$year=$year.'-'.strval(floatval($year)+1).'学年度';
		}
		return $year . $season;
	}
	function term2num($term) {
		return str_replace("学年度秋季学期", "0", str_replace("学年度春季学期", "1", str_replace("-", "", $term)));
	}
	function num2term($num) {
		$year=substr($num, 0, 4) . '-' . substr($num, 4, 4);
		if($num % 2 == 0){
			return $year . '学年度秋季学期';
		}else {
			return $year . '学年度春季学期';
		}
	}
	function num2shortterm($num) {
		$year=substr($num, 2, 2) . '-' . substr($num, 6, 2);
		if($num % 2 == 0){
			return $year . '秋';
		}else {
			return $year . '春';
		}
	}
?>