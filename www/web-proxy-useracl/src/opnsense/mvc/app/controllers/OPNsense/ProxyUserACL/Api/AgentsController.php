<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class AgentsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Agent';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchAgentAction()
    {
        return $this->searchBase(
            "general.Agents.Agent",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getAgentAction($uuid = null)
    {
        return $this->getBase("Agent", "general.Agents.Agent", $uuid);
    }

    public function setAgentAction($uuid)
    {
        return $this->setBase("Agent", "general.Agents.Agent", $uuid);
    }

    public function addAgentAction()
    {
        return $this->addBase("Agent", "general.Agents.Agent");
    }

    public function delAgentAction($uuid)
    {
        if (($agent = $this->getModel()->general->Agents->Agent->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($agents = $acl->Agents) != null && isset($agents->getNodeData()[$uuid]["selected"]) && $agents->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Agents.Agent", $uuid);
    }

}
