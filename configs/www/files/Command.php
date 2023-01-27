<?php

abstract class Command {
    public string $request_id;

    protected abstract function run($input_arr);

    public function processArgs() {
        syslog(LOG_INFO, $this->request_id . " made it into the class");
    }

    protected function log($priority, string $message) {
        syslog($priority, $this->request_id . ": " . $message);
    }
}
