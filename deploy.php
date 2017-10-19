<?php

$settings = array(
    'branch' => 'master',
    'dir' => '',
    'log_request' => false,
    'log' => true,
    'ssh_key' => 'qwe_rsa'
);

class Git_Deploy
{
    protected $_settings = array(
        'branch' => 'master'
    );

    public function __construct($settings = array())
    {
        $this->_settings = array_merge($this->_settings, $settings);
    }

    public function run()
    {
        if (!$this->_validateDisabledFunctions()) {
            $this->_output('shell_exec() has been disabled for security reasons');
            return;
        }
        $data = $this->_getParsedData();

        if (empty($data) || empty($data['ref'])) {
            $this->_output('Wrong input data.');
            return;
        }

        if ($data['ref'] != 'refs/heads/' . $this->_settings['branch']) {
            $this->_output('Skip. Branch not match. Push was to ref "' . $data['ref'] . '". Need branch "' . $this->_settings['branch'] . '".');
            return;
        }

        if ($this->_settings['dir']) {
            $result = $this->_shell_exec('cd ' . $this->_settings['dir']);
            if ($result) {
                $this->_output('Can\'t change directory.');
                return;
            }
        }

        $this->_gitExec('reset --hard HEAD');

        if (!$this->_checkout($this->_settings['branch'])) {
            $this->_output('Can\'t checkout to branch \'' . $this->_settings['branch'] . '\'');
            return;
        }

        $this->_gitExec('pull');
    }

    protected function _checkout($branch)
    {
        $i = 0;
        do {
            $result = $this->_gitExec('branch');

            if (!preg_match('/\* ' . $branch . '/', $result)) {
                if ($i > 0) {
                    return false;
                }
                $this->_gitExec('checkout ' . $branch);
            } else {
                return true;
            }
        } while ($i++ < 1);

        return false;
    }

    protected function _gitExec($command, $output = true)
    {
        if ($this->_settings['ssh_key']) {
            $command = "ssh-agent bash -c 'ssh-add \"{$this->_settings['ssh_key']}\"; git $command'";
        } else {
            $command = 'git ' . $command;
        }

        return $this->_shell_exec($command, $output);
    }

    protected function _shell_exec($command, $output = true)
    {
        $this->_output($command);
        $result = shell_exec($command);
        if ($output) {
            $this->_output($result);
        }
        return $result;
    }

    protected function _validateDisabledFunctions()
    {
        $functions = ini_get('disable_functions');
        $functions = explode(',', $functions);
        foreach ($functions as $key => $function) {
            if ($function == 'shell_exec') {
                return false;
            }
        }
        return true;
    }

    protected function _getParsedData()
    {
        $data = file_get_contents("php://input");

        if ($this->_settings['log_request']) {
            $this->_log($data);
        }

        return json_decode(utf8_decode($data), true);
    }

    protected function _output($message)
    {
        echo $message . PHP_EOL;
        if ($this->_settings['log']) {
            $this->_log($message);
        }
    }

    protected function _log($message)
    {
        file_put_contents('deploy.log', date(DATE_ISO8601) . ': ' . $message . PHP_EOL, FILE_APPEND);
    }
}

$deploy = new Git_Deploy($settings);
$deploy->run();