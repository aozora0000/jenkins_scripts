<?php
execute();

function execute() {
	switch(true) {
		case isset($argv[1]) :
		$file = $argv[1];
		break;
		case isset($_ENV["WORKSPACE"]) :
		$file = $_ENV["WORKSPACE"]."/.jenkins.yml";
		break;
		default:
		$file = "./.jenkins.yml";
		break;
	}
	$scripter = new Script($file);

	$docker = $scripter->createDockerScript();
	$step = $scripter->createStepScript();
	$notify = $scripter->createNotifyScript();

	file_put_contents("build.sh",$docker);
	file_put_contents("step.sh",$step);
	file_put_contents("notify.sh",$notify);
}
