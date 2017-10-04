<?php

$data = file_get_contents("php://input");

file_put_contents('log.log', date(DATE_ISO8601) . ': ' . $data . PHP_EOL);

var_dump($data);