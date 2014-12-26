<?php
class Script {
	CONST IMAGE = "container";
	CONST STEP = "steps";
	CONST STEP_NAME = "name";
	CONST STEP_CODE = "code";
	CONST NOTIFY = "notify";
	CONST NOTIFY_SERVICE = "service";
	CONST NOTIFY_APITOKEN = "token";
	CONST NOTIFY_USERNAME = "from";
	CONST NOTIFY_ROOM_ID = "room_id";

	protected $parser;
	public function __construct($filename) {
		try {
			$this->parser = new YamlParser($filename);
		} catch (InvalidArgumentException $e) {
			return self::createError($e->getMessage());
		} catch (LogicException $e) {
			return self::createError($e->getMessage());
		} catch (Exception $e) {
			return self::createError($e->getMessage());
		}
	}

	public function createDockerScript() {
		try {
			$image = $this->parser->getImage(self::IMAGE);
			return self::createDockerLoader($image);
		} catch(Exception $e) {
			return self::createError($e->getMessage);
		}
	}

	public function createStepScript() {
		$steps = $this->parser->getStep(self::STEP);
		$script = "";
		if($steps) {
			$count = count($steps);
			$script .= self::createStart($count);
			for($i = 0; $i < $count; ++$i) {
				switch(true) {
					case !isset($steps[$i][self::STEP_CODE]) :
					$command = self::joinStepScript($steps[$i]);
					break;
					case isset($steps[$i][self::STEP_CODE]) :
					$command = self::joinStepScript($steps[$i][self::STEP_CODE]);
					break;
				}
				switch(true) {
					case !isset($steps[$i][self::STEP_NAME]) :
					$title = "NOTITLE";
					break;
					case isset($steps[$i][self::STEP_NAME]) :
					$title = $steps[$i][self::STEP_NAME];
					break;
				}
				$script .= self::createStep($i,$count,$title,$command);
			}
			return $script;
		} else {
			$script .= self::createStart(0);
			return $script;
		}
	}

	public function createNotifyScript() {
		$steps = $this->parser->getNotify(self::NOTIFY);
		$script = "";
		if($steps) {
			foreach($steps as $step) {
				if(isset($step[self::NOTIFY_SERVICE])) {
					switch($step[self::NOTIFY_SERVICE]) {
						case "hipchat" :
							if(!isset($step[self::NOTIFY_APITOKEN])) { break; }
							if(!isset($step[self::NOTIFY_ROOM_ID])) { break; }
							$from = (isset($step[self::NOTIFY_USERNAME])) ? $step[self::NOTIFY_USERNAME] : "Jenkins";
							$room_id = $step[self::NOTIFY_ROOM_ID];
							$api_token = $step[self::NOTIFY_APITOKEN];
							$script .= "cat /tmp/message | hipchat-notify -t {$api_token} -r {$room_id} -f {$from} -c \$COLOR".PHP_EOL;
							break;
						case "idobata" :
							if(!isset($step[self::NOTIFY_APITOKEN])) { break; }
							$api_token = $step[self::NOTIFY_APITOKEN];
							$script .= "cat /tmp/message | idobata-notify -t {$api_token}".PHP_EOL;
							break;
						default :
							break;
					}
				}
			}
			return self::createNotify($script);
		} else {
			return "";
		}
	}

	public static function joinStepScript($command) {
		return preg_replace("/\n/"," && \\\n",$command);
	}

	public static function createDockerLoader($image) {
		return <<< EOM
#!/bin/bash -le
echo -e "\e[32m
#######################################
# DockerStart
#######################################
\e[m"

chmod -R 777 \$WORKSPACE/build.sh && \
chmod -R 777 \$WORKSPACE/step.sh && \
chmod -R 777 \$WORKSPACE/notify.sh
DOCKER_RESULT=0
docker run --rm -v \$WORKSPACE:/home/worker/workspace -w /home/worker/workspace -u worker -t $image /bin/bash -l step.sh || DOCKER_RESULT=$?
docker rm `docker ps -a -q` || true

source \$WORKSPACE/notify.sh

rm -f \$WORKSPACE/step.sh && \
rm -f \$WORKSPACE/notify.sh && \
rm -f \$WORKSPACE/build.sh && \
rm -rf /tmp/message
exit \$DOCKER_RESULT
EOM;
	}

	public static function createStep($i,$count,$title,$script) {
		$i++;
		$step = sprintf("%d/%d",$i,$count);
		return <<< EOM
echo -e "\e[32m
#######################################
# Step: $step $title
#######################################
\e[m"

$script


EOM;
	}

	public static function createNotify($script) {
		return <<< EOM
#!/bin/bash -le
echo -e "\e[32m
#######################################
# Notification
#######################################
\e[m"

if [ "\$DOCKER_RESULT" -eq "0" ]
then
LABEL='<span class="label label-success">PASSED</span>'
COLOR='green'
else
LABEL='<span class="label label-important">FAILED</span>'
COLOR='red'
fi

COMMIT_ID=\$(git log -1 --pretty='%h')
COMMIT_AUTHER=\$(git log -1 --pretty='%cn')
COMMIT_COMMENT=\$(git log -1 --pretty='%s')
COMMIT_BRANCH=\$(git name-rev --name-only \$COMMIT_ID)
REMOTE_REPOSITORY=\$(git-parser `git config --get remote.origin.url`)
printf "Build \$REMOTE_REPOSITORY<a href="\$JOB_URL">#\$BUILD_NUMBER</a> (\$COMMIT_BRANCH - \$COMMIT_ID): \$LABEL \n\$COMMIT_AUTHER: \$COMMIT_COMMENT" > /tmp/message

$script
EOM;
	}

	public static function createStart($count) {
		return <<< EOM
#!/bin/bash -lxe
if [ -f ~/.bashrc ] ; then
. ~/.bashrc
fi
echo -e "\e[32m
#######################################
# ScriptStart $count Steps
#######################################
\e[m"

EOM;
	}

	public static function createEnd() {
		return <<< EOM
echo -e "\e[32m
#######################################
# ScriptEnd
#######################################
\e[m"

EOM;
	}

	public static function createError($errorMessage) {
		return <<< EOM
echo -e "\e[31m
#######################################
# Error: $errorMessage
#######################################
\e[m"
exit 1

EOM;
	}
}
