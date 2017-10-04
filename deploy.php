<?php

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
        $data = $this->_getParsedData();

        if ($data['ref'] != 'refs/heads/' . $this->_settings['branch']) {
            $this->_output('Skip. Branch not match. Push was to ref "' . $data['ref'] . '". Need branch "' . $this->_settings['branch'] . '".');
            return;
        }

        if ($this->_settings['dir']) {
            $result = shell_exec('cd ' . $this->_settings['dir']);
            $this->_output($result);
        }

        $result = shell_exec('ls');
        $this->_output($result);
        return;

        $result = shell_exec('git reset --hard HEAD && git pull');
        $this->_output($result);
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
        $this->_log($message);
    }

    protected function _log($message)
    {
        file_put_contents('deploy.log', date(DATE_ISO8601) . ': ' . $message . PHP_EOL, FILE_APPEND);
    }
}

$settings = array(
    'branch' => 'master',
    'dir' => '',
);

$deploy = new Git_Deploy();
$deploy->run();