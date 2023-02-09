<?php declare(strict_types=1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/sanitize_input.php");

# generate unique request ID
$request_id = uniqid();

# handle request
$input_arr = json_decode(file_get_contents('php://input'), true);
if(! $input_arr) {
    print "expecting json, unable decode input\n";
    syslog(LOG_WARNING, "$request_id: expecting json, unable decode input");
    http_response_code(400);
    return;
}

# ensure command was provided
if( ! isset($input_arr['command'])){ 
    print "this is an api, please send your command\n";
    syslog(LOG_WARNING, "$request_id: expecting json, unable decode input for " . $input_arr);
    http_response_code(400);
    return;
} else {
    $safe_request['command'] = sanitize_input('simple_string', $input_arr['command']);
    syslog(LOG_INFO, "$request_id: got command: " . $safe_request['command']);
}

# see if we have the command
include_once($_SERVER['DOCUMENT_ROOT'] . "/Command.php");
try {
    include_once($_SERVER['DOCUMENT_ROOT'] . "/api_commands/" . $safe_request['command'] . ".php");
    $command = new $safe_request['command']($request_id, $input_arr);
} catch (Throwable $t) {
    print("unrecognized command\n");
    syslog(LOG_WARNING, "$request_id: Unknown api command " . $safe_request['command'] . " error: " . $t->getMessage());
    http_response_code(404);
    return;
}

syslog(LOG_INFO, "$request_id: calling run_command(): " . $safe_request['command'] . " with input " . json_encode($input_arr));
$command->run();

# if we made it here, then something is broken
syslog(LOG_WARNING, "$request_id: run_command(): command $command->name run() function did not exit, continuing");
print("unknown system error\n");
http_response_code(500);
die();
