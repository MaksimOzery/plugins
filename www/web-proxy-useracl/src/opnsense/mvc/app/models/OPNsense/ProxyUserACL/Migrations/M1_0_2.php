<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_2 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);
        if (!isset($model->general->ACLs->ACL))
            return;

        foreach ($model->general->ACLs->ACL->getNodes() as $uuid => $acl) {
            $result = [];
            if ($model->general->ACLs->ACL->{$uuid}->Domains != "") {
                foreach (explode(",", $model->general->ACLs->ACL->{$uuid}->Domains) as $domain) {
                    $result[] = ($domain[0] == "." ? "." : "") . idn_to_utf8($domain);
                }
            }
            $model->general->ACLs->ACL->{$uuid}->Domains = implode(",", $result);
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
