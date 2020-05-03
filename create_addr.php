#!/usr/local/bin/php
<?php
require_once 'local.inc';

$debugMode = 0;

$app = new App('Create XChain-Address');
// get logger-object
Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

$addr = Address::createNew($app, WALLET_PATH);

echo "Your XChain-address is: \n" . $addr->getAddressBase16() . "\nCopy it and paste to configuration file './local.inc'\n\n";
echo "Private key of this addres You can found in file\n" . WALLET_PATH . $addr->getAddressBase16() . "\nPlease, save backup of this file without any changes to not lost your coins'./local.inc'\n";

exit(0);


