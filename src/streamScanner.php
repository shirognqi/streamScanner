<?php

define('LINES_FILTER_CONTROLLER', false);	// 是否过滤多流程，一段代码可能会是多个流程的复用，
						// 如果只想看其中几条流程，开启这个（设置为true），并且在使
						// 用!!!registerLines!!!方法进行注册，只有注册过的才能被记录，
						// 默认主线是开启的，可以设置关闭，传入的数组举例如下
						//	array(
						//		PL::DEFAULTLINENAME=>false,	// 关闭主线
						//		'line2'	=> true,				// 打开line2线
						//		'line3' => false, 				// 关闭line3线
						//	)；

define('APCU_SWITCH',	false);			// 启用apcu缓存记录日志的开关；

define('DISDIR', 	dirname(__FILE__).'/data/'); // 生成的数据的目录地址；

class StreamScanner{

	private function __construct(){}   
	private function __clone(){}
	static $_instance = null;
	public static function instance(){    
		if(! (self::$_instance instanceof self) ){
			self::$_instance = new self();    
		}
		
		return self::$_instance;    
		
	}

	static $Lines = [];
	
	
	static $lineCount = [];
	
	const DEFAULTLINENAME = 'defaultLine';

	public function __destruct(){
		$ProjectLine = static::get();
		//print_r($ProjectLine);
		$this->saveMssage($ProjectLine);
	}

	
	public function set($lineName = false, $message = false){

		if($lineName===false){
			$_lineName = self::DEFAULTLINENAME;
		}elseif(static::nameTest($lineName)){
			$_lineName = $lineName;
		}else{
			throw new exception();
			exit;
		}
		

		// 过滤器
		if(defined('LINES_FILTER_CONTROLLER'))
			if(LINES_FILTER_CONTROLLER)
				if(!static::linesFilter($_lineName))
					return ;
		$msg = '';
		
		if(static::nameTest($message)) $msg = $message;


		$breakTrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
		
		$callPlaceCount = count($breakTrace)>1 ? 1 : 0;
		
		$parent  	= $breakTrace[0];
		$callPlace 	= $breakTrace[$callPlaceCount];

		$fileName       = $parent['file'] ?? 	'';
		$lineNumber     = $parent['line'] ?? 	'';
		$nameSpace      = $callPlace['class'] 	?? '';
                $functionName   = $callPlace['function']?? '';
		
		list($usec,$sec)= explode(" ", microtime());
		$time           = (float)$usec + (float)$sec;
		$callCount 	= static::getLineNumber($_lineName);
		
		$logString 	= sprintf(
					"callCount:%s|file:%s|namespace:%s|function:%s|line:%s|time:%s|message:%s",
					$callCount,
					$fileName,
					$nameSpace,
					$functionName,
					$lineNumber,
					$time,
					$msg
		);
		
		
		if(!isset(static::$Lines[$_lineName]))
			static::$Lines[$_lineName] = [];
		static::$Lines[$_lineName][$callCount] = $logString;

	}


	static function get($lineName=false, $callCount=false){
		if($lineName == false && $callCount == false)
			return static::$Lines;
		if(static::nameTest($lineName) && $callCount == false){
			if(isset(static::$Lines[$lineName]))
				return static::$Lines[$lineName];
			return false;
		}
		if(static::nameTest($lineName) && is_numeric($callCount)){
			if(!isset(static::$Lines[$lineName]))
				return false;
			if(!isset(static::$Lines[$lineName][$callCount]))
				return false;
			return static::$Lines[$lineName][$callCount];
		}
		return false;
	}

	static function getLineNumber($lineName){
		if(!isset(static::$lineCount[$lineName])){
			static::$lineCount[$lineName] = 1;
		}else{
			static::$lineCount[$lineName] = static::$lineCount[$lineName]+1;
		}
		return static::$lineCount[$lineName];
		
	}




	static function nameTest($lineName){

		if(is_string($lineName)){

			if( $lineName!=='' && $lineName != self::DEFAULTLINENAME){

				return true;

			}
			return false;
		}elseif(is_numeric($lineName)){
			return true;
		}
		return false;
	}
	
	static $lineFilter = [
		self::DEFAULTLINENAME => true
	];
	
	public function registerLines(array $Lines = []){
		foreach($$Lines as $k => $v){
			static::$lineFilter[$k] = $v;
		}
	}

	public function getRegisterLines(){
		return static::$lineFilter;
	}
		
	static function linesFilter($lineName){
		return static::$lineFilter[$lineName]??false;
	}


	public function saveMssage($input, $overFlowFlag = false){
		
		if($overFlowFlag) usleep(10000);
		
		$apcuSwitch = defined('APCU_SWITCH')?APCU_SWITCH:false;
		foreach($input as $lineName => $lines){
			if($apcuSwitch){
				$apcuIncKey 	= 'ProjectLinesINC_'.$lineName;

				$apcuInc 	= apcu_inc($apcuIncKey);
				if($apcuInc>100){
					if($apcuInc>100*1.2){
						apcu_store($apcuIncKey,0);
						$apcuInc = 0;
					}else{
						$this->saveMssage([$lineName => $lines], true); 
					}
				}
				if($apcuInc == 100){
					$keyArr = [];
					foreach(range(1,99) as $num)
						$keyArr[] = 'ProjectLinesKey_' . $lineName . $num;
					
					$messages  = apcu_fetch($keyArr);
					apcu_store($apcuIncKey,0);
					$messages[]= implode('&',$lines);
					$flieDir = rtrim(DISDIR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.date("Ymd");
					if(!is_dir($flieDir)) mkdir($flieDir);

					$file = $flieDir.DIRECTORY_SEPARATOR.$lineName.'.data';
					file_put_contents($file, implode(chr(10), $messages).chr(10), FILE_APPEND );

				}else{
					$key = 'ProjectLinesKey_' . $lineName . $apcuInc;
					apcu_store($key, implode('&',$lines));
				}
			}else{
				$flieDir = rtrim(DISDIR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.date("Ymd");
				if(!is_dir($flieDir)) mkdir($flieDir);

				$file = $flieDir.DIRECTORY_SEPARATOR.$lineName.'.data';
				file_put_contents($file, implode('&',$lines).chr(10), FILE_APPEND );
			}
		}
	}

}


