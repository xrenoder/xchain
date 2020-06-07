<?php
/**
 * "Address-to-Chain without amount without data" (type C)
 * unfreeze all
 * undelegate all from all nodes
 *
 * 115 bytes
 */


abstract class aTransactionC extends aTransaction
{
    use tTransactionConstructor;


    /**
     * @return string
     */
    public function createRaw() : ?string
    {
        $this->raw = '';
        return $this->compositeRaw();
    }

}