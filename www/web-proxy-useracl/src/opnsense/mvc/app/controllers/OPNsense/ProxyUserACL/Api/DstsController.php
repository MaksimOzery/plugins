<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class DstsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Dst';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchDstAction()
    {
        return $this->searchBase(
            "general.Dsts.Dst",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getDstAction($uuid = null)
    {
        return $this->getBase("Dst", "general.Dsts.Dst", $uuid);
    }

    public function setDstAction($uuid)
    {
        return $this->setBase("Dst", "general.Dsts.Dst", $uuid);
    }

    public function addDstAction()
    {
        return $this->addBase("Dst", "general.Dsts.Dst");
    }

    public function delDstAction($uuid)
    {
        if (($dst = $this->getModel()->general->Dsts->Dst->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($dsts = $acl->Dsts) != null && isset($dsts->getNodeData()[$uuid]["selected"]) && $dsts->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Dsts.Dst", $uuid);
    }

}
