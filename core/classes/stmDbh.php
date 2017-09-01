<?php

class stmDbh {
    private $dbh;
        
    function error($location) {
        echo $location." error: (" . $this->dbh->errno . ") " . $this->dbh->error;
        return false;
    }
    
    function connect($dbhost,$dbport,$dbuser,$dbpass,$database) {
        $this->dbh = new mysqli($dbhost, $dbuser, $dbpass, $database, $dbport);
        if($this->dbh->connect_errno) {
            return false;
        }
        return true;
    }

    function qconnect($config) {
        return $this->connect($config->value("dbhost"),
                              $config->value("dbport"),
                              $config->value("dbuser"),
                              $config->value("dbpass"),
                              $config->value("dbname"));
    }
    
    function prepare($query) {
        $stmt = $this->dbh->prepare($query);
        if(!$stmt) {
            return false;
        }
        return $stmt;
    }

    function bind($stmt,$param,$value) {
        return $stmt->bind_param($param,$value);
    }

    function execute($stmt) {
        return $stmt->execute();
    }

    function close($stmt) {
        $stmt->close();
    }

    function query($query) {
        $res = $this->dbh->query($query);
        if($res) {
            return $res;
        }
        else {
            return false;
        }
    }
    
    function runPrepared($stmt) {
        if(!$stmt->execute()) {
            echo $this->dbh->error("question.load.execute");
            return false;
        }
        
        return $stmt->get_result();
    }
}