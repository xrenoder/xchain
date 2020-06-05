<?php


class Zero extends aBase
{
    public function getApp() : App {return $this->getLocator();}

    public static function create(App $app) : self
    {
        return new static($app);
    }

    public function run()
    {
        $app = $this->getApp();

        $firstMNodeAddress = Address::createFromPrivateHex($app, FIRST_M_NODE_KEY);

        $dbTransaction = $this->dbTrans();

        $registerPublicKeyTransaction
            = RegisterPublicKeyTransaction::create($app)
                ->setAuthorAddress($firstMNodeAddress)
                ->setTargetAddrBin($firstMNodeAddress->getAddressBin())
                ->setData($firstMNodeAddress->getPublicKeyBin());

        $registerNodeHostTransaction = RegisterNodeHostTransaction::create($app);
    }
}