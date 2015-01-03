<?php
	// Ref# http://simplehtmldom.sourceforge.net
	include('simple_html_dom.php');

	$html = file_get_html('http://bet.hkjc.com/football/index.aspx?lang=ch');
	// Ref Error text:
	// Hong Kong Jockey Club   You may visit any of the pages below about The Hong Kong Jockey Club and its related services.   >> Jockey Club Home Page No Information
	if( strlen($html) < 1000 ){
		header("refresh: 1;");
		echo '<p>Loading ...</p>';
	}

	$arr 	= array(array());
	$path_header = '#footballmaincontent > table > tbody > tr > ';

	// ----- ----- ----- ----- -----
	// Round of day

	foreach($html->find($path_header.'td.cday') as $data){
		$arr['round_of_day'][] 	= substr($data->plaintext, 10);
		$arr['week_day'][] 		= substr($data->plaintext, 6, 3);
	}

	// ----- ----- ----- ----- -----
	// Team

	foreach($html->find($path_header.'td.cflag img') as $data){
		$vs_pos = strpos($data->plaintext, '對');
		$arr['flag'][] = $data->src;
	}
	foreach($html->find($path_header.'td.cteams') as $data){
		$vs_pos = strpos($data->plaintext, '對');
		$arr['team_a'][] = trim(substr($data->plaintext, 0, $vs_pos));
		$arr['team_b'][] = trim(substr($data->plaintext, ($vs_pos + 4) ));
	}

	// ----- ----- ----- ----- -----
	// Time

	foreach($html->find($path_header.'td.cesst') as $data){
		if( strlen($data->plaintext) == 12 ){
			$year 	= date('Y');
			$month 	=  substr($data->plaintext, 3, 2);
			$day 	=  substr($data->plaintext, 0, 2);
			$hour 	=  substr($data->plaintext, 6, 2);
			$second =  substr($data->plaintext, 9, 2);

			// Next year issue
			if( date('m') == 12 && $month == 1 )
				$year = $year + 1;

			$arr['date'][] =  mktime(00, $second, $hour, $month, $day, $year);
		}else{
			// Not time
			$arr['date'][] = '';
		}
	}// foreach

	// ----- ----- ----- ----- -----
	// Rate: Home
	$i = 0;
	$home = array();
	$draw = array();
	$away = array();

	foreach($html->find($path_header.'td.codds') as $data){
		if( $i % 3 == 0){
			$arr['home'][] =  $data->plaintext;
		}elseif( $i % 3 == 1){
			$arr['draw'][] =  $data->plaintext;
		}else{
			$arr['away'][] =  $data->plaintext;
		}

		$i++;
	}

	// ----- ----- ----- ----- -----
	// Rate: Draw

	foreach($html->find($path_header.'td') as $data){
		$arr['draw'][] =  $data->plaintext;
	}

	// --------------------------------------------------
	// Fn.
	function img($img){
		$hkjc		= 'http://bet.hkjc.com/';
		$len 		= strlen($img);
		$base 		= basename($img);
		$q_pos 		= strpos($base, '?');
		$file_name 	= substr($base,  0, $q_pos);

		if( $file_name != 'flag_AD1.gif' )
			return '<img src="'.$hkjc.$img.'"> ';
		else
			return '<span style="display: inline-block; width: 20px;"></span>';
	}

	// --------------------------------------------------
	// Main output
	$str 	  =  '<p>受注球賽 - 合共 ' .(count($arr['date']) - 1).' 場, ';
	$str 	 .= '<thead><tr>';
	$str 	 .= '<td>星期</td>';
	$str 	 .= '<td>場次</td>';
	$str 	 .= '<td>日期</td>';
	$str 	 .= '<td>主隊</td>';
	$str 	 .= '<td>客隊</td>';
	$str 	 .= '<td>主勝</td>';
	$str 	 .= '<td>和</td>';
	$str 	 .= '<td>勝</td>';
	$str 	 .= '</thead></tr>';

	for($i = 1; /* Avoid title */ $i < count($arr['date']); $i++){
		$str .= '<tr>';
		$str .= '<td>'.$arr['week_day'][$i].'</td>';
		$str .= '<td>'.$arr['round_of_day'][$i].'</td>';
		$str .= '<td>'.date("Y-m-d h:i:sa", $arr['date'][$i]).'</td>';
		$str .= '<td>'.img($arr['flag'][$i]).$arr['team_a'][$i].'</td>';
		$str .= '<td>'.$arr['team_b'][$i].'</td>';
		$str .= '<td>'.$arr['home'][$i+1].'</td>';	// adjust
		$str .= '<td>'.$arr['draw'][$i+1].'</td>';	// adjust
		$str .= '<td>'.$arr['away'][$i+1].'</td>';	// adjust
		$str .= '</tr>';
	}

	echo $str;

	// ----- ----- ----- ----- -----

	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	echo "Page was created in ".substr($totaltime,0, 4)." seconds</p>";

 ?>