<?php

namespace OPNsense\ProxySSO\Api;

use \OPNsense\Core\Backend;
use \OPNsense\Core\Config;
use \OPNsense\Proxy\Proxy;
use \OPNsense\ProxySSO\ProxySSO;

require_once 'Net/DNS2.php';

class ServiceController extends \OPNsense\Proxy\Api\ServiceController
{

    /**
     * show Kerberos keytab for Proxy
     * @return array
     */
    public function showkeytabAction()
    {
        $backend = new Backend();

        $response = $backend->configdRun("proxysso showkeytab");
        return ["response" => $response, "status" => "ok"];
    }

    /**
     * delete Kerberos keytab for Proxy
     * @return array
     */
    public function deletekeytabAction()
    {
        $backend = new Backend();

        $response = $backend->configdRun("proxysso deletekeytab");
        return ["response" => $response, "status" => "ok"];
    }

    /**
     * create Kerberos keytab for Proxy
     * @return array
     */
    public function createkeytabAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $mdl = new ProxySSO();
            $index = $this->request->getPost("index");
            $hostname = 'HTTP/' . Config::getInstance()->object()->system->hostname;
            $domain = $this->getDomain($index)[1];
            $realm = strtoupper($domain);
            $kerbname = (string)$mdl->KerberosHostName;
            $winver = (string)$mdl->ADKerberosImplementation == 'W2008' ? '2008' : '2003';
            $username = $this->request->getPost("admin_login");
            $pass = escapeshellarg($this->request->getPost("admin_password"));
            if (strpos($username, "@") === false) {
                $username .= "@" . strtoupper($domain);
            }
            $username = escapeshellarg($username);

