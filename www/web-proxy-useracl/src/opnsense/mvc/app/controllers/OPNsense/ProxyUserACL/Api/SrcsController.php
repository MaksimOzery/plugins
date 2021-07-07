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
 
namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class SrcsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Src';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchSrcAction()
    {
        return $this->searchBase(
            "general.Srcs.Src",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getSrcAction($uuid = null)
    {
        return $this->getBase("Src", "general.Srcs.Src", $uuid);
    }

    public function setSrcAction($uuid)
    {
        return $this->setBase("Src", "general.Srcs.Src", $uuid);
    }

    public function addSrcAction()
    {
        return $this->addBase("Src", "general.Srcs.Src");
    }

    public function delSrcAction($uuid)
    {
        if (($src = $this->getModel()->general->Srcs->Src->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($srcs = $acl->Srcs) != null && isset($srcs->getNodeData()[$uuid]["selected"]) && $srcs->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Srcs.Src", $uuid);
    }

}
