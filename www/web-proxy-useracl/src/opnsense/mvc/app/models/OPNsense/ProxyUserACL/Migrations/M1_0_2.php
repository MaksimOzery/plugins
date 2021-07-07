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
