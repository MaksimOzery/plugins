<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class ArpsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Arp';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchArpAction()
    {
        return $this->searchBase(
            "general.Arps.Arp",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getArpAction($uuid = null)
    {
        return $this->getBase("Arp", "general.Arps.Arp", $uuid);
    }

    public function setArpAction($uuid)
    {
        return $this->setBase("Arp", "general.Arps.Arp", $uuid);
    }

    public function addArpAction()
    {
        return $this->addBase("Arp", "general.Arps.Arp");
    }

    public function delArpAction($uuid)
    {
        if (($arp = $this->getModel()->general->Arps->Arp->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($arps = $acl->Arps) != null && isset($arps->getNodeData()[$uuid]["selected"]) && $arps->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Arps.Arp", $uuid);
    }

}
