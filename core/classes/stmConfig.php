<?php
class stmConfig {
    private $config;

    function __construct() {
        $this->config = array();
        $this->config["dbhost"] = "localhost";
        $this->config["dbport"] = 3306;
        $this->config["dbuser"] = 'sagamedb';
        $this->config["dbpass"] = '4$utQ0';
        $this->config["dbname"] = "sagame";
        $this->config["app"] = "https://localhost/index.php";
        $this->config["cas"] = "https://login.case.edu/cas/";
        $this->config["templates"] = "/var/php/stmCore";
    }
    
    function value($param) {
        return $this->config[$param];
    }
}
