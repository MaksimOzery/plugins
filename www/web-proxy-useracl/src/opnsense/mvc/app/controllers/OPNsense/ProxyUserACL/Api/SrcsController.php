<?php

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
