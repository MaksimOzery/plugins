<?php

/*
 * Copyright (C) 2017-2021 Smart-Soft
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */
 
namespace OPNsense\ProxyUserACL\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class RemoteController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'blacklist';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchRemoteBlacklistsAction()
    {
        return $this->searchBase(
            "general.remoteACLs.remoteACL",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getRemoteBlacklistsAction($uuid = null)
    {
        return $this->getBase("blacklist", "general.remoteACLs.remoteACL", $uuid);
    }

    public function setRemoteBlacklistsAction($uuid)
    {
        return $this->setBase("blacklist", "general.remoteACLs.remoteACL", $uuid);
    }

    public function addRemoteBlacklistsAction()
    {
        return $this->addBase("blacklist", "general.remoteACLs.remoteACL");
    }

    public function delRemoteBlacklistsAction($uuid)
    {
        if (($remote = $this->getModel()->general->remoteACLs->remoteACL->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($remoteACLs = $acl->remoteACLs) != null && isset($remoteACLs->getNodeData()[$uuid]["selected"]) && $remoteACLs->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.remoteACLs.remoteACL", $uuid);
    }

    public function fetchaclsAction()
    {
        if (!$this->request->isPost()) {
            return ["response" => []];
        }

        // close session for long running action
        $this->sessionClose();

        $backend = new Backend();
        // generate template
        $backend->configdRun('template reload OPNsense/ProxyUserACL');

        // fetch files
        $response = $backend->configdRun("proxy fetchacls");
        return ["response" => $response, "status" => "ok"];
    }

    public function downloadaclsAction()
    {
        if (!$this->request->isPost()) {
            return ["response" => []];
        }
        // close session for long running action
        $this->sessionClose();

        $backend = new Backend();
        // generate template
        $backend->configdRun('template reload OPNsense/ProxyUserACL');

        // download files
        $response = $backend->configdRun("proxy downloadacls");
        return ["response" => $response, "status" => "ok"];
    }
}
