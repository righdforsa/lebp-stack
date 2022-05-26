<?php declare(strict_types=1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/sanitize_input.php");

# handle request
$input = json_decode(file_get_contents('php://input'), true);
if(! $input) {
    print "expecting json, unable decode input\n";
    syslog(LOG_WARNING, "expecting json, unable decode input");
    http_response_code(400);
    return;
}

# ensure command was provided
if( ! isset($input['command'])){ 
    print "this is an api, please send your command\n";
    syslog(LOG_WARNING, "expecting json, unable decode input for " . $input);
    http_response_code(400);
    return;
} else {
    $safe_request['command'] = sanitize_input('simple_string', $input['command']);
    syslog(LOG_INFO, "got command: " . $safe_request['command']);
}

# see if we have the command
include_once($_SERVER['DOCUMENT_ROOT'] . "/Command.php");
try {
    include_once($_SERVER['DOCUMENT_ROOT'] . "/api_commands/" . $safe_request['command'] . ".php");
    $command = new $safe_request['command']();
} catch (Throwable $t) {
    print("unrecognized command\n");
    syslog(LOG_WARNING, "Unknown api command " . $safe_request['command'] . " error: " . $t->getMessage());
    http_response_code(404);
    return;
}

syslog(LOG_INFO, "calling run_command(): " . $safe_request['command'] . " with input " . json_encode($input));
run_command($command, $input);

function run_command(Command $command, array $input_arr) {
    syslog(LOG_INFO, "run_command(): $command->name with input " . json_encode($input_arr));
    $command->run($input_arr);
    syslog(LOG_WARNING, "run_command(): command $command->name run() function did not exit, continuing");

    # if we made it here, then something is broken
    syslog(LOG_WARNING, "Uknown command name: " . $command->name);
    print("unrecognized command\n");
    http_response_code(400);
}
