<?php

chdir(dirname(__DIR__));

ini_set("display_errors", 1);

use Application\Application;

$app = new Application();

echo "<pre>".print_r($app->getImage(), true)."</pre>";
