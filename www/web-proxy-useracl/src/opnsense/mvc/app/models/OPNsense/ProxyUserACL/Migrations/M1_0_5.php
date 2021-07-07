<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_5 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);

        $remoteACL = $model->general->remoteACLs->remoteACL->add();
        $remoteACL->filename = "zapretinfo_url";
        $remoteACL->url = "http://list.smart-soft.ru/URL/zapretinfo_url.txt";
        $remoteACL->Type = "url_regex";
        $remoteACL->Description = "Список URL Роскомнадзора";

        $remoteACL = $model->general->remoteACLs->remoteACL->add();
        $remoteACL->filename = "zapretinfo_ip";
        $remoteACL->url = "http://list.smart-soft.ru/IP/zapretinfo_ip.txt";
        $remoteACL->Type = "dst";
        $remoteACL->Description = "Список IP Роскомнадзора";

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
