<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class MimesController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Mime';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchMimeAction()
    {
        return $this->searchBase(
            "general.Mimes.Mime",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getMimeAction($uuid = null)
    {
        return $this->getBase("Mime", "general.Mimes.Mime", $uuid);
    }

    public function setMimeAction($uuid)
    {
        return $this->setBase("Mime", "general.Mimes.Mime", $uuid);
    }

    public function addMimeAction()
    {
        return $this->addBase("Mime", "general.Mimes.Mime");
    }

    public function delMimeAction($uuid)
    {
        if (($mime = $this->getModel()->general->Mimes->Mime->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($mimess = $acl->Mimes) != null && isset($mimess->getNodeData()[$uuid]["selected"]) && $mimess->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Mimes.Mime", $uuid);
    }

}
