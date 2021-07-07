#!/usr/bin/env php
<?php

require_once('script/load_phalcon.php');

use \OPNsense\ProxyUserACL\ProxyUserACL;
use \OPNsense\Core\Config;
use \OPNsense\Proxy\Proxy;

$mdlProxyUserACL = new ProxyUserACL();

array_map('unlink', glob("/usr/local/etc/squid/ACL_useracl_*.txt"));
foreach ($mdlProxyUserACL->getNodeByReference('general.Users.User')->getChildren() as $User) {
    foreach (explode(",", (new Proxy())->forward->authentication->method->__toString()) as $method) {
        if ($User->Server->__toString() == $method) {
            $domain = [];
            foreach (Config::getInstance()->object()->system->authserver as $server) {
                if ($server->type->__toString() == 'ldap' and $server->name->__toString() == $method) {
                    foreach (explode(",", $server->ldap_basedn->__toString()) as $element) {
                        $domain[] = explode("=", $element)[1];
                    }
                }
            }
            if ($User->Group->__toString() == "user" && $domain != [] ) {
                $users = [];
                foreach (array_filter(explode(",", $User->Names->__toString())) as $user) {
                    $users[] = $user . "@" . strtoupper(implode(".", $domain));
                    $users[] = $user . "@" . implode(".", $domain);
                }
                $domain_users = implode("\n", $users);
            } else {
                $domain_users = "";
            }
            file_put_contents("/usr/local/etc/squid/ACL_useracl_" . $User->id->__toString() . ".txt",
                implode("\n", array_filter(explode(",",
                    $User->Names->__toString()))) . "\n" . $domain_users . "\n");
            break;
        }
    }
}
