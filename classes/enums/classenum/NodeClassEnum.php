<?php
/**
 * Enumeration of nodes
 * items:   "node type" => "node classenum name"
 * data:    "node type" => array(
 *              'canConnect' => flags of nodes, to whom this node can connect
 *              'canAccept' =>  flags of nodes, from whom this node can accept connection
 *          )
 */

class NodeClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aNode';

    public const CAN_CONNECT  = 'canConnect';
    public const CAN_ACCEPT  = 'canAccept';

    /* maximal ID is 255 (8 bits) */
    public const CLIENT =    1;
    public const FRONT =     2;
    public const PROXY =     4;
    public const SIDE =      8;
    public const MASTER =    16;
    public const TORRENT =   32;

    protected static $items = array(
        self::CLIENT => 'ClientNode',
        self::FRONT => 'FrontNode',
        self::PROXY => 'ProxyNode',
        self::SIDE => 'SideNode',
        self::MASTER => 'MasterNode',
        self::TORRENT => 'TorrentNode',
    );

    protected static $data = array(
        self::CLIENT => array(
            self::CAN_ACCEPT => 0,
            self::CAN_CONNECT => self::FRONT
        ),
        self::FRONT => array(
            self::CAN_ACCEPT => self::CLIENT,
            self::CAN_CONNECT => self::PROXY
        ),
        self::PROXY => array(
            self::CAN_ACCEPT => self::FRONT,
            self::CAN_CONNECT => self::SIDE
        ),
        self::SIDE => array(
            self::CAN_ACCEPT => self::PROXY | self::SIDE,
            self::CAN_CONNECT => self::SIDE | self::MASTER
        ),
        self::MASTER => array(
            self::CAN_ACCEPT => self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE | self::MASTER
        ),
        self::TORRENT => array(
            self::CAN_ACCEPT => self::PROXY | self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE
        ),
    );

    public static function getCanConnect(int $id) : int
    {
        return self::$data[$id][self::CAN_CONNECT];
    }

    public static function getCanAccept(int $id) : int
    {
        return self::$data[$id][self::CAN_ACCEPT];
    }

    public static function getName(int $id) : string
    {
        return self::$items[$id];
    }
}