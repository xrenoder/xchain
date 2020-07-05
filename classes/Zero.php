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

        $chainName = MAIN_CHAIN_NAME;
        $chainLastSign = '';
        $chainTime = time();
        $zero = 0;
        $signerNode = aNode::spawn($app, NodeClassEnum::MASTER);

        $chain =
            ChainByIdDbRow::create($app, 0)
                ->setChainName($chainName)
                ->setSignerNode($signerNode)
                ->setLastPreparedBlockId($zero)
                ->setLastPreparedBlockTime($chainTime)
                ->setLastPreparedBlockSignature($chainLastSign)
                ->setLastKnownBlockId($zero)
                ->save()
        ;

        $firstMnodeAddress = Address::createFromPrivateHex($app, FIRST_M_NODE_KEY);

        $block = Block::createNew($app, $chain, $firstMnodeAddress);

        $block
            ->addTransaction(
                RegisterPublicKeyTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setTargetAddress($firstMnodeAddress)
                    ->setAuthorPubKeyAddress($firstMnodeAddress)
            )

            ->addTransaction(
                RegisterNodeHostTransaction::create($app)
                    ->setAuthorAddress($firstMnodeAddress)
                    ->setHost(Host::create($app, Host::TRANSPORT_TCP, FIRST_M_NODE_HOST))
                    ->setNodeName(FIRST_M_NODE_NAME)
            )

            ->createRaw()
        ;





        $chain->save();
    }
}