<?php

file_put_contents('log.log', print_r($HTTP_RAW_POST_DATA, true));

var_dump($HTTP_RAW_POST_DATA);