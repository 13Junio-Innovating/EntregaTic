<?php
class LdapService
{
    private $host = 'zeus';
    private $domain = 'costao.com.br';
    private $basedn = 'dc=costao,dc=com,dc=br';
    private $group_admin = 'TIC_ADMIN';
    private $group_user = 'TIC_USER';

    public function authenticate($user, $password)
    {
        $ad = ldap_connect("ldap://{$this->host}.{$this->domain}");
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ad, "{$user}@{$this->domain}", $password)) {
            return ['status' => false];
        }

        $filter = "(sAMAccountName={$user})";
        $attributes = ['displayname', 'mail', 'memberof'];
        $result = ldap_search($ad, $this->basedn, $filter, $attributes);
        $entries = ldap_get_entries($ad, $result);

        if ($entries['count'] <= 0) {
            return ['status' => false];
        }

        $entry = $entries[0];
        $name = $entry['displayname'][0] ?? '';
        $mail = $entry['mail'][0] ?? '';
        $grupo = 'usuario';

        if (isset($entry['memberof'])) {
            foreach ($entry['memberof'] as $value) {
                if (is_string($value)) {
                    if (stripos($value, "CN={$this->group_admin}") !== false) {
                        $grupo = $this->group_admin;
                        break;
                    }
                    if (stripos($value, "CN={$this->group_user}") !== false) {
                        $grupo = $this->group_user;
                    }
                }
            }
        }

        return [
            'status' => true,
            'name' => $name,
            'mail' => $mail,
            'grupo' => $grupo
        ];
    }
}
