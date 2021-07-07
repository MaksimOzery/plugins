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
