<?php
session_start();

require_once('orion/orion.php');

try {
    $orion = new Orion('orion/');
    $orion->configure('main');
    $orion->run();
}
catch(OrionException $e)
{
    echo $e->__toString();
    exit(1);
}

echo "\nMemory usage: ". memory_get_usage(true);
?>