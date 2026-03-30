<?php
class Test {

    public $alive;
    public $api;
    public $api_wrap;
    public $cache;
    public $candle;
    public $candle_volume;
    public $connection;
    public $dashboard;
    public $forex;
    public $library;
    public $maintenance;
    public $server;
    public $setting;
    public $sql;
    public $sql_cnx;
    public $test;
    public $tool;
    public $tool_trade;
    public $trade_add;
    public $trade_call;
    public $trade_call_send;
    public $trade_history;
    public $trade_order;
    public $visual;

    public function __construct() {

        # Function
        foreach ($_SESSION[fun] as $k => $v) $this->$k = $v;
    }

    public function Exec() {

        $_SESSION[fun][visual]->Print($_SESSION[mem]);
    }
}