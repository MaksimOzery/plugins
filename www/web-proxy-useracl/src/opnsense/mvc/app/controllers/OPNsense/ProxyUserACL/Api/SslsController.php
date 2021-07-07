<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Tools;
use \OPNsense\ProxyUserACL\ProxyUserACL;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class SslsController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Arp';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchAclAction()
    {
        $this->sessionClose();

        $mdlProxyUserACL = new ProxyUserACL();
        $grid = new UIModelGrid($mdlProxyUserACL->general->SSLs->SSL);
        $columns = ["Users", "Domains", "Agents", "Times", "Mimes", "Srcs", "Dsts", "Arps", "remoteACLs"];
        $ret = $grid->fetchBindRequest($this->request, array_merge($columns, ["enabled", "uuid"]),
            "Users");
        foreach ($ret["rows"] as &$row) {
            $visible = [];
            foreach ($columns as $column) {
                if ($row[$column] != "") {
                    $visible[] = $row[$column];
                }
            }

            $row["Description"] = implode("|", $visible);
        }
        return $ret;
    }

    public function getACLAction($uuid = null)
    {
        $mdlProxyUserACL = new ProxyUserACL();
        if ($uuid == null) {
            // generate new node, but don't save to disc
            $node = $mdlProxyUserACL->general->SSLs->SSL->add();
            $nodes = $node->getNodes();
            return ["SSL" => $nodes];
        }

        $node = $mdlProxyUserACL->getNodeByReference('general.SSLs.SSL.' . $uuid);
        if ($node != null) {
            // return node
            $nodes = $node->getNodes();
            return ["SSL" => $nodes];
        }

        return [];
    }

    public function setAclAction($uuid)
    {
        return $this->setBase("SSL", "general.SSLs.SSL", $uuid);
    }

    public function addAclAction()
    {
        return $this->addBase("SSL", "general.SSLs.SSL");
    }

    public function delAclAction($uuid)
    {
        return $this->delBase("general.SSLs.SSL", $uuid);
    }

    public function toggleACLAction($uuid, $enabled = null)
    {
        return $this->toggleBase("general.SSLs.SSL", $uuid, $enabled);
    }


}
