<?php

require_once("Bedrock-PHP/vendor/autoload.php");
print("successfully loaded Bedrock PHP vendor package autoload\n");

require_once("/var/www/html/Bedrock-PHP/src/Client.php");
print("successfully loaded Bedrock lib\n");

use Expensify\Bedrock;
$bedrock = Bedrock\Client::getInstance([
            'clusterName' => 'bedrock',
            'mainHostConfigs' => ['localhost' => ['blacklistedUntil' => 0, 'port' => 8888]],
            'failoverHostConfigs' => ['localhost' => ['blacklistedUntil' => 0, 'port' => 8888]],
            'connectionTimeout' => 1,
            'readTimeout' => 300,
            'bedrockTimeout' => 290,
            'logger' => new Psr\Log\NullLogger(),
            'stats' => new Bedrock\Stats\NullStats(),
            'writeConsistency' => 'ASYNC',
            'maxBlackListTimeout' => 1,
            'commandPriority' => null,
            'logParam' => null,
           ]);
print("successfully created bedrock instance\n");


$result = $bedrock->call("query: SELECT 1 AS foo, 2 AS bar;");
print("successfully queried bedrock\n");
print_r($result);


?>
