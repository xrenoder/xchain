<?php
/**
 * Base classenum for other application classes, uses App classenum (with Logger, Daemon, Server, Node etc)
 */
abstract class aBase
{
    protected static $dbgLvl = Logger::DBG_LOCATOR; /* override me */

    /** @var aLocator */
    private $locator = null;
    public function setLocator(?aLocator $val) : self {$this->locator = $val; return $this;}
    protected function getLocator() : aLocator {return $this->locator;}

    /** @var aBase */
    private $parent = null;
    public function setParent(?aBase $val) : self {$this->parent = $val; return $this;}
    protected function getParent() : aBase {return $this->parent;}

    /** @var int */
    private $unpackedLength = null;
    public function getUnpackedLength() : int {return $this->unpackedLength;}

    public function getMyNodeType() : ?int {return $this->locator->getMyNode()->getType();}

    /**
     * AppBase constructor.
     * @param aBase $parent
     */
    protected function __construct(aBase $parent)
    {
        $this->parent = $parent;
        $locator = $parent;

        while (!$locator instanceof aLocator) {
            $locator = $locator->getParent();
        }

        $this->locator = $locator;
    }

    /**
     * Short work logging
     * @param string $message
     */
    public function log(string $message) : void
    {
        $this->getLocator()->getLogger()->simpleLog(static::$dbgLvl, $message);
    }

    /**
     * Short error logging
     * @param string $message
     */
    public function err(string $message) : void
    {
        $this->getLocator()->getLogger()->errorLog(static::$dbgLvl, $message);
    }

    /**
     * Short debug logging
     * @param int $lvl
     * @param string $message
     */
    public function dbg(string $message) : void
    {
        $this->getLocator()->getLogger()->debugLog(static::$dbgLvl, $message);
    }

    protected function dbTrans() : string
    {
        return $this->getLocator()->getDba()->transactionBegin();
    }

    protected function dbCommit(string $transactionKey)
    {
        $this->getLocator()->getDba()->transactionCommit($transactionKey);
    }

    public function &simplePack(int $formatType, &$data) : ?string
    {
        $fieldFormatObject = aFieldFormat::spawn($this, $formatType);
        $result = $fieldFormatObject->packField($data);
        unset($fieldFormatObject);

        return $result;
    }

    public function &simpleUnpack(int $formatType, string &$raw, int $offset = 0)
    {
        $fieldFormatObject = aFieldFormat::spawn($this, $formatType, $offset);
        $result = $fieldFormatObject->unpackField($raw);
        $this->unpackedLength = $fieldFormatObject->getLength();
        unset($fieldFormatObject);

        return $result;
    }
}