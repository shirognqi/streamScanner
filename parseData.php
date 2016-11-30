<?php

define('AIMDIR', dirname(__FILE__).'/data/');	// 定义路径
define('SUFFIXOF_DADA_FILE', '.parse');		// 定义数据文件后缀，解析过的直接加个后缀

function endWith($haystack, $needle) {   
	$length = strlen($needle);  
	if($length == 0) return true;
	return (substr($haystack, -$length) === $needle);
}

function dealData($oriFile){

	$tempFile = $oriFile.'.tmp';
	if(!is_file($tempFile)) return ;
	
	$handle = fopen($tempFile, 'r');
    	while(!feof($handle)){
        	$oneLine 	= fgets($handle, 4096);
		if(!trim($oneLine)) continue;
		$step 		= explode('&',$oneLine);
		$setpArr 	= [];
		$i = 0;
		foreach($step as $oneStep){	
			$i++;
			$setpArr[$i] = [];
			$fields = explode('|',$oneStep);
			foreach($fields as $kv){
				if(strpos($kv, ':') === false) continue;
				list($key, $value) = explode(':',$kv);
				$setpArr[$i][$key] = $value;
			}
		}
		$outStr 	= '';
		$lastTime 	= 0;

		$timeSpendAll   = $setpArr[count($setpArr)]['time'] - $setpArr[1]['time'];

		foreach($setpArr as $index => $serializeStep){
			$outStrTmp      = '';
			$outStrTmp .= '(';
			if($index == 1) $outStrTmp .= (int)$serializeStep['time'].'::';
			$outStrTmp .= $serializeStep['callCount'].':';
			$outStrTmp .= $serializeStep['namespace'].':';
			$outStrTmp .= $serializeStep['function'].':';
			$outStrTmp .= $serializeStep['line'];
			if($index == count($setpArr)) $outStrTmp .= '::'.sprintf("%.4f",$timeSpendAll);
			$outStrTmp .= ')';
			if($index != 1 ){
				$timeSpend = $serializeStep['time'] - $lastTime;
				$timeSpendPrecent = sprintf("%.2f",$timeSpend/$timeSpendAll*100);
				$outStr .= '---'.sprintf("%.4f",$timeSpend).'('.$timeSpendPrecent.'%)'.'>>>'.$outStrTmp;
			}else{
				$outStr .= $outStrTmp;
			}
			$lastTime = $serializeStep['time'];
		}
		$outStr .= chr(10);
		file_put_contents($oriFile.SUFFIXOF_DADA_FILE, $outStr, FILE_APPEND);

    	}
    	fclose($handle);
	if(is_file($tempFile))
		unlink($tempFile);
	
}



$datas = scandir(AIMDIR);
sort($datas, SORT_NUMERIC);


foreach($datas as $day){
	
	if($day == '.' || $day == '..') continue;

	$files = scandir(AIMDIR.$day);

	foreach($files as $fk => $fvalue){
		if($fvalue == '.' || $fvalue == '..') 	continue;
		if(endWith($fvalue, SUFFIXOF_DADA_FILE))continue;
		
		rename(AIMDIR.$day.'/'.$fvalue, AIMDIR.$day.'/'.$fvalue.'.tmp');
		dealData(AIMDIR.$day.'/'.$fvalue);
	}

}



