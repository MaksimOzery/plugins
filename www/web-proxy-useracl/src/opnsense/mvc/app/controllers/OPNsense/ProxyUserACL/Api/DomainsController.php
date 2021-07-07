<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class DomainsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Domain';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchDomainAction()
    {
        return $this->searchBase(
            "general.Domains.Domain",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getDomainAction($uuid = null)
    {
        return $this->getBase("Domain", "general.Domains.Domain", $uuid);
    }

    public function setDomainAction($uuid)
    {
        return $this->setBase("Domain", "general.Domains.Domain", $uuid);
    }

    public function addDomainAction()
    {
        return $this->addBase("Domain", "general.Domains.Domain");
    }

    public function delDomainAction($uuid)
    {
        if (($domain = $this->getModel()->general->Domains->Domain->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($domains = $acl->Domains) != null && isset($domains->getNodeData()[$uuid]["selected"]) && $domains->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Domains.Domain", $uuid);
    }

}
