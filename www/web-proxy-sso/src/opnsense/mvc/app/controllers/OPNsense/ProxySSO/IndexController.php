<?php

namespace OPNsense\ProxySSO;

use \OPNsense\Core\Config;
use OPNsense\Proxy\Proxy;

class IndexController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->title = gettext("Web Proxy Single Sign-On");
        $this->view->pick('OPNsense/ProxySSO/index');
        $this->view->generalForm = $this->getForm("general");
        $mdlProxy = new Proxy();
        $ldaps = [];
        $count = 0;
        $forms = [];
        $form_arr = [
            "testingCreateForm" => "testing_create",
            "testingTestForm" => "testing_test",
            "checkListForm" => "checklist"
        ];

        foreach ($form_arr as $form_name => $xml) {
            $forms[$form_name] = [];
        }

        foreach (explode(",", $mdlProxy->forward->authentication->method->__toString()) as $method) {
            foreach (Config::getInstance()->object()->system->authserver as $server) {
                if ($server->type->__toString() == 'ldap' and $server->name->__toString() == $method) {
                    foreach ($form_arr as $form_name => $xml) {
                        $form = $this->getForm($xml);
                        foreach ($form as &$field) {
                            if (isset($field["id"])) {
                                $field["id"] .= "_" . $count;
                            }
                        }
                        $forms[$form_name][] = $form;
                    }
                    $count++;
                    $ldaps[] = $method;
                }
            }
        }
        foreach ($form_arr as $form_name => $xml) {
            $this->view->{$form_name} = $forms[$form_name];
        }
        $this->view->ldaps = $ldaps;
    }
}

