<?php
/**
 * LDAP Authentication Handler
 * Supports both LDAP/SSO (production) and demo credentials (development)
 */

class LDAPAuthenticator {
    private $ldap_connection = null;
    private $enabled = false;
    
    public function __construct() {
        $this->enabled = LDAP_ENABLED;
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        // In development, allow demo credentials
        if (!$this->enabled) {
            return $this->authenticate_demo($username, $password);
        }
        
        return $this->authenticate_ldap($username, $password);
    }
    
    /**
     * LDAP authentication
     */
    private function authenticate_ldap($username, $password) {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username and password required.'
            ];
        }
        
        try {
            // Establish LDAP connection
            $ldap_conn = ldap_connect(LDAP_SERVER, LDAP_PORT);
            
            if (!$ldap_conn) {
                throw new Exception('Could not connect to LDAP server');
            }
            
            ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
            
            // Try to bind with user credentials
            $user_dn = 'uid=' . ldap_escape($username, null, LDAP_ESCAPE_DN) . ',' . LDAP_BASE_DN;
            
            if (@ldap_bind($ldap_conn, $user_dn, $password)) {
                ldap_close($ldap_conn);
                
                return [
                    'success' => true,
                    'username' => $username,
                    'name' => $this->get_ldap_display_name($username),
                    'message' => 'Authentication successful.'
                ];
            } else {
                ldap_close($ldap_conn);
                
                return [
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication service unavailable. Please try again later.'
            ];
        }
    }
    
    /**
     * Demo authentication (development only)
     */
    private function authenticate_demo($username, $password) {
        // Demo accounts - only for development
        $demo_accounts = [
            'admin' => password_hash('admin123', PASSWORD_BCRYPT),
            'editor' => password_hash('editor123', PASSWORD_BCRYPT),
            'viewer' => password_hash('viewer123', PASSWORD_BCRYPT),
        ];
        
        if (isset($demo_accounts[$username]) && password_verify($password, $demo_accounts[$username])) {
            return [
                'success' => true,
                'username' => $username,
                'name' => ucfirst($username),
                'role' => match($username) {
                    'admin' => 'Administrator',
                    'editor' => 'Editor',
                    'viewer' => 'Viewer',
                    default => 'User'
                },
                'message' => 'Demo authentication successful.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }
    
    /**
     * Get display name from LDAP
     */
    private function get_ldap_display_name($username) {
        try {
            $ldap_conn = ldap_connect(LDAP_SERVER, LDAP_PORT);
            if (!$ldap_conn) return $username;
            
            ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            
            // Bind as service account to read user info
            $bind_dn = 'uid=' . ldap_escape(getenv('LDAP_BIND_USER'), null, LDAP_ESCAPE_DN) . ',' . LDAP_BASE_DN;
            
            if (@ldap_bind($ldap_conn, $bind_dn, getenv('LDAP_BIND_PASSWORD'))) {
                $search = ldap_search(
                    $ldap_conn,
                    LDAP_BASE_DN,
                    '(uid=' . ldap_escape($username, null, LDAP_ESCAPE_FILTER) . ')',
                    ['displayName', 'mail']
                );
                
                if ($search) {
                    $entries = ldap_get_entries($ldap_conn, $search);
                    if ($entries['count'] > 0) {
                        $name = $entries[0]['displayname'][0] ?? $username;
                        ldap_close($ldap_conn);
                        return $name;
                    }
                }
            }
            
            ldap_close($ldap_conn);
            return $username;
        } catch (Exception $e) {
            return $username;
        }
    }
}

/**
 * Get authenticator instance
 */
function get_authenticator() {
    static $auth = null;
    if ($auth === null) {
        $auth = new LDAPAuthenticator();
    }
    return $auth;
}
?>
