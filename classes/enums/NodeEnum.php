<?php
/**
 * Enumeration of nodes
 * items:   "node type" => "node classenum name"
 * data:    "node type" => array(
 *              'canConnect' => flags of nodes, to whom this node can connect
 *              'canAccept' =>  flags of nodes, from whom this node can accept connection
 *          )
 */

class NodeEnum extends aEnum
{
    public const CAN_CONNECT  = 'canConnect';
    public const CAN_ACCEPT  = 'canAccept';

    /* maximal ID is 255 (8 bits) */
    public const CLIENT =       0;
    public const FRONT =        1;
    public const PROXY_ASP =    2;  // 2+1 = 3
    public const PROXY =        4;
    public const SIDE_ASP =     8;  // 8+4 = 12
    public const SIDE =         16;
    public const MASTER_ASP =   32; // 32+16 = 48
    public const MASTER =       64;
    public const TORRENT =      128;

    protected static $items = array(
        self::CLIENT => 'Client',
        self::FRONT => 'Front',
        self::PROXY_ASP => 'Proxy Aspirant',
        self::PROXY => 'Proxy',
        self::SIDE_ASP => 'Side Aspirant',
        self::SIDE => 'Side',
        self::MASTER_ASP => 'Master Aspirant',
        self::MASTER => 'Master',
        self::TORRENT => 'Torrent',
    );

    protected static $data = array(
        self::CLIENT => array(
            self::CAN_ACCEPT => 0,
            self::CAN_CONNECT => self::FRONT
        ),

        self::FRONT => array(
            self::CAN_ACCEPT => self::CLIENT,
            self::CAN_CONNECT => self::PROXY | self::TORRENT
        ),
        self::PROXY_ASP => array(
            self::CAN_ACCEPT => self::CLIENT,
            self::CAN_CONNECT => self::PROXY | self::TORRENT
        ),

        self::PROXY => array(
            self::CAN_ACCEPT => self::FRONT,
            self::CAN_CONNECT => self::SIDE | self::TORRENT
        ),
        self::SIDE_ASP => array(
            self::CAN_ACCEPT => self::FRONT,
            self::CAN_CONNECT => self::SIDE | self::TORRENT
        ),

        self::SIDE => array(
            self::CAN_ACCEPT => self::PROXY | self::SIDE,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),
        self::MASTER_ASP => array(
            self::CAN_ACCEPT => self::PROXY | self::SIDE,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),

        self::MASTER => array(
            self::CAN_ACCEPT => self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),

        self::TORRENT => array(
            self::CAN_ACCEPT => self::FRONT | self::PROXY | self::SIDE | self::MASTER | self::TORRENT,
            self::CAN_CONNECT => self::TORRENT
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