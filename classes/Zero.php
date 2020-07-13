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
        $chainId = 0;
        $chainLastSign = '';
        $zero = 0;
        $signerNodeType = NodeEnum::MASTER;

        $firstMnodeAddress = $this->getApp()->getMyAddress();
        $signerAddress = $firstMnodeAddress;

// create main chain
        ChainByIdDbRow::create($app, $chainId)
            ->setChainName($chainName)
            ->setSignerNodeType($signerNodeType)
            ->setLastPreparedBlockId($zero)
            ->setLastPreparedBlockTime($zero)
            ->setLastPreparedBlockSignature($chainLastSign)
            ->setLastKnownBlockId($zero)
            ->save()
        ;

// create first master node transactions
        NewPubKeyByAddrDbRow::create($app, $firstMnodeAddress->getAddressBin())
            ->setAddressWithPubKey($firstMnodeAddress)
            ->save();

        RegisterNodeHostTransaction::create($app)
            ->setAuthorAddress($firstMnodeAddress)
            ->setHost(Host::create($app, Host::TRANSPORT_TCP, FIRST_M_NODE_HOST))
            ->setNodeName(FIRST_M_NODE_NAME)
            ->createRaw()
            ->saveAsNewTransaction()
        ;

        $block = Block::createNew($app, $chainId);
    }
}