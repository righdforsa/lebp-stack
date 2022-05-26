<?php
require_once("Bedrock-PHP/vendor/autoload.php");
require_once("/var/www/html/Bedrock-PHP/src/Client.php");

use Expensify\Bedrock;
use Expensify\Bedrock\DB\Response;
use Expensify\Bedrock\Exceptions\BedrockError;

function getInstance() {
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
    syslog(LOG_INFO, "successfully created bedrock instance");

    return new Bedrock\DB($bedrock);
}

