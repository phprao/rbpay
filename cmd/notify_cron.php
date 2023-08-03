<?php

/**
 * 后台通知程序
 * 
 * 按照间隔  15s/15s/30s/3m/10m/20m/30m/30m/30m/60m/3h/3h/3h/6h/6h - 总计 24h4m
 * 
 * php notify_cron.php
 * 
 * php notify_cron.php stop
 */

require dirname(__FILE__) . '/../includes/common.php';

class NotifyCron
{
    public $gap;
    public $maxNotifyNum;
    public $db;
    public $filename;

    public function __construct($db)
    {
        $this->db = $db;
        $this->gap = [15, 15, 30, 180, 600, 1200, 1800, 1800, 1800, 3600, 10800, 10800, 10800, 21600, 21600];
        $this->maxNotifyNum = count($this->gap) + 1;
        $this->filename = ROOT . "cmd/notify_cron_exit.lock";
    }

    public function dealOrder()
    {
        try {
            $list = $this->db->getAll("SELECT * from pre_order where status = 1 and notify_status = 0 and notify < {$this->maxNotifyNum} order by endtime asc limit 20");
            if (count($list) == 0) {
                return;
            }

            foreach ($list as $order) {
                if ($this->needNotify($order)) {
                    notifyCustom($order);
                }
            }
        } catch (Exception $e) {
            $err = sprintf("%s in %s:%d", $e->getMessage(), $e->getFile(), $e->getLine());
            addLog("[服务器错误dealOrder]" . $err, "ERROR");
        }
    }

    public function dealWithdrawOrder()
    {
        try {
            $list = $this->db->getAll("SELECT * from pre_withdraw_order where status = 1 and notify_status = 0 and notify < {$this->maxNotifyNum} order by endtime asc limit 20");
            if (count($list) == 0) {
                return;
            }

            foreach ($list as $order) {
                if ($this->needNotify($order)) {
                    notifyCustomWithdraw($order);
                }
            }
        } catch (Exception $e) {
            $err = sprintf("%s in %s:%d", $e->getMessage(), $e->getFile(), $e->getLine());
            addLog("[服务器错误dealWithdrawOrder]" . $err, "ERROR");
        }
    }

    public function needNotify($order)
    {
        $curr = time();
        if (empty($order['endtime'])) {
            return false;
        }
        if ($curr - strtotime($order['endtime']) <= 2) {
            return false;
        }

        if ($order['notify'] == 0) {
            return true;
        }

        $g = $order['notify'] - 1;
        if (($curr - strtotime($order['notifytime'])) >= $this->gap[$g]) {
            return true;
        } else {
            return false;
        }
    }

    public function needExit()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
            return true;
        }

        return false;
    }

    public function cronExit()
    {
        touch($this->filename);
    }

    public function notify()
    {
        while (true) {
            if ($this->needExit()) {
                break;
            }

            $this->dealOrder();
            $this->dealWithdrawOrder();
            sleep(2);
        }
    }
}

$o = new NotifyCron($DB);

if (isset($argv[1]) && $argv[1] == 'stop') {
    $o->cronExit();
} else {
    $o->notify();
}
