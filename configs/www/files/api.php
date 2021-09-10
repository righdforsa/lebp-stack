<?php declare(strict_types=1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/sanitize_input.php");

# handle request
$input = json_decode(file_get_contents('php://input'), true);
if(! $input) {
    print "expecting json, unable decode input\n";
    syslog(LOG_WARNING, "expecting json, unable decode input");
    http_response_code(401);
    return;
}

# ensure command was provided
if( ! isset($input['command'])){ 
    print "this is an api, please send your command\n";
    syslog(LOG_WARNING, "expecting json, unable decode input for " . $input);
    http_response_code(401);
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
    http_response_code(401);
    return;
}

syslog(LOG_INFO, "calling run_command(): " . $safe_request['command'] . " with input " . json_encode($input));
run_command($command, $input);

function run_command(Command $command, array $input_arr) {
    syslog(LOG_INFO, "run_command(): $command->name with input " . json_encode($input_arr));
    $command->run($input_arr);
    syslog(LOG_WARNING, "run_command(): command $command->name run() function did not exit, continuing");

    $safe_request['command'] = $command->name;

    if( isset($input_arr['mock'] )) {
        $safe_request['mock'] = sanitize_input('boolean', $input_arr['mock']);
        syslog(LOG_INFO, "received mock request setting: " . $safe_request['mock']);
    } else {
	$safe_request['mock'] = false;
    }

    if( isset($input_arr['requestAttempt'] )) {
        $safe_request['requestAttempt'] = sanitize_input('number_string', $input_arr['requestAttempt']);
        syslog(LOG_INFO, "received request attempt setting: " . $safe_request['requestAttempt']);
    } else {
	$safe_request['requestAttempt'] = 0;
    }

    # check command-specific inputs
    if( $command->name == 'getHelp' ) {
        $required_params = [
            'ticketLatitude',
            'ticketLongitude',
            'urgency',
            'helpType',
        ];
		$die = false;
        foreach ($required_params as $paramName) {
            if ( ! isset($input_arr[$paramName])) {
                print("missing required parameter $paramName\n");
				$die = true;
                http_response_code(401);
            }
		}
		if ($die) {
			return;
		}

        $safe_request['ticketLatitude'] = sanitize_input('float', $input_arr['ticketLatitude']);
        $safe_request['ticketLongitude'] = sanitize_input('float', $input_arr['ticketLongitude']);
		$safe_request['urgency'] = sanitize_input('simple_string', $input_arr['urgency']);
		$safe_request['helpType'] = sanitize_input('simple_string', $input_arr['helpType']);
        syslog(LOG_INFO, "finished collecting parameters\n");

		// start the response object
		$response_json = array();

		// get the db
		require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/bedrock.php");
		$db = getInstance();

		// construct the ticket details to save in the db
		$ticket_details['ticketStatus'] = 'new';
		$ticket_details['ticketLatitude'] = $safe_request['ticketLatitude'];
		$ticket_details['ticketLongitude'] = $safe_request['ticketLongitude'];
		$ticket_details['urgency'] = $safe_request['urgency'];
		$ticket_details['helpType'] = $safe_request['helpType'];

		// insert the new ticket and save new ticketID
		$db_response = $db->query('INSERT INTO tickets SELECT MAX(ticketID) + 1, \'' . json_encode($ticket_details) . '\', datetime(\'now\') FROM tickets');
		syslog(LOG_INFO, "insert query response: " . $db_response . "\n");
		$response_json['ticketID'] = $db_response->getLastInsertRowID();

		if($safe_request['mock'] == true) {
			//$response_json['ticketID'] = 12345;
		}

        // send the response
        print(json_encode($response_json));
        http_response_code(200);
		return;
    } else if( $command->name == 'getTicketStatus' ) {
        $required_params = [
            'ticketID',
        ];
		$die = false;
        foreach ($required_params as $paramName) {
            if ( ! isset($input_arr[$paramName])) {
                print("missing required parameter $paramName\n");
				$die = true;
                http_response_code(401);
            }
		}
		if ($die) {
			return;
		}

		$safe_request['ticketID'] = sanitize_input('number_string', $input_arr['ticketID']);
        syslog(LOG_INFO, "finished collecting parameters");

		// start the response
		$response_json = array();

		// get the db
		require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/bedrock.php");
		$db = getInstance();

		$db_response = $db->query("select * from tickets where ticketID=" . $safe_request['ticketID']); 
		syslog(LOG_INFO, "DB query response: " . $db_response);

		if($safe_request['mock'] != true) {
			// use array_merge to get the db_response to act the same as a hand-built response_json dict
			$response_json = array_merge($response_json, $db_response->getRows(true)[0]['details']);
		} else if($safe_request['mock'] == true) {
			$response_json['ticketLatitude'] = $db_response->getRows(true)[0]['details']['ticketLatitude'];
			$response_json['ticketLongitude'] = $db_response->getRows(true)[0]['details']['ticketLongitude'];
			$latDeltaStartingDistance = 0.07;
			$longDeltaStartingDistance = 0.07;
			$latDelta = (float) $latDeltaStartingDistance - ($safe_request['requestAttempt'] * 0.01);
			$longDelta = (float) $latDeltaStartingDistance - ($safe_request['requestAttempt'] * 0.01);
			$agentLatitude = round($response_json['ticketLatitude'] + $latDelta, 4);
			$agentLongitude = round($response_json['ticketLongitude'] + $longDelta, 4);
            syslog(LOG_INFO, "ticketLatitude: $ticketLatitude ticketLongitude: $ticketLongitude agentLatitude: $agentLatitude agentLongitude: $agentLongitude");
			$response_json['agentLatitude'] = $agentLatitude;
			$response_json['agentLongitude'] = $agentLongitude;
			if($safe_request['requestAttempt'] <= 1) {
				$response_json['ticketStatus'] = 'new';
			} else if($safe_request['requestAttempt'] >= 2 && $safe_request['requestAttempt'] < 6) {
				$response_json['ticketStatus'] = 'assigned';
			} else if($safe_request['requestAttempt'] >= 6) {
				$response_json['ticketStatus'] = 'arrived';
            }
		}
	
        // send the response
        print(json_encode($response_json));
        syslog(LOG_INFO, "sending api response:" . json_encode($response_json));
        http_response_code(200);
		return;
    } else if( $command->name == 'getAgentTicket' ) {
        $required_params = [
            'agentLatitude',
            'agentLongitude',
        ];
		$die = false;
        foreach ($required_params as $paramName) {
            if ( ! isset($input_arr[$paramName])) {
                print("missing required parameter $paramName\n");
				$die = true;
                http_response_code(401);
            }
		}
		if ($die) {
			return;
		}
        $safe_request['agentLatitude'] = sanitize_input('float', $input_arr['agentLatitude']);
        $safe_request['agentLongitude'] = sanitize_input('float', $input_arr['agentLongitude']);
        syslog(LOG_INFO, "finished collecting parameters" . $safe_request);

		// start the response
		$response_json = array();

		// get the db
		require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/bedrock.php");
		$db = getInstance();

		$latitude_range = 0.02;
		$longitude_range = 0.02;
		syslog(LOG_INFO, "SELECT * FROM tickets WHERE" .
			" json_extract(details, '$.ticketStatus') = 'new'" .
			" AND cast(json_extract(details, '$.ticketLatitude') AS real) > " . (float) ($safe_request['agentLatitude'] - $latitude_range) .
			" AND cast(json_extract(details, '$.ticketLatitude') AS real) < " . (float) ($safe_request['agentLatitude'] + $latitude_range) .
			" AND cast(json_extract(details, '$.ticketLongitude') AS real) > " . (float) ($safe_request['agentLongitude'] - $longitude_range) .
			" AND cast(json_extract(details, '$.ticketLongitude') AS real) < " . (float) ($safe_request['agentLongitude'] + $longitude_range) .
			" LIMIT 10"
		);
		$db_response = $db->query("SELECT * FROM tickets WHERE" .
			" json_extract(details, '$.ticketStatus') = 'new'" .
			" AND cast(json_extract(details, '$.ticketLatitude') AS real) > " . (float) ($safe_request['agentLatitude'] - $latitude_range) .
			" AND cast(json_extract(details, '$.ticketLatitude') AS real) < " . (float) ($safe_request['agentLatitude'] + $latitude_range) .
			" AND cast(json_extract(details, '$.ticketLongitude') AS real) > " . (float) ($safe_request['agentLongitude'] - $longitude_range) .
			" AND cast(json_extract(details, '$.ticketLongitude') AS real) < " . (float) ($safe_request['agentLongitude'] + $longitude_range) .
			" LIMIT 10"
		);
		syslog(LOG_INFO, "DB query response: " . $db_response);
		$db_rows[] = $db_response->getRows(true);
		syslog(LOG_INFO, "DB query returned " . sizeof($db_rows) . " rows");
		syslog(LOG_INFO, "debug: db_rows: " . json_encode($db_rows));

		//TODO: sort tickets based on time, then proximity
		if($safe_request['mock'] == true) {
			if ($safe_request['requestAttempt'] < 3) {
				$response_json['ticket'] = null;
            } else {
				syslog(LOG_INFO, "sending agent first ticket row: " . $db_rows[0][0]);
				$response_json['ticket'] = $db_rows[0][0];
			}
		} else {
			syslog(LOG_INFO, "sending agent first ticket row: " . $db_rows[0][0]);
			$response_json['ticket'] = $db_rows[0][0];
		}
		$response_json['ticketLatitude'] = $db_response->getRows(true)[0]['details']['ticketLatitude'];
		$response_json['ticketLongitude'] = $db_response->getRows(true)[0]['details']['ticketLongitude'];
		//$response_json['longitude'] = $db_response->getRows(true)[0]['details']['longitude'];
		//$response_json['ticketStatus'] = $db_response->getRows(true)[0]['details']['ticketStatus'];

        // send the response
        print(json_encode($response_json));
        syslog(LOG_INFO, "sending api response:" . json_encode($response_json));
        http_response_code(200);
		return;
    } else if( $command->name == 'updateTicketAssignment' ) {
        $required_params = [
            'ticketID',
        ];
	    $die = false;
        foreach ($required_params as $paramName) {
            if ( ! isset($input_arr[$paramName])) {
                print("missing required parameter $paramName\n");
		        $die = true;
                http_response_code(401);
            }
	    }
	    if ($die) {
	        return;
	    }
        $safe_request['ticketID'] = sanitize_input('number_string', $input_arr['ticketID']);

	    // gather optional parameters
	    if(isset($input_arr['agentLatitude'])){
            $safe_request['agentLatitude'] = sanitize_input('float', $input_arr['agentLatitude']);
	    }
	    if(isset($input_arr['agentLongitude'])){
            $safe_request['agentLongitude'] = sanitize_input('float', $input_arr['agentLongitude']);
	    }
	    if(isset($input_arr['userLatitude'])){
            $safe_request['userLatitude'] = sanitize_input('float', $input_arr['userLatitude']);
	    }
	    if(isset($input_arr['userLongitude'])){
            $safe_request['userLongitude'] = sanitize_input('float', $input_arr['userLongitude']);
	    }
        syslog(LOG_INFO, "finished collecting parameters" . $safe_request);

	    // start the response
	    $response_json = array();

        $json_patch = '"ticketStatus":"assigned",';

        // optional update params
	    if(isset($safe_request['agentLatitude'])){
	        $json_patch .= '"agentLatitude":"' . $safe_request['agentLatitude'] . '",';
	    }
	    if(isset($safe_request['agentLongitude'])){
	        $json_patch .= '"agentLongitude":"' . $safe_request['agentLongitude'] . '",';
	    }
	    if(isset($safe_request['userLatitude'])){
	        $json_patch .= '"userLatitude":"' . $safe_request['userLatitude'] . '",';
	    }
	    if(isset($safe_request['userLongitude'])){
	        $json_patch .= '"userLongitude":"' . $safe_request['userLongitude'] . '",';
	    }
	    $json_patch = rtrim($json_patch, ',');

	    // get the db
	    require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/bedrock.php");
	    $db = getInstance();
	    $db_response = $db->query("SELECT * FROM tickets WHERE json_extract(details, '$.ticketStatus') = 'new' AND ticketID = " . $safe_request['ticketID']);
	    //print("debug: " . $db_response . "\n");
	    if(sizeof($db_response->getRows()) == 1) {
	        syslog(LOG_INFO, "sending query: " . "UPDATE tickets SET details=json_patch(details, '{" . $json_patch . "}') WHERE ticketID = " . $safe_request['ticketID']);
	        $db_response = $db->query("UPDATE tickets SET details=json_patch(details, '{" . $json_patch . "}') WHERE ticketID = " . $safe_request['ticketID']);
	        //$db_response = $db->query("UPDATE tickets SET details=json_set(details, '$.ticketStatus', 'assigned') WHERE ticketID = " . $safe_request['ticketID']);
	        if($db_response->getCode() != '200'){
                syslog(LOG_ALERT, "unknown db error: " . $db_response);
		        print("internal system error\n");
		        http_response_code(501);
	        }
	        //print("debug: " . $db_response . "\n");
	    } else {
            //TODO: this can happen if the ticket is already assigned, check here
            print("ticket not accessible\n");
            http_response_code(403);
	        return;
	    }

        // send the response
        print(json_encode($response_json));
        syslog(LOG_INFO, "sending api response:" . json_encode($response_json));
        http_response_code(200);
	    return;
    } else if( $command->name == 'updateTicketDetails' ) {
        $required_params = [
            'ticketID',
        ];
		$die = false;
        foreach ($required_params as $paramName) {
            if ( ! isset($input_arr[$paramName])) {
                print("missing required parameter $paramName\n");
				$die = true;
                http_response_code(401);
            }
		}
		if ($die) {
			return;
		}
        $safe_request['ticketID'] = sanitize_input('number_string', $input_arr['ticketID']);

		// gather optional parameters
		if(isset($input_arr['agentLatitude'])){
            $safe_request['agentLatitude'] = sanitize_input('float', $input_arr['agentLatitude']);
		}
		if(isset($input_arr['agentLongitude'])){
            $safe_request['agentLongitude'] = sanitize_input('float', $input_arr['agentLongitude']);
		}
		if(isset($input_arr['userLatitude'])){
            $safe_request['userLatitude'] = sanitize_input('float', $input_arr['userLatitude']);
		}
		if(isset($input_arr['userLongitude'])){
            $safe_request['userLongitude'] = sanitize_input('float', $input_arr['userLongitude']);
		}
		if(isset($input_arr['ticketLatitude'])){
            $safe_request['ticketLatitude'] = sanitize_input('float', $input_arr['ticketLatitude']);
		}
		if(isset($input_arr['ticketLongitude'])){
            $safe_request['ticketLongitude'] = sanitize_input('float', $input_arr['ticketLongitude']);
		}
        syslog(LOG_INFO, "finished collecting parameters" . json_encode($safe_request));

		// start the response
		$response_json = array();

		$json_patch = "";
		if(isset($safe_request['agentLatitude'])){
			$json_patch .= '"agentLatitude":"' . $safe_request['agentLatitude'] . '",';
		}
		if(isset($safe_request['agentLongitude'])){
			$json_patch .= '"agentLongitude":"' . $safe_request['agentLongitude'] . '",';
		}
		if(isset($safe_request['userLatitude'])){
			$json_patch .= '"userLatitude":"' . $safe_request['userLatitude'] . '",';
		}
		if(isset($safe_request['userLongitude'])){
			$json_patch .= '"userLongitude":"' . $safe_request['userLongitude'] . '",';
		}
		if(isset($safe_request['ticketLatitude'])){
			$json_patch .= '"ticketLatitude":"' . $safe_request['ticketLatitude'] . '",';
		}
		if(isset($safe_request['ticketLongitude'])){
			$json_patch .= '"ticketLongitude":"' . $safe_request['ticketLongitude'] . '",';
		}
		$json_patch = rtrim($json_patch, ',');

		// get the db
		require_once($_SERVER['DOCUMENT_ROOT'] . "/api_lib/bedrock.php");
		$db = getInstance();
		$db_response = $db->query("SELECT * FROM tickets WHERE ticketID = " . $safe_request['ticketID']);
		syslog(LOG_INFO, "debug: db_response: " . $db_response);
		if(sizeof($db_response->getRows()) == 1) {
			if($json_patch !== ""){
				syslog(LOG_INFO, "UPDATE tickets SET details=json_patch(details, '{" . $json_patch . "}') WHERE ticketID = " . $safe_request['ticketID']);
				$db_response = $db->query("UPDATE tickets SET details=json_patch(details, '{" . $json_patch . "}') WHERE ticketID = " . $safe_request['ticketID']);
				if($db_response->getCode() != '200'){
                    syslog(LOG_ALERT, "unknown db error: " . $db_response);
					print("internal system error\n");
					http_response_code(501);
				}
				//print("debug: " . $db_response . "\n");
			} else {
				syslog(LOG_INFO, "json_patch empty, nothing to update: $json_patch");
            }
		} else {
            print("ticket not accessible\n");
            http_response_code(403);
			return;
		}

        // send the response
        print(json_encode($response_json));
        syslog(LOG_INFO, "sending api response: " . json_encode($response_json));
        http_response_code(200);
	    return;
    } else {
        syslog(LOG_INFO, "Uknown command name: " . $command->name);
        print("unrecognized command\n");
        http_response_code(400);
    }
}
