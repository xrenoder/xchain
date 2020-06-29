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

        $block = Block::create($app, $firstMnodeAddress,  null, 0);

        $block
            ->addTransaction(
                RegisterPublicKeyTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setTargetAddress($firstMnodeAddress)
                    ->setAuthorPubKeyAddress($firstMnodeAddress)
                    ->createRaw()
            )
            ->addTransaction(
                RegisterNodeHostTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setHost(Host::create($app, Host::TRANSPORT_TCP, FIRST_M_NODE_HOST))
                    ->setNodeName(FIRST_M_NODE_NAME)
                    ->createRaw()
            )
            ->createRaw()
        ;


    }
}