<?php

require_once 'Events.php';


$username = $_SERVER['argv'][1];

$obj = new Events($username);
Events::formatResponse($obj->getEvents());
