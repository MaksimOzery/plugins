<?php
/**
 *    Copyright (C) 2017-2021 Smart-Soft
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
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
