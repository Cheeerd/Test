<?php

file_put_contents('log.log', date(DATE_ISO8601) . ': ' . print_r($HTTP_RAW_POST_DATA, true) . PHP_EOL);

var_dump($HTTP_RAW_POST_DATA);