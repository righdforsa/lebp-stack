<?php

require_once("Bedrock-PHP/vendor/autoload.php");
print("successfully loaded Bedrock PHP vendor package autoload\n");

require_once("/var/www/html/Bedrock-PHP/src/Client.php");
print("successfully loaded Bedrock lib\n");

use Expensify\Bedrock;
use Expensify\Bedrock\DB\Response;
use Expensify\Bedrock\Exceptions\BedrockError;
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

$db = new Bedrock\DB($bedrock);

$result = $db->query("SELECT json_array(1, 2) as foobar;", true);
print("successfully queried bedrock\n");
print_r($result);


?>
