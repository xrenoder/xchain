#!/usr/local/bin/php
<?php
// TODO реализовать создание зашифрованого ключа
require_once 'local.inc';

$debugMode = 0;

$app = new App('Create XChain-Address');
// get logger-object
Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

$addr = Address::createNew($app, WALLET_PATH);

echo "Your XChain-address is: \n" . $addr->getAddressHuman() . "\nCopy it and paste to configuration file './local.inc'\n\n";
echo "Private key of this addres You can found in file\n" . WALLET_PATH . $addr->getAddressHuman() . "\nPlease, save backup of this file without any changes to not lost your coins'./local.inc'\n";

exit(0);


