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

    public const ASP_FLAG  = 128;

    /* maximal ID is 255 (8 bits) */
    public const CLIENT =       1;
    public const FRONT =        2;
    public const PROXY_ASP =    self::FRONT | self::ASP_FLAG;
    public const PROXY =        4;
    public const SIDE_ASP =     self::PROXY | self::ASP_FLAG;
    public const SIDE =         8;
    public const MASTER_ASP =   self::SIDE | self::ASP_FLAG;
    public const MASTER =       16;
    public const TORRENT_ASP =  32 | self::ASP_FLAG;
    public const TORRENT =      64;

    protected static $items = array(
        self::CLIENT => 'Client',
        self::FRONT => 'Front',
        self::PROXY_ASP => 'Proxy Aspirant',
        self::PROXY => 'Proxy',
        self::SIDE_ASP => 'Side Aspirant',
        self::SIDE => 'Side',
        self::MASTER_ASP => 'Master Aspirant',
        self::MASTER => 'Master',
        self::TORRENT_ASP => 'Torrent Aspirant',
        self::TORRENT => 'Torrent',
    );

    protected static $data = array(
        self::CLIENT => array(
            self::CAN_ACCEPT => self::CLIENT,                   // 0
            self::CAN_CONNECT => self::CLIENT | self::FRONT     // FRONT
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
            self::CAN_ACCEPT => self::PROXY | self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),
        self::MASTER_ASP => array(
            self::CAN_ACCEPT => self::PROXY | self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),

        self::MASTER => array(
            self::CAN_ACCEPT => self::SIDE | self::MASTER,
            self::CAN_CONNECT => self::SIDE | self::MASTER | self::TORRENT
        ),

        self::TORRENT_ASP => array(
            self::CAN_ACCEPT => 0,
            self::CAN_CONNECT => self::TORRENT
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