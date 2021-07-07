<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_3 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);
        if (!isset(Config::getInstance()->object()->OPNsense->ProxyUserACL->general->ACLs->ACL))
            return;

        foreach (Config::getInstance()->object()->OPNsense->ProxyUserACL->general->ACLs->ACL as $acl) {

            $user = $model->general->Users->User->add();
            $user->Names = $acl->Name->__toString();
            $user->Hex = $acl->Hex->__toString();
            $user->Group = $acl->Group->__toString();
            $user->Server = $acl->Server->__toString();
            $user->Description = $acl->Name->__toString();

            $domain = $model->general->Domains->Domain->add();
            $domain->Names = $acl->Domains->__toString();
            $domain->Description = $acl->Domains->__toString();
            $domain->Regexp = 1;

            $new_acl = $model->general->HTTPAccesses->HTTPAccess->add();
            $new_acl->Users = $user->getAttributes()["uuid"];
            $new_acl->Domains = $domain->getAttributes()["uuid"];

            unset($acl);
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
