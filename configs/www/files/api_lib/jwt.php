<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function newJWT($username) {
        // Your passphrase
        require 'passphrase.php';

        // Your private key file with passphrase
        $privateKeyFile = '/private_key.pem';

        // Create a private key of type "resource"
        $privateKey = openssl_pkey_get_private(
            file_get_contents($privateKeyFile),
            $passphrase
        );

        $payload = [
            'username' => $username
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');



        // TODO: Actually make this do something
        // syslog(LOG_INFO, "newJWT(): issuing new JWT for $username");
        //$jwt = hash('sha256', time() . $username . "asdfasdf");
        return $jwt;
}

function validateJWT($username,$jwt) {
        // Your passphrase
        require 'passphrase.php';

        // Your private key file with passphrase
        // Can be generated with "ssh-keygen -t rsa -m pem"
        $privateKeyFile = '/private_key.pem';

        // Create a private key of type "resource"
        $privateKey = openssl_pkey_get_private(
            file_get_contents($privateKeyFile),
            $passphrase
        );
        // Get public key from the private key, or pull from from a file.
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        try{
            $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
            $decoded = (array) $decoded;
            if(isset($decoded['username']) && $decoded['username'] == $username){
                    return $decoded;
            }
            return false;
        }catch(\Exception $e){
            return false;
        }
}
