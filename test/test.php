<?php

require(__DIR__.'/../vendor/autoload.php');



class AAA{
	public function test(){
		StreamScanner::instance()->set(false);
		usleep(100);
		StreamScanner::instance()->set(false);
		usleep(50);
		StreamScanner::instance()->set('line2');

	}
}

class BBB{
	public function test(){
		StreamScanner::instance()->set(false);
		usleep(100);
		StreamScanner::instance()->set(false);
		usleep(50);
		StreamScanner::instance()->set('line2');
		usleep(50);
		StreamScanner::instance()->set('line2');

	}
}

$aaa = new AAA();	// 业务2
$aaa->test();
$bbb = new BBB();	// 业务2
$bbb->test();
if(APCU_SWITCH) {
	echo '由于是建立在先输入缓存在落盘的思路，这个脚本得跑100次才能输出日志，请到data目录下查看.data文件，之后执行php parseData.php解析日志即可。Notice：务必给目录下创建data目录并赋予0777权限；';
} else {
	echo '请到data目录下查看.data文件，之后执行php parseData.php解析日志即可。Notice：务必给目录下创建data目录并赋予0777权限；';
}
