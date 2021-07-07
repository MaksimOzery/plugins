<?php

namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;
use OPNsense\ProxyUserACL\Api\UsersController;


class M1_0_0 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);
        if (!is_object(Config::getInstance()->object()->OPNsense->ProxyUserACL->general))
            return;

        $config = Config::getInstance()->object()->OPNsense->ProxyUserACL->general;
        $priority = 0;

        if (isset($config->userACLs->userACL)) {
            foreach ($config->userACLs->userACL as $userACL) {
                $ACL = $model->general->ACLs->ACL->add();
                $ACL->Name = (string)$userACL->userName;
                $ACL->Hex = UsersController::strToHex((string)$userACL->userName);
                $ACL->Domains = (string)$userACL->userWhiteList;
                $ACL->Black = "allow";
                $ACL->Priority = (string)$priority++;
                $ACL->Group = "user";

                $ACL = $model->general->ACLs->ACL->add();
                $ACL->Name = (string)$userACL->userName;
                $ACL->Hex = UsersController::strToHex((string)$userACL->userName);
                $ACL->Domains = (string)$userACL->userBlackList;
                $ACL->Black = "deny";
                $ACL->Priority = (string)$priority++;
                $ACL->Group = "user";
            }
            unset($config->userACLs);
        }

        if (isset($config->groupACLs->groupACL)) {
            foreach ($config->groupACLs->groupACL as $groupACL) {
                $ACL = $model->general->ACLs->ACL->add();
                $ACL->Name = (string)$groupACL->groupName;
                $ACL->Hex = UsersController::strToHex((string)$groupACL->groupName);
                $ACL->Domains = (string)$groupACL->groupWhiteList;
                $ACL->Black = "allow";
                $ACL->Priority = (string)$priority++;
                $ACL->Group = "group";

                $ACL = $model->general->ACLs->ACL->add();
                $ACL->Name = (string)$groupACL->groupName;
                $ACL->Hex = UsersController::strToHex((string)$groupACL->groupName);
                $ACL->Domains = (string)$groupACL->groupBlackList;
                $ACL->Black = "deny";
                $ACL->Priority = (string)$priority++;
                $ACL->Group = "group";
            }
            unset($config->groupACLs);
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
