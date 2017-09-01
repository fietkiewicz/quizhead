<?php
require_once 'stmCas.php';

class stmSecurity {
    private $config;
    private $dbh;
    private $error;
    private $sdao;
    
    function __construct($config, $session, $dbh) {
        $this->config = $config;
        $this->sdao = $session;
        $this->dbh = $dbh;
        $this->error = "";
    }
        
    function authenticate() {
        
        # Check if the user has already authenticated
        if($this->sdao->hasUsername()) {
            return true;
        }
        
        # Otherwise, use CAS for login
        $cas = new stmCas($this->config,$this->sdao);
        $status = $cas->authenticate();
        if($status == 3) {
            if(!headers_sent()) {
                print $cas->error();
            }
            return false;
        }
        else if($status == 2) {
            return false;
        }
        else {
            return true;
        }
    }
    
    function authorize() {
        # If we already loaded the list into the session,
        if($this->sdao->hasAuthorizationList()) {
            return true;
        }
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return false;
        }
        
        # Load authorization list
        $authlist = array();
        $res = $this->dbh->query("SELECT * FROM permissions WHERE user=\"".$this->sdao->username()."\"");
        while($row = $res->fetch_assoc()) {
            if($row["class"] == "administration") {
                $this->sdao->setAdmin();
            }
            else {
                $role = intval($row["role"]);
                if($role === 0) {
                    $this->sdao->setInstructor();
                }
                $authlist[$row["class"]] = $role;
            }
        }
                
        # Add to session and return
        $this->sdao->setAuthorizationList($authlist);
        return true;
    }
    
    function reauthorize() {
        # Delete current list
        $this->sdao->deleteAuthorizationList();
        return $this->authorize();
    }
    
    function isRequestingReauthorize() {
        $reauthorize = filter_input(INPUT_GET,"reauthorize");
        return isset($reauthorize) && $reauthorize == 1;
    }
    
    function ready() {
        return $this->authenticate() && $this->authorize();
    }
}
