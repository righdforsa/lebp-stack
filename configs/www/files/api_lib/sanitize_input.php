<?php declare(strict_types=1);

function sanitize_input(string $datatype, $value, bool $ok_empty = false) {
    syslog(LOG_INFO, "debug: testing datatype match for '$datatype'");
    if($value === "") {
        if($ok_empty == true) {
            return $value;
        } else {
            syslog(LOG_WARNING, "Argument error: command parameter with type '$datatype' contains empty value");
            print("Argument error: empty command parameter failed '$datatype' test\n");
            http_response_code(400);
            exit;
        }
    }

    if( $datatype == 'boolean' ) {
        if (in_array($value, ['t', 'T', 'true', 'True', 'TRUE'])) {
            return true;
        } else if (in_array($value, ['f', 'F', 'false', 'False', 'FALSE'])) {
            return false;
        } else {
            $safe_value = false;
        }
    } else if ( $datatype == 'float' ) {
        $safe_value = is_numeric($value) ? $value : false;
    } else if ( $datatype == 'number_string' ) {
        $safe_value = preg_match('/^[0-9]+$/', strval($value)) == 1 ? strval($value) : false;
    } else if ( $datatype == 'simple_string' ) {
        $safe_value = preg_match('/^[A-Za-z0-9]+$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'text_string' ) {
        $safe_value = preg_match('/^[A-Za-z0-9\ \-]+$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'json' ) {
        $safe_value = preg_match('/^\{[A-Za-z0-9\"\'\,\:\=\{\}\-\ ]+\}$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'email' ) {
        $value = strtolower($value);
        $safe_value = preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'javascript_base64_file' ) {
        $safe_value = preg_match('/^[0-9A-Za-z\:\;\,\+\=\/\\\]+$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'encrypted' ) {
        $safe_value = preg_match('/^[0-9A-Za-z\:\;\.\,\+\-\=\_\/\\\]+$/', $value) == 1 ? $value : false;
    } else if ( $datatype == 'wild_west' ) {
        $safe_value = preg_match('/^.+$/', $value) == 1 ? $value : false;
    } else {
        syslog(LOG_ERR, "Server error: command parameter processing failed, unknown datatype '$datatype'");
        print("Server error: command parameter processing failed, unknown datatype\n");
        http_response_code(501);
        exit;
    }

    if ($safe_value === false) {
        syslog(LOG_WARNING, "Parse error: command parameter with type '$datatype' contains invalid value: '$value'");
        print("Parse error: command parameter failed '$datatype' test\n");
        http_response_code(400);
        exit;
    } else {
        return( $safe_value );
    }
}
