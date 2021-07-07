<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class TimesController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Time';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchTimeAction()
    {
        return $this->searchBase(
            "general.Times.Time",
            array('Description', 'uuid'),
            "Description"
        );
    }

    public function getTimeAction($uuid = null)
    {
        return $this->getBase("Time", "general.Times.Time", $uuid);
    }

    public function setTimeAction($uuid)
    {
        return $this->setBase("Time", "general.Times.Time", $uuid);
    }

    public function addTimeAction()
    {
        return $this->addBase("Time", "general.Times.Time");
    }

    public function delTimeAction($uuid)
    {
        if (($time = $this->getModel()->general->Times->Time->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($times = $acl->Times) != null && isset($times->getNodeData()[$uuid]["selected"]) && $times->getNodeData()[$uuid]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Times.Time", $uuid);
    }
}
