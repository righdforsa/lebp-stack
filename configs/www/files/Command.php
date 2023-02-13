<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/sanitize_input.php");

abstract class Command {
    public string $request_id;

    protected array $input_arr;

    public function __construct(string $request_id, array $input_arr) {
        $this->request_id = $request_id;
        $this->input_arr = $input_arr;
    }

    protected abstract function run();

    public function processArgs(array $required_params) {
        syslog(LOG_INFO, $this->request_id . " processArgs()");

        $die = false;
        foreach ($required_params as $paramName => $validator) {
            syslog(LOG_INFO, "debug: testing parameter $paramName was provided in input");
            if (!isset($this->input_arr[$paramName])) {
                syslog(LOG_WARNING, "missing required parameter $paramName");
                print("missing required parameter $paramName\n");
                $die = true;
                http_response_code(401);
            }
        }
        if ($die) {
            exit;
        }

        $die = false;
	$safe_request = [];
        foreach ($required_params as $paramName => $validator) {
            try {
                syslog(LOG_INFO, "debug: testing '$paramName' against validator '$validator'");
                $safe_request[$paramName] = sanitize_input($validator, $this->input_arr[$paramName]);
            } catch (Throwable $t) {
                syslog(LOG_WARNING, "failed to sanitize required input parameter: $paramName, error " . $t);
	        print("failed to sanitize required input parameter: $paramName\n");
                $die = true;
                http_response_code(401);
            }
        }
        if ($die) {
            exit;
        }

        syslog(LOG_INFO, "finished validating required parameters\n");
	return $safe_request;
    }

    protected function log($priority, string $message) {
        syslog($priority, $this->request_id . ": " . $message);
    }
}
