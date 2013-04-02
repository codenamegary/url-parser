<?php

$loader = __DIR__ . '/../vendor/autoload.php';
 
if (!$loader = @include($loader))
{
	echo "Failed to include autoload.php.";
	exit(1);
}
