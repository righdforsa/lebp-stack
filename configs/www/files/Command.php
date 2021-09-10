<?php

abstract class Command {
    public function processArgs() {
        syslog(LOG_INFO, "made it into the class");
    }
}
