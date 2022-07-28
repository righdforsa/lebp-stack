<?php

function newJWT($username) {
	// TODO: Actually make this do something
	syslog(LOG_INFO, "newJWT(): issuing new JWT for $username");
        $jwt = hash('sha256', time() . $username . "asdfasdf");
        return $jwt;
}

function validateJWT($username) {
        // TODO: Actually make this do something
	syslog(LOG_INFO, "validateJWT(): evaluating JWT for $username");
        return;
}
