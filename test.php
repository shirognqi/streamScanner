<?php

require(__DIR__.DIRECTORY_SEPARATOR.streamScanner.php);



class AAA{
	public function test(){
		PL::instance()->set(false);
		usleep(100);
		PL::instance()->set(false);
		usleep(50);
		PL::instance()->set('line2');

	}
}

class BBB{
	public function test(){
		PL::instance()->set(false);
		usleep(100);
		PL::instance()->set(false);
		usleep(50);
		PL::instance()->set('line2');
		usleep(50);
		PL::instance()->set('line2');

	}
}

$aaa = new AAA();	// 业务1
$aaa->test();

$bbb = new BBB();	// 业务2
$bbb->test();

echo '由于是建立在先输入缓存在落盘的思路，这个脚本得跑100次才能输出日志，Notice：务必给目录下创建data目录并赋予0777权限；';
