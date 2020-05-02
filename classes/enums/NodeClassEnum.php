<?php
/**
 * Enumeration of nodes
 * items:   "node type" => "node class name"
 * data:    "node type" => array(
 *              'canConnect' => flags of nodes, to whom this node can connect
 *              'canAccept' =>  flags of nodes, from whom this node can accept connection
 *          )
 */

class NodeClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aNode';

    public const DATA_CAN_CONNECT  = 'canConnect';
    public const DATA_CAN_ACCEPT  = 'canAccept';

    /* maximal ID is 255 (8 bits) */
    public const CLIENT_ID =    1;
    public const FRONT_ID =     2;
    public const PROXY_ID =     4;
    public const SIDE_ID =      8;
    public const MASTER_ID =    16;
    public const TORRENT_ID =   32;

    protected static $items = array(
        self::CLIENT_ID => 'ClientNode',
        self::FRONT_ID => 'FrontNode',
        self::PROXY_ID => 'ProxyNode',
        self::SIDE_ID => 'SideNode',
        self::MASTER_ID => 'MasterNode',
        self::TORRENT_ID => 'TorrentNode',
    );

    protected static $data = array(
        self::CLIENT_ID => array(
            self::DATA_CAN_ACCEPT => 0,
            self::DATA_CAN_CONNECT => self::FRONT_ID
        ),
        self::FRONT_ID => array(
            self::DATA_CAN_ACCEPT => self::CLIENT_ID,
            self::DATA_CAN_CONNECT => self::PROXY_ID
        ),
        self::PROXY_ID => array(
            self::DATA_CAN_ACCEPT => self::FRONT_ID,
            self::DATA_CAN_CONNECT => self::SIDE_ID
        ),
        self::SIDE_ID => array(
            self::DATA_CAN_ACCEPT => self::PROXY_ID | self::SIDE_ID,
            self::DATA_CAN_CONNECT => self::SIDE_ID | self::MASTER_ID
        ),
        self::MASTER_ID => array(
            self::DATA_CAN_ACCEPT => self::SIDE_ID | self::MASTER_ID,
            self::DATA_CAN_CONNECT => self::SIDE_ID | self::MASTER_ID
        ),
        self::TORRENT_ID => array(
            self::DATA_CAN_ACCEPT => self::PROXY_ID | self::SIDE_ID | self::MASTER_ID,
            self::DATA_CAN_CONNECT => self::SIDE_ID
        ),
    );

    public static function getCanConnect(int $id) : int
    {
        return self::$data[$id][self::DATA_CAN_CONNECT];
    }

    public static function getCanAccept(int $id) : int
    {
        return self::$data[$id][self::DATA_CAN_ACCEPT];
    }

    public static function getName(int $id) : int
    {
        return self::$items[$id];
    }
}