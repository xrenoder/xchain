<?php
/**
 * Enumeration of nodes
 * items:   "node type" => "node class name"
 * data:    "node type" => array(
 *              'canConnect'    => flags of nodes, to whom this node can connect
 *          )
 */

class NodeClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aNode';

    public const DATA_CAN_CONNECT  = 'canConnect';

    public const FRONT_FLAG  = 1;
    public const PROXY_FLAG  = 2;
    public const SIDE_FLAG  = 4;
    public const MASTER_FLAG  = 8;

    /* maximal ID is 15 (4 bits) */
    public const CLIENT_ID  = 0;
    public const FRONT_ID  = self::FRONT_FLAG;                                                              // 1
    public const PROXY_ID  = self::PROXY_FLAG;                                                              // 2
    public const SIDE_ID  = self::SIDE_FLAG;                                                                // 4
    public const MASTER_ID  = self::MASTER_FLAG;                                                            // 8
    public const TORRENT_ID  = self::MASTER_FLAG | self::SIDE_FLAG | self::PROXY_FLAG | self::FRONT_FLAG;   // 15

    protected static $items = array(
        self::CLIENT_ID => 'ClientNode',
        self::FRONT_ID => 'FrontNode',
        self::PROXY_ID => 'ProxyNode',
        self::SIDE_ID => 'SideNode',
        self::MASTER_ID => 'MasterNode',
        self::TORRENT_ID => 'TorrentNode',
    );

    protected static $data = array(
        self::CLIENT_ID => array(self::DATA_CAN_CONNECT => self::FRONT_FLAG),
        self::FRONT_ID => array(self::DATA_CAN_CONNECT => self::PROXY_FLAG),
        self::PROXY_ID => array(self::DATA_CAN_CONNECT => self::SIDE_FLAG),
        self::SIDE_ID => array(self::DATA_CAN_CONNECT => self::SIDE_FLAG | self::MASTER_FLAG),
        self::MASTER_ID => array(self::DATA_CAN_CONNECT => self::SIDE_FLAG | self::MASTER_FLAG),
        self::TORRENT_ID => array(self::DATA_CAN_CONNECT => self::SIDE_FLAG),
    );
}