            $response = $backend->configdRun("proxysso createkeytab {$hostname} {$domain} {$kerbname} {$winver} {$username} {$pass} {$realm}");
            parent::reconfigureAction();
            return ["response" => $response, "status" => "ok"];
        }

        return ["response" => []];
    }

    /**
     * test Kerberos login
     * @return array
     */
    public function testkerbloginAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $cnf = Config::getInstance()->object();
            $index = $this->request->getPost("index");
            $domain = $this->getDomain($index)[1];
            $fqdn = $cnf->system->hostname . '.' . $domain;
            $username = escapeshellarg($this->request->getPost("login"));
            $pass = escapeshellarg($this->request->getPost("password"));
            if (strpos($username, "@") === false) {
                $username .= "@" . strtoupper($domain);
            }

            $response = $backend->configdRun("proxysso testkerblogin {$username} {$pass} {$fqdn}");
            return ["response" => $response, "status" => "ok"];
        }

        return ["response" => []];
    }

    /**
     * get checklist data
     * @return array
     */
    public function getCheckListAction($index)
    {
        $backend = new Backend();
        $cnf = Config::getInstance()->object();
        list($method, $domain) = $this->getDomain($index);
        $hostname = $cnf->system->hostname . '.' . $domain;

        // LDAP
        $xpath = $cnf->xpath("//system/authserver[name=\"$method\" and type=\"ldap\"]");
        if (count($xpath)) {
            $ldap_server = $xpath[0];
        }
        $ldap_ip = null;
        $ldap_fqdn = null;
        $ldap_server_ping = ["status" => "failure"];
        if (isset($ldap_server) && !empty($ldap_server->host)) {
            if (filter_var($ldap_server->host, FILTER_VALIDATE_IP)) {
                $ldap_ip = $ldap_server->host->__toString();
            } else {
                $ldap_fqdn = $ldap_server->host->__toString();
            }

            $host_esc = escapeshellarg("{$ldap_server->host}");
            $output = ["# ping -c 1 -W 1 {$host_esc}"];
            $retval = 0;
            exec("ping -c 1 -W 1 {$host_esc}", $output, $retval);
            $ldap_server_ping = ["status" => $retval == 0 ? "ok" : "failure"];
            $ldap_server_ping["dump"] = implode("\n", $output);
        }

        // DNS
        $nameservers = preg_grep('/nameserver/', file('/etc/resolv.conf'));
        $dns_servers = [];
        foreach ($nameservers as $record) {
            $parts = explode(' ', $record);
            $dns_servers[] = trim($parts[1]);
        }
        if (count($dns_servers) > 0) {
            $dns_server = $dns_servers[0];
            $resolver = new \Net_DNS2_Resolver(['nameservers' => [$dns_servers[0]]]);
        } else {
            $resolver = null;
            $dns_server = null;
        }
        $output = ["# cat /etc/resolv.conf"];
        exec('cat /etc/resolv.conf', $output);

        // DNS: hostname
        $resolv_direct = null;
        $dns_hostname_resolution = ["status" => "failure"];
        if ($resolver != null) {
            try {
                $resp = $resolver->query($hostname, 'A');
                if (isset($resp->answer[0]->address)) {
                    $resolv_direct = $resp->answer[0]->address;
                    if (filter_var($resolv_direct, FILTER_VALIDATE_IP)) {
                        $dns_hostname_resolution = ["status" => "ok"];
                    }
                }
            } catch (\Exception $e) {
            }
            $output = ["# drill @{$dns_server} {$hostname}"];
            exec("drill @{$dns_server} {$hostname}", $output);
            $dns_hostname_resolution["dump"] = implode("\n", $output);
        }

        $dns_hostname_reverse_resolution = [
            "message" => gettext("Hostname doesn't resolved to host IP."),
            "status" => "failure"
        ];
        if ($resolver != null && !empty($resolv_direct) && filter_var($resolv_direct, FILTER_VALIDATE_IP)) {
            try {
                $resp = $resolver->query($resolv_direct, "PTR");
                if (isset($resp->answer[0]->ptrdname)) {
                    $resolv_reverse = $resp->answer[0]->ptrdname;
                    if (strtolower($resolv_reverse) == strtolower($hostname)) {
                        unset($dns_hostname_reverse_resolution["message"]);
                        $dns_hostname_reverse_resolution["status"] = "ok";
                    }
                }
            } catch (\Exception $e) {}
            $output = ["# drill -x {$resolv_direct} @{$dns_server}"];
            exec("drill -x {$resolv_direct} @{$dns_server}", $output);
            $dns_hostname_reverse_resolution["dump"] = implode("\n", $output);
        }

        // DNS: LDAP server
        ldap_dns:
        $dns_ldap_reverse_resolution = ["status" => "failure", "message" => gettext('LDAP server IP reverse lookup error. ')];
        if (empty($ldap_ip)) {
            $dns_ldap_reverse_resolution["message"] = gettext("Unknown LDAP server IP.");
        } else {
            $ldap_ip_esc = escapeshellarg($ldap_ip);
            if ($resolver != null) {
                try {
                    $resp = $resolver->query($ldap_ip, "PTR");
                    if (isset($resp->answer[0]->ptrdname)) {
                        $resolv_reverse = $resp->answer[0]->ptrdname;
                    }
                    if (!empty($ldap_fqdn) && $resolv_reverse != "{$ldap_fqdn}" && strpos($resolv_reverse,
                            ".{$ldap_fqdn}") == false) {
                        $dns_ldap_reverse_resolution["message"] = gettext('LDAP server reverse DNS lookup is not equal to LDAP server FQDN. ');
                    } else {
                        $dns_ldap_reverse_resolution["status"] = "ok";
                        unset($dns_ldap_reverse_resolution["message"]);
                        $ldap_fqdn = $resolv_reverse;
                    }
                }
                catch (\Exception $e) {}
                $output = ["# drill -x {$ldap_ip_esc} @{$dns_server}"];
                exec("drill -x {$ldap_ip_esc} @{$dns_server}", $output);
                $dns_ldap_reverse_resolution["dump"] = implode("\n", $output);
            }
        }

        $dns_ldap_resolution = ["status" => "failure", "message" => gettext('LDAP server DNS lookup error. ')];
        if (empty($ldap_fqdn)) {
            $dns_ldap_resolution["message"] = gettext('Unknown LDAP server FQDN.');
        } else {
            $ldap_fqdn_esc = escapeshellarg($ldap_fqdn);
            if ($resolver != null) {
                try {
                    $resp = $resolver->query($ldap_fqdn, 'A');
                    if (isset($resp->answer[0]->address)) {
                        $resolv = $resp->answer[0]->address;
                        if (!empty($ldap_ip) && $resolv != $ldap_ip) {
                            $dns_ldap_resolution["message"] = gettext('LDAP server DNS lookup is not equal to LDAP IP. ');
                        } else {
                            $dns_ldap_resolution["status"] = "ok";
                            unset($dns_ldap_resolution["message"]);
                            if (empty($ldap_ip)) {
                                $ldap_ip = $resolv;
                                goto ldap_dns;
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
                $output = ["# drill @{$dns_server} {$ldap_fqdn_esc}"];
                exec("drill @{$dns_server} {$ldap_fqdn_esc}", $output);
                $dns_ldap_resolution["dump"] = implode("\n", $output);
            }
        }

        $dns_external_resolution = ["status" => "failure", "message" => gettext("External hostname doesn't resolved to host IP. ")];
        if ($resolver != null) {
            try {
                $resp = $resolver->query("ya.ru", 'A');
                if (isset($resp->answer[0]->address)) {
                    $resolv = $resp->answer[0]->address;
                    if (filter_var($resolv_direct, FILTER_VALIDATE_IP)) {
                        $dns_external_resolution = ["status" => "ok"];
                    }
                }
            } catch (\Exception $e) {
            }
            $output = ["# drill @{$dns_server} ya.ru"];
            exec("drill @{$dns_server} ya.ru", $output);
            $dns_external_resolution["dump"] = implode("\n", $output);
        }

        // KERBEROS
        $kerberos_config = [];
        $config_valid = shell_exec("grep '{$domain}' /etc/krb5.conf");
        $kerberos_config["status"] = file_exists('/etc/krb5.conf') && !empty($config_valid) ? "ok" : "failure";
        if (!file_exists('/etc/krb5.conf')) {
            $kerberos_config["message"] = gettext('File /etc/krb5.conf does not exists.');
        } else {
            if (empty($config_valid)) {
                $kerberos_config["message"] = gettext('Kerberos configuration file has invalid content');
            }
        }
        $output = ["# cat /etc/krb5.conf"];
        exec('cat /etc/krb5.conf', $output);
        $kerberos_config["dump"] = implode("\n", $output);

        $keytab = [];
        $keytab["status"] = file_exists('/usr/local/etc/squid/squid.keytab') ? "ok" : "failure";
        if (!file_exists('/usr/local/etc/squid/squid.keytab')) {
            $keytab["message"] = gettext('File /usr/local/etc/squid/squid.keytab does not exists.');
        }
        $keytab["dump"] = $backend->configdRun("proxysso showkeytab");


        return [
            "hostname_" . $index => $hostname,
            "ldap_server_config_" . $index => isset($ldap_server) ? $ldap_server->name->__toString() : "",
            "ldap_server_" . $index => isset($ldap_server) ? $ldap_server->host->__toString() : "",
            "ldap_server_ping_" . $index => $ldap_server_ping,
            "dns_hostname_resolution_" . $index => $dns_hostname_resolution,
            "dns_hostname_reverse_resolution_" . $index => $dns_hostname_reverse_resolution,
            "dns_ldap_resolution_" . $index => $dns_ldap_resolution,
            "dns_ldap_reverse_resolution_" . $index => $dns_ldap_reverse_resolution,
            "dns_external_resolution_" . $index => $dns_external_resolution,
            "kerberos_config_" . $index => $kerberos_config,
            "keytab_" . $index => $keytab,
        ];
    }

    private function getDomain($index)
    {
        $count = 0;
        foreach (explode(",", (new Proxy())->forward->authentication->method->__toString()) as $method) {
            foreach (Config::getInstance()->object()->system->authserver as $server) {
                if ($server->type->__toString() == 'ldap' && $server->name->__toString() == $method && $count++ == $index) {
                    $domain_arr = [];
                    foreach (explode(",", $server->ldap_basedn->__toString()) as $element) {
                        if ($element == "") {
                            continue;
                        }
                        $domain_arr[] = explode("=", $element)[1];
                    }
                    return [$method, implode(".", $domain_arr)];
                }
            }
        }
        return null;
    }
}
