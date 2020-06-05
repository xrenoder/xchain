<?php
/**
 * Based on pattern Proxy (ambassador, legate)
 * Class for transfering socket state between socket-object in parent thread and message in worker thread
 */

class SocketLegate extends aBase
{
    /** @var string */
    private $id;
    public function setId(string $val) : self {$this->id = $val; return $this;}
    public function getId() : string {return $this->id;}

    /** @var bool  */
    private $workerResult = null;
    public function getWorkerResult() : bool {return $this->workerResult;}

    /** @var string  */
    private $responseString = null;
    public function setResponseString(?string $val) : self {$this->responseString = $val; return $this;}
    public function getResponseString() : ?string {return $this->responseString;}

    /* is this socket create by 'connect' */
    /** @var bool  */
    private $connected = false;
    public function setConnected() : self {$this->connected = true; return $this;}
    public function isConnected() : bool {return $this->connected;}

    /** @var bool  */
    private $badData = false;
    public function setBadData() : self {$this->badData = true; return $this;}
    public function isBadData() : bool {return $this->badData;}

    /** @var bool  */
    private $freeAfterSend = false;
    public function setFreeAfterSend(bool $val) : self {$this->freeAfterSend = $val; return $this;}
    public function needFreeAfterSend() : bool {return $this->freeAfterSend;}

    /** @var bool  */
    private $closeAfterSend = false;
    public function setCloseAfterSend() : self {$this->closeAfterSend = true; return $this;}
    public function needCloseAfterSend() : bool {return $this->closeAfterSend;}

    /** @var bool  */
    private $needCloseSocket = false;
    public function setNeedCloseSocket() : self {$this->needCloseSocket = true; return $this;}
    public function needCloseSocket() : bool {return $this->needCloseSocket;}

    /** @var bool  */
    private $isServerBusy = false;
    public function setServerBusy() : self {$this->isServerBusy = true; return $this;}
    public function isServerBusy() : bool {return $this->isServerBusy;}

    /** @var int  */
    private $myNodeId = null;
    public function setMyNodeId(int $val) : self {$this->myNodeId = $val; return $this;}
    public function getMyNodeId() : ?int {return $this->myNodeId;}

    /** @var string  */
    private $incomingString = null;
    public function setIncomingString(string $val) : self {$this->incomingString = $val; return $this;}

    /** @var int  */
    private $incomingStringTime = 0;
    public function setIncomingStringTime(int $val) : self {$this->incomingStringTime = $val; return $this;}

    /** @var int  */
    private $readBufferSize = 0;
    public function setReadBufferSize(int $val) : self {$this->readBufferSize = $val; return $this;}
    public function getReadBufferSize() : int {return $this->readBufferSize;}

    /* objects stay in worker thread, not sended to parent thread and not used in socket-object  */
    /** @var aMessage  */
    private $inMessage = null;
    public function setInMessage(?aMessage $val) : self {$this->inMessage = $val; return $this;}
    public function getInMessage() : ?aMessage {return $this->inMessage;}

    public static function create(aBase $parent, string $id) : self
    {
// for parent thread $parent is socket-object, for worker thread - worker-object
        $me = new static($parent);
        return $me->setId($id);
    }

    public function incomingPacketHandler(parallel\Channel $channel) : void
    {
        $packet = $this->incomingString;
        $this->incomingString = null;
        $message = $this->getInMessage();

        if ($message === null) {
//            $tmpMessage = BusyResMessage::create($this->getLocator());
            $messageId = TypeMessageField::create($this)->unpack($packet);
//            unset($tmpMessage);

            if (!($message = aMessage::spawn($this, $messageId))) {
// if cannot create classenum of request by declared type - incoming data is bad
                $this->getLocator()->dbg("BAD DATA cannot create message classenum with ID $messageId");
                $this->badData = true;
                $this->workerResult = aMessage::MESSAGE_PARSED;
                return;
            }

            $message->setIncomingMessageTime($this->incomingStringTime);
        }

        $this->workerResult = $message->addPacket($packet);

        $serializedLegate = $this->serializeInWorker();
        CommandToParent::send($channel, CommandToParent::PACKET_RESPONSE, $this->id, $serializedLegate);

        $this->getLocator()->dbg("Send SocketLegate from worker " . $this->getLocator()->getName(). " to socket $this->id");
//        $this->dbg("\n $serializedLegate\n");

    }

    public function createResponseString(aMessage $message) : void
    {
        $this->responseString = $message->createMessageString();
    }

    public function serializeInSocket() : string
    {
        $locator = $this->getLocator();
        $this->setLocator(null);

        $parent = $this->getParent();
        $this->setParent(null);

        $string = serialize($this);

        $this->setLocator($locator);
        $this->setParent($parent);

        return $string;
    }

    public function unserializeInWorker(string $serializedLegate) : SocketLegate
    {
        /** @var SocketLegate $legate */
        $legate = unserialize($serializedLegate, ['allowed_classes' => true]);

        $message = $this->getInMessage();

        if ($message !== null) {
            $message->setParent($legate); // very important!!!!! if not do it, message will use old, not unserialized legate
        }

        $legate
            ->setLocator($this->getLocator())
            ->setParent($this->getParent())
            ->setInMessage($message);

//        $this->getLocator()->dbg("legate unserialized");

        return $legate;
    }

    public function serializeInWorker() : string
    {
/*
        if ($this->workerResult) {
            $this->dbg("SocketLegate-object is:\n" . var_export($this, true) . "\n");
        }
*/
        $locator = $this->getLocator();
        $this->setLocator(null);

        $parent = $this->getParent();
        $this->setParent(null);

        $inMessage = $this->getInMessage();
        $this->setInMessage(null);

        $string = serialize($this);

        $this->setLocator($locator);
        $this->setParent($parent);
        $this->setInMessage($inMessage);

        return $string;
    }

    public function unserializeInSocket(string $serializedLegate) : SocketLegate
    {
        /** @var SocketLegate $legate */
        $legate = unserialize($serializedLegate, ['allowed_classes' => true]);
        $legate
            ->setLocator($this->getLocator())
            ->setParent($this->getParent());

        return $legate;
    }
}