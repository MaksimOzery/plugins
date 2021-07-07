<?php

namespace OPNsense\ProxyUserACL\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Core\Config;
use \OPNsense\Auth\AuthenticationFactory;

/**
 * Class SettingsController Handles settings related API actions for the ProxyUserACL
 * @package OPNsense\ProxySSO
 */
class UsersController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'User';
    static protected $internalModelClass = '\OPNsense\ProxyUserACL\ProxyUserACL';

    public function searchUserAction()
    {
        return $this->searchBase(
            "general.Users.User",
            array('Description', "Server", "Group", 'uuid'),
            "Description"
        );
    }

    public function getUserAction($uuid = null)
    {
        return $this->getBase("User", "general.Users.User", $uuid);
    }

    public function addUserAction()
    {
        $result = array("result" => "failed");
        if ($this->request->isPost() && $this->request->hasPost("User")) {
            $result = array("result" => "failed", "validations" => array());
            $mdl = $this->getModel();
            $node = $mdl->general->Users->User->Add();
            $post = $this->request->getPost("User");
            $post["Hex"] = self::strToHex(implode(":", array_filter(explode(",", $post["Names"]))));
            $node->setNodes($post);
            $find = $this->checkName($post);
            if ($find !== true) {
                $result["validations"]["User.Names"] = $find;
            }
            $valMsgs = $mdl->performValidation();

            foreach ($valMsgs as $field => $msg) {
                $fieldnm = str_replace($node->__reference, "User", $msg->getField());
                $result["validations"][$fieldnm] = $msg->getMessage();
            }

            if (count($result['validations']) == 0) {
                // save config if validated correctly
                $mdl->serializeToConfig();
                Config::getInstance()->save();
                unset($result['validations']);
                $result["result"] = "saved";
            }
        }
        return $result;
    }

    public function setUserAction($uuid)
    {
        if ($this->request->isPost() && $this->request->hasPost("User")) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                $node = $mdl->getNodeByReference("general.Users.User." . $uuid);
                if ($node != null) {
                    $result = array("result" => "failed", "validations" => array());

                    $post = $this->request->getPost("User");
                    $post["Hex"] = self::strToHex(implode(":", array_filter(explode(",", $post["Names"]))));
                    $node->setNodes($post);
                    $find = $this->checkName($post);
                    if ($find !== true) {
                        $result["validations"]["User.Names"] = $find;
                    }
                    $valMsgs = $mdl->performValidation();
                    foreach ($valMsgs as $field => $msg) {
                        $fieldnm = str_replace($node->__reference, "User", $msg->getField());
                        $result["validations"][$fieldnm] = $msg->getMessage();
                    }

                    if (count($result['validations']) == 0) {
                        // save config if validated correctly
                        $mdl->serializeToConfig();
                        Config::getInstance()->save();
                        $result = array("result" => "saved");
                    }
                    return $result;
                }
            }
        }
        return array("result" => "failed");
    }

    public function delUserAction($uuid)
    {
        if (($user = $this->getModel()->general->Users->User->{$uuid}) == null) {
            return ["result" => gettext("value not found")];
        }
        $id = $user->id->__toString();
        foreach (["HTTPAccesses" => "HTTPAccess", "SSLs" => "SSL", "ICAPs" => "ICAP", "Outgoings" => "Outgoing", "Delays" => "Delay"] as $group => $element) {
            foreach ($this->getModel()->general->{$group}->{$element}->getChildren() as $acl) {
                if (($users = $acl->Users) != null && isset($users->getNodeData()[$id]["selected"]) && $users->getNodeData()[$id]["selected"] == 1) {
                    return ["result" => gettext("value is used")];
                }
            }
        }
        return $this->delBase("general.Users.User", $uuid);
    }

    private function checkName($post)
    {
        $names = $post["Names"];
        $type = $post["Group"];

        $authFactory = new AuthenticationFactory();
        $server = $authFactory->listServers()[$post["Server"]];

        switch ($server["type"]) {
            case "ldap":
                if (!isset($server["ldap_binddn"])) {
                    $userdn = null;
                } else {
                    $userdn = $server["ldap_binddn"];
                }

                if (!isset($server["ldap_bindpw"])) {
                    $password = null;
                } else {
                    $password = $server["ldap_bindpw"];
                }

                $ldapBindURL = strstr($server['ldap_urltype'], "Standard") ? "ldap://" : "ldaps://";
                $ldapBindURL .= strpos($server['host'], "::") !== false ? "[{$server['host']}]" : $server['host'];
                $ldapBindURL .= !empty($server['ldap_port']) ? ":{$server['ldap_port']}" : "";
                $ldap_auth_server = $authFactory->get($server["name"]);
                if ($ldap_auth_server->connect($ldapBindURL, $userdn, $password) == false) {
                    return gettext("Error connecting to LDAP server");
                }

                foreach (explode(",", $names) as $name) {
                    $attr_user = $server["ldap_attr_user"];
                    switch ($server["ldap_attr_user"]) {
                        case "sAMAccountName":
                            $attribute = "objectClass=$type";
                            break;
                        case "cn":
                            $attribute = $type == "user" ? "objectClass=posixAccount" : "objectClass=posixGroup";
                            break;
                        case "uid":
                            $attribute = null;
                            $attribute = $type == "user" ? "objectClass=posixaccount" : "objectClass=posixgroup";
                            $attr_user = $type == "user" ? "uid" : "cn";
                            break;
                    }
                    try {
                        $users = $ldap_auth_server->searchUsers($name, $attr_user, $attribute);
                    } catch (\Exception $e) {
                        break;
                    }
                    if ($users === false || count($users) == 0) {
                        return sprintf(gettext('The %s %s does not exist'), $type, $name);
                    }
                }
                break;

            case "local":
                foreach (explode(",", $names) as $name) {
                    $find = false;
                    foreach (Config::getInstance()->object()->system->{"$type"} as $item) {
                        if ($name == (string)$item->name) {
                            $find = true;
                            break;
                        }
                    }
                    if (!$find) {
                        return sprintf(gettext('The %s %s does not exist'), $type, $name);
                    }
                }
                break;

            default:
                break;
        }
        return true;
    }

    public static function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
}
