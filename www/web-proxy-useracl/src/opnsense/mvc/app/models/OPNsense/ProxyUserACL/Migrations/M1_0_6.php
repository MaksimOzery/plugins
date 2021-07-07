<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_6 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);

        $remoteACL = $model->general->remoteACLs->remoteACL->add();
        $remoteACL->filename = "yoyo.org";
        $remoteACL->url = "https://pgl.yoyo.org/adservers/serverlist.php?hostformat=nohtml&showintro=1";
        $remoteACL->Type = "dstdomain";
        $remoteACL->Description = "Блокировка рекламы от https://pgl.yoyo.org/adservers";

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
