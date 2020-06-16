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

        $firstMnodeAddress = Address::createFromPrivateHex($app, FIRST_M_NODE_KEY);

        $block = Block::createNew($app, $firstMnodeAddress,  null, 0);

        $block
            ->addTransaction(
                RegisterPublicKeyTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setTargetAddrBin($firstMnodeAddress->getAddressBin())
                    ->setPublicKey($firstMnodeAddress->getPublicKeyBin())
            )
            ->addTransaction(
                RegisterNodeHostTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setHost(FIRST_M_NODE_HOST)
                    ->setNodeName(FIRST_M_NODE_NAME)
            )
        ;


    }
}