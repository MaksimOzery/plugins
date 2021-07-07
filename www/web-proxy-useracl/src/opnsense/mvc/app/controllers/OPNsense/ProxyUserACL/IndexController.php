<?php

namespace OPNsense\ProxyUserACL;

use OPNsense\Core\Tools;

class IndexController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        // set page title, used by the standard template in layouts/default.volt.
        // pick the template to serve to our users.
        $this->view->mainForm = $this->getForm("main");
        $this->view->tabs = [
            [
                'name' => 'users',
                'title' => gettext('Users and groups'),
                'formDialog' => $this->getForm("dialogUsers"),
                'fields' => [
                    ['name' => 'Server', 'description' => gettext('Server'), 'width' => 0],
                    ['name' => 'Group', 'description' => gettext('Group'), 'width' => 10]
                ],
                'list' => true,
                'priority' => false,
                'active' => 'active'
            ],
            [
                'name' => 'arps',
                'title' => gettext('MAC-addresses'),
                'formDialog' => $this->getForm("dialogArps"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'srcs',
                'title' => gettext('Sources nets'),
                'formDialog' => $this->getForm("dialogSrcs"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'dsts',
                'title' => gettext('Destination nets'),
                'formDialog' => $this->getForm("dialogDsts"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'domains',
                'title' => gettext('Destination domains'),
                'formDialog' => $this->getForm("dialogDomains"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'agents',
                'title' => gettext('Browser user agents'),
                'formDialog' => $this->getForm("dialogAgents"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'mimes',
                'title' => gettext('Mime types'),
                'formDialog' => $this->getForm("dialogMimes"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'times',
                'title' => gettext('Schedules'),
                'formDialog' => $this->getForm("dialogTimes"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'remote',
                'title' => gettext('Remote Access Control Lists'),
                'formDialog' => $this->getForm("dialogEditBlacklist"),
                'fields' => [],
                'list' => true,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'ssls',
                'title' => gettext('SSL Bump'),
                'formDialog' => $this->getForm("dialogSsls"),
                'fields' => [],
                'list' => false,
                'priority' => false,
                'active' => ''
            ],
            [
                'name' => 'httpaccesses',
                'title' => gettext('HTTP access'),
                'formDialog' => $this->getForm("dialogHttpaccesses"),
                'fields' => [
                    ['name' => 'Black', 'description' => gettext('Black'), 'width' => 10],
                ],
                'list' => false,
                'priority' => true,
                'active' => ''
            ],
            [
                'name' => 'icaps',
                'title' => gettext('ICAP'),
                'formDialog' => $this->getForm("dialogIcaps"),
                'fields' => [
                    ['name' => 'Black', 'description' => gettext('Black'), 'width' => 10],
                ],
                'list' => false,
                'priority' => true,
                'active' => ''
            ],
            [
                'name' => 'outgoing',
                'title' => gettext('TCP Outgoing Address'),
                'formDialog' => $this->getForm("dialogOutgoing"),
                'fields' => [
                    ['name' => 'IPAddr', 'description' => gettext('IP Address'), 'width' => 10],
                ],
                'list' => false,
                'priority' => true,
                'active' => ''
            ],
            [
                'name' => 'delays',
                'title' => gettext('Delay pools'),
                'formDialog' => $this->getForm("dialogDelays"),
                'fields' => [
                    ['name' => 'Class', 'description' => gettext('Delay class'), 'width' => 10],
                ],
                'list' => false,
                'priority' => true,
                'active' => ''
            ]
        ];
        $this->view->locale = explode(".", $this->translator->getLocale())[0];
        $this->view->pick('OPNsense/ProxyUserACL/index');
    }
}
