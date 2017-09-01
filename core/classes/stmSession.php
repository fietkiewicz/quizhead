<?php
class stmSession {
    private $db = null;
    
    function __construct() {
        $this->db = array();
    }
    
    function delete($key) {
        unset($_SESSION[$key]);
    }
    
    function set($key,$value) {
        $_SESSION[$key] = $value;
    }
    
    function get($key) {
        return $_SESSION[$key];
    }
    
    function has($key) {
        return isset($_SESSION[$key]);
    }
    
    function username() {
        return $this->get("username");
    }
    
    function setUsername($name) {
        $this->set("username",$name);
    }
    
    function hasUsername() {
        return $this->has("username");
    }
    
    function authorizationList() {
        return $this->get("authlist");
    }
    
    function setAuthorizationList($authlist) {
        $this->set("authlist",$authlist);
    }
    
    function hasAuthorizationList() {
        return $this->has("authlist");
    }
    
    function deleteAuthorizationList() {
        return $this->delete("authlist");
    }
    
    function setAdmin() {
        $this->set("admin",true);
    }
    
    function setInstructor() {
        $this->set("instructor",true);
    }
    
    function isAdmin() {
        return $this->get("admin");  
    }
    
    function isInstructor() {
        return $this->get("instructor");  
    }
    
    function isInstructorOf($course) {
        $al = $this->get("authlist");
        return isset($al[$course]) && $al[$course] === 0;
    }
    
    function isInCourse($course) {
        $al = $this->get("authlist");
        return isset($al[$course]);
    }
    
    function dump() {
        print_r($_SESSION);
    }
}
