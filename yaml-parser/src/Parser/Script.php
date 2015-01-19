<?php
namespace Parser;
use \InvalidArgumentException;
use \LogicException;
use \Exception;

class Script {
    CONST IMAGE = "container";
    CONST LINK = "links";
    CONST LINK_NAME = "name";
    CONST LINK_CONTAINER = "container";
    CONST STEP = "steps";
    CONST STEP_NAME = "name";
    CONST STEP_CODE = "code";
    CONST NOTIFY = "notify";
    CONST NOTIFY_SERVICE = "service";
    CONST NOTIFY_APITOKEN = "token";
    CONST NOTIFY_USERNAME = "from";
    CONST NOTIFY_ROOM_ID = "room_id";
    CONST NOTIFY_IKACHAN = "ikachan";

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
            return self::createBuildLoader($image);
        } catch(Exception $e) {
            return self::createError($e->getMessage);
        }
    }

    public function createLinksScript() {
        $links = $this->parser->getLinks(self::LINK);
        $script = array();
        if($links) {
            for($i = 0; $i <= count($links); $i++) {
                if(isset($links[$i][self::LINK_NAME]) && isset($links[$i][self::LINK_CONTAINER])) {
                    $script[] = self::createLinks($i+1,$links[$i][self::LINK_NAME],$links[$i][self::LINK_CONTAINER]);
                }
            }
        }
        return ($script) ? implode(" && \\\n",$script) : "";
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
                            $script .= "cat /tmp/jenkins_message_normal | hipchat-notify -t {$api_token} -r {$room_id} -f {$from} -c \$COLOR".PHP_EOL;
                            break;
                        case "idobata" :
                            if(!isset($step[self::NOTIFY_APITOKEN])) { break; }
                            $api_token = $step[self::NOTIFY_APITOKEN];
                            $script .= "cat /tmp/jenkins_message_normal | idobata-notify -t {$api_token}".PHP_EOL;
                            break;
                        case "irc" :
                            if(!isset($step[self::NOTIFY_IKACHAN])) { break; }
                            if(!isset($step[self::NOTIFY_ROOM_ID])) { break; }
                            $ikachan = $step[self::NOTIFY_IKACHAN];
                            $room_id = $step[self::NOTIFY_ROOM_ID];
                            $script .= "tail -n 1 /tmp/jenkins_message_short | irc-notify -o {$ikachan} -c {$api_token}".PHP_EOL;
                            break;
                        case "chatwork" :
                            if(!isset($step[self::NOTIFY_APITOKEN])) { break; }
                            if(!isset($step[self::NOTIFY_ROOM_ID])) { break; }
                            $room_id = $step[self::NOTIFY_ROOM_ID];
                            $api_token = $step[self::NOTIFY_APITOKEN];
                            $script .= "cat /tmp/jenkins_message_chatwork | chatwork-notify -t {$api_token} -r {$room_id}".PHP_EOL;
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

    public static function createLinks($i,$name,$container) {
        return <<< EOM
docker run -d --name $name $container && \
export LINK_$i="--link $name:$name"
EOM;
    }

    public static function createBuildLoader($image) {
        return <<< EOM
#!/bin/bash -le
echo -e "\e[32m
#######################################
# DockerStart
#######################################
\e[m"

chmod -R 777 \$WORKSPACE/build.sh && \
chmod -R 777 \$WORKSPACE/step.sh && \
chmod -R 777 \$WORKSPACE/notify.sh && \
chmod -R 777 \$WORKSPACE/link.sh
DOCKER_RESULT=0
source ./link.sh

docker run --rm -v \$WORKSPACE:/home/worker/workspace -w /home/worker/workspace \$LINK_1 \$LINK_2 \$LINK_3 -u worker -t $image /bin/bash -l step.sh || DOCKER_RESULT=$?
docker rm -f $(docker ps -a -q) || true

source \$WORKSPACE/notify.sh

rm -f \$WORKSPACE/step.sh && \
rm -f \$WORKSPACE/notify.sh && \
rm -f \$WORKSPACE/build.sh && \
rm -f \$WORKSPACE/link.sh && \
rm -rf /tmp/jenkins_message*
exit \$DOCKER_RESULT
EOM;
    }

    public static function createStep($i,$count,$title,$script) {
        $i++;
        $step = sprintf("%d/%d",$i,$count);
        return <<< EOM
echo -e "\e[32m
## Step: $step $title
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
    SHORT_RESULT='Passed'
else
    LABEL='<span class="label label-important">FAILED</span>'
    COLOR='red'
    SHORT_RESULT='Failed'
fi

COMMIT_ID=\$(git log -1 --pretty='%h')
COMMIT_AUTHER=\$(git log -1 --pretty='%cn')
COMMIT_COMMENT=\$(git log -1 --pretty='%s')
COMMIT_BRANCH=\$(git name-rev --name-only \$COMMIT_ID)
REMOTE_REPOSITORY=\$(git-parser `git config --get remote.origin.url`)
printf "Build \$REMOTE_REPOSITORY<a href="\$JOB_URL">#\$BUILD_NUMBER</a> (\$COMMIT_BRANCH - \$COMMIT_ID): \$LABEL \n\$COMMIT_AUTHER: \$COMMIT_COMMENT" > /tmp/jenkins_message_normal
printf "Build \$SHORT_RESULT \$REMOTE_REPOSITORY #\$BUILD_NUMBER (\$COMMIT_BRANCH - \$COMMIT_ID): \$COMMIT_AUTHER: \$COMMIT_COMMENT" > /tmp/jenkins_message_short
printf "[info][title]Build \$SHORT_RESULT \$REMOTE_REPOSITORY[/title] #\$BUILD_NUMBER (\$COMMIT_BRANCH - \$COMMIT_ID): \$COMMIT_AUTHER: \$COMMIT_COMMENT[/info]" > /tmp/jenkins_message_chatwork
printf "[info][title]Build \$SHORT_RESULT \$REMOTE_REPOSITORY[/title] #\$BUILD_NUMBER (\$COMMIT_BRANCH - \$COMMIT_ID) \n\$COMMIT_AUTHER: \n\$COMMIT_COMMENT[/info]" > /tmp/chatwork_message_log

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
# ScriptStart $count Steps
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
