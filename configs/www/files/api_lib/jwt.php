<?php

function newJWT($username) {
	// TODO: Actually make this do something
        $jwt = hash('sha256', time() . $username . "asdfasdf");
}

function validateJWT($username) {
        // TODO: Actually make this do something
        return;
}
