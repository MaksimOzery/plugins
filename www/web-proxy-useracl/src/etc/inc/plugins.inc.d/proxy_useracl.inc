<?php

function proxy_useracl_configure()
{
    return [
        'webproxy' => ['proxy_useracl_squid_reload:2'],
    ];
}

function proxy_useracl_squid_reload($verbose, $args)
{
    if (!in_array($args, ['restart', 'reload', 'start', 'stop'])) {
        exit;
    }
    $res = configd_run('template reload OPNsense/ProxyUserACL');
    if ($verbose) {
        printf("template reload OPNsense/ProxyUserACL: %s\n", trim($res));
    }
    $res = configd_run('proxyuseracl reload');
    if ($verbose) {
        printf("proxyuseracl reload: %s\n", trim($res));
    }
}

function proxy_useracl_xmlrpc_sync()
{
    $result = array();
    $result['id'] = 'proxyuseracl';
    $result['section'] = 'OPNsense.ProxyUserACL';
    $result['description'] = gettext('Group & User Squid ACL');
    return array($result);
}
