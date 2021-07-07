<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Core\Config;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\ProxyUserACL\ProxyUserACL;
use \OPNsense\Core\Tools;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class DelaysController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Delays';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';
    /**
     *
     * search ACL
     * @return array
     */
    public function searchACLAction()
    {
        $this->sessionClose();

        $cert = true;
        try {
            Tools::check_certificate("NetPolice");
        } catch (\Exception $ex) {
            $cert = false;
        }

        $mdlProxyUserACL = new ProxyUserACL();
        $grid = new UIModelGrid($mdlProxyUserACL->general->Delays->Delay);
        $columns = ["Aggregate", "Domains", "Agents", "Times", "Mimes", "Srcs", "Dsts", "Arps", "remoteACLs"];
        $ret = $grid->fetchBindRequest($this->request, array_merge($columns, ["enabled", "Class", "Individual", "Network", "User", "Tagrate", "Users", "NetPolice", "IPAddr", "Priority", "uuid"]),
            "Priority");
        foreach ($ret["rows"] as &$row) {
            $visible = [];
            foreach ($columns as $column) {
                if ($row[$column] != "") {
                    $visible[] = $row[$column];
                }
            }

            $user_list = [];
            foreach (explode(",", $row["Users"]) as $user_id) {
                if ($user_id == "") {
                    continue;
                }
                foreach ($mdlProxyUserACL->general->Users->User->getChildren() as $user) {
                    if ($user->id->__toString() == $user_id) {
                        $user_list[] = $user->Description->__toString();
                    }
                }
            }

            $category_list = [];

            if ($cert) {
                $categories = \OPNsense\ProxyUserACL\Tools::getCategories();
                foreach (explode(",", $row["NetPolice"]) as $category) {
                    if ($category == "") {
                        continue;
                    }
                    if (isset($categories[$category])) {
                        $category_list[] = $categories[$category];
                    }
                }
            }
            if ($user_list != []) {
                $visible[] = implode(",", $user_list);
            }

            if ($category_list != []) {
                $visible[] = implode(",", $category_list);
            }

            $row["Visible"] = implode("|", $visible);
        }
        return $ret;
    }

    /**
     *
     * add ACL
     * @return array
     */
    public function addACLAction()
    {
        if (!$this->request->isPost() || !$this->request->hasPost("Delay")) {
            return ["result" => "failed"];
        }

        $result = ["result" => "failed", "validations" => []];
        $mdlProxyUserACL = new ProxyUserACL();
        $post = $this->request->getPost("Delay");

        $count = count($mdlProxyUserACL->general->Delays->Delay->getNodes());
        if ($post["Priority"] > $count) {
            $post["Priority"] = (string) $count;
        }
        foreach ($mdlProxyUserACL->general->Delays->Delay->sortedBy("Priority", true) as $acl) {
            $key = $acl->getAttributes()["uuid"];
            $priority = (string)$mdlProxyUserACL->general->Delays->Delay->{$key}->Priority;
            if ($priority < $post["Priority"]) {
                break;
            }
            $mdlProxyUserACL->general->Delays->Delay->{$key}->Priority = (string)($priority + 1);
        }
        $node = $mdlProxyUserACL->general->Delays->Delay->Add();
        $node->setNodes($post);
        $valMsgs = $mdlProxyUserACL->performValidation();

        foreach ($valMsgs as $field => $msg) {
            $fieldnm = str_replace($node->__reference, "Delay", $msg->getField());
            $result["validations"][$fieldnm] = $msg->getMessage();
        }

        if (count($result['validations']) > 0) {
            return $result;
        }

        // save config if validated correctly
        $mdlProxyUserACL->serializeToConfig();
        Config::getInstance()->save();
        return ["result" => "saved"];
    }

    /**
     *
     * get ACL
     * @return array
     */
    public function getACLAction($uuid = null)
    {
        $cert = true;
        try {
            Tools::check_certificate("NetPolice");
        } catch (\Exception $ex) {
            $cert = false;
        }

        $mdlProxyUserACL = new ProxyUserACL();
        if ($cert) {
            $categories = \OPNsense\ProxyUserACL\Tools::getCategories();
        }
        if ($uuid == null) {
            // generate new node, but don't save to disc
            $node = $mdlProxyUserACL->general->Delays->Delay->add();
            $nodes = $node->getNodes();
            foreach ($mdlProxyUserACL->general->Users->User->getChildren() as $uuid => $user) {
                $nodes["Users"][$user->id->__toString()] = [
                    "value" => $user->Description->__toString(),
                    "selected" => "0"
                ];
            }
            if ($cert) {
                foreach ($categories as $key => $category) {
                    $nodes["NetPolice"][$key] = ["value" => $category, "selected" => "0"];
                }
            }
            return ["Delay" => $nodes];
        }

        $node = $mdlProxyUserACL->getNodeByReference('general.Delays.Delay.' . $uuid);
        if ($node != null) {
            // return node
            $nodes = $node->getNodes();
            foreach ($mdlProxyUserACL->general->Users->User->getChildren() as $uuid => $user) {
                if (isset($nodes["Users"][$user->id->__toString()])) {
                    $nodes["Users"][$user->id->__toString()] = ["value" => $user->Description->__toString()];
                } else {
                    $nodes["Users"][$user->id->__toString()] = [
                        "value" => $user->Description->__toString(),
                        "selected" => "0"
                    ];
                }
            }
            if ($cert) {
                foreach ($categories as $key => $category) {
                    if (isset($nodes["NetPolice"][$key])) {
                        $nodes["NetPolice"][$key] = ["value" => $category];
                    } else {
                        $nodes["NetPolice"][$key] = ["value" => $category, "selected" => "0"];
                    }
                }
            } else {
                $nodes["NetPolice"] = [];
            }
            return ["Delay" => $nodes];
        }

        return [];
    }

    /**
     *
     * set ACL
     * @return array
     */
    public function setACLAction($uuid)
    {
        if (!$this->request->isPost() || !$this->request->hasPost("Delay")) {
            return ["result" => "failed"];
        }

        $mdlProxyUserACL = new ProxyUserACL();
        if ($uuid == null) {
            return ["result" => "failed"];
        }

        $node = $mdlProxyUserACL->getNodeByReference('general.Delays.Delay.' . $uuid);
        if ($node == null) {
            return ["result" => "failed"];
        }

        $result = ["result" => "failed", "validations" => []];
        $ACLInfo = $this->request->getPost("Delay");
        $old_priority = (string)$node->Priority;
        $new_priority = $ACLInfo["Priority"];

        if ($new_priority < $old_priority) {
            if ($new_priority < 0) {
                $new_priority = 0;
            }

            foreach ($mdlProxyUserACL->general->Delays->Delay->sortedBy("Priority", true) as $acl) {
                $key = $acl->getAttributes()["uuid"];
                $priority = (string)$mdlProxyUserACL->general->Delays->Delay->{$key}->Priority;
                if ($priority < $new_priority) {
                    break;
                }
                if ($priority >= $old_priority) {
                    continue;
                }
                $mdlProxyUserACL->general->Delays->Delay->{$key}->Priority = (string)($priority + 1);
            }
        } elseif (($new_priority > $old_priority)) {
            $count = count($mdlProxyUserACL->general->Delays->Delay->getNodes());
            if ($new_priority >= $count) {
                $new_priority = $count - 1;
                $ACLInfo["Priority"] = (string)$new_priority;
            }
            foreach ($mdlProxyUserACL->general->Delays->Delay->sortedBy("Priority") as $acl) {
                $key = $acl->getAttributes()["uuid"];
                $priority = (string)$mdlProxyUserACL->general->Delays->Delay->{$key}->Priority;
                if ($priority > $new_priority) {
                    break;
                }
                if ($priority <= $old_priority) {
                    continue;
                }
                $mdlProxyUserACL->general->Delays->Delay->{$key}->Priority = (string)($priority - 1);
            }
        }
        $node->setNodes($ACLInfo);
        $valMsgs = $mdlProxyUserACL->performValidation();
        foreach ($valMsgs as $field => $msg) {
            $fieldnm = str_replace($node->__reference, "Delay", $msg->getField());
            $result["validations"][$fieldnm] = $msg->getMessage();
        }

        if (count($result['validations']) > 0) {
            return $result;
        }

        // save config if validated correctly
        $mdlProxyUserACL->serializeToConfig();
        Config::getInstance()->save();
        return ["result" => "saved"];
    }

    /**
     *
     * del ACL
     * @return array
     */
    public function delACLAction($uuid)
    {
        $ret = $this->delBase("general.Delays.Delay", $uuid);
        $mdl = $this->getModel();
        $this->repackPriority($mdl);
        $mdl->serializeToConfig();
        Config::getInstance()->save();
        return $ret;
    }

    public function toggleACLAction($uuid, $enabled = null)
    {
        return $this->toggleBase("general.Delays.Delay", $uuid, $enabled);
    }

    /**
     *
     * Change ACL priority
     * @param $uuid item unique id
     * @return array
     */
    public function updownACLAction($uuid)
    {

        if (!$this->request->isPost() || $uuid == null || !$this->request->hasPost("command")) {
            return ["result" => "failed"];
        }

        $mdlProxyUserACL = new ProxyUserACL();
        $count = $this->repackPriority($mdlProxyUserACL);
        $nodes = $mdlProxyUserACL->general->Delays->Delay->getNodes();
        $acl = $nodes[$uuid];
        $priority = $acl["Priority"];
        switch ($this->request->getPost("command")) {
            case "up":
                $new_priority = $priority - 1;
                if ($new_priority < 0) {
                    return ["result" => "success"];
                }
                break;

            case "down":
                $new_priority = $priority + 1;
                if ($new_priority >= $count) {
                    return ["result" => "success"];
                }
                break;

            default:
                return ["result" => "failed"];
        }
        foreach ($nodes as $key => $node) {
            if ($node["Priority"] == $new_priority) {
                $mdlProxyUserACL->general->Delays->Delay->{$key}->Priority = (string)$priority;
                $mdlProxyUserACL->general->Delays->Delay->{$uuid}->Priority = (string)$new_priority;
                $mdlProxyUserACL->serializeToConfig();
                Config::getInstance()->save();
                return ['result' => 'success'];
            }
        }
    }

    private function repackPriority($mdlProxyUserACL)
    {
        $count = 0;
        foreach ($mdlProxyUserACL->general->Delays->Delay->sortedBy("Priority") as $node) {
            $key = $node->getAttributes()["uuid"];
            $mdlProxyUserACL->general->Delays->Delay->{$key}->Priority = (string)$count++;
        }
        return $count;
    }
}
