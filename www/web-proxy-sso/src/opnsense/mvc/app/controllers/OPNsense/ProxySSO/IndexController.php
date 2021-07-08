<?php

/*
 * Copyright (C) 2017-2021 Smart-Soft
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\ProxySSO;

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
