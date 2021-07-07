<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_7 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);
        if (!isset(Config::getInstance()->object()->OPNsense->ProxyUserACL->general->HTTPAccesses->HTTPAccess)) {
            return;
        }

        foreach (Config::getInstance()->object()->OPNsense->ProxyUserACL->general->HTTPAccesses->HTTPAccess as $acl) {
            if (($id = $model->general->Users->User->{$acl->Users}->id) === null) {
                continue;
            }
            $model->general->HTTPAccesses->HTTPAccess->{$acl->attributes()["uuid"]}->Users = $id->__toString();
        }

        foreach (Config::getInstance()->object()->OPNsense->ProxyUserACL->general->ICAPs->ICAP as $acl) {
            if (($id = $model->general->Users->User->{$acl->Users}->id) === null) {
                continue;
            }
            $model->general->ICAPs->ICAP->{$acl->attributes()["uuid"]}->Users = $id->__toString();;
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
