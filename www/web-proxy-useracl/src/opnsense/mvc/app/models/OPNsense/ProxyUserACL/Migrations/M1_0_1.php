<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;
use OPNsense\Proxy\Proxy;


class M1_0_1 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);
        if (!isset($model->general->ACLs->ACL))
            return;

        $methods = explode(',', (new Proxy())->forward->authentication->method->__toString());
        if (count($methods) > 0 && !empty($methods[0])) {
            $server = $methods[0];
        } else {
            $server = "Local Database";
        }
        foreach ($model->general->ACLs->ACL->getChildren() as $ACL) {
            $ACL->Server = $server;
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
