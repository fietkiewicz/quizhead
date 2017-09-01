<?php
class stmRoster {
    private $dbh;
    private $config;
    private $error;
    private $students;
    
    function __construct($dbh,$config) {
        $this->dbh = $dbh;
        $this->config = $config;
        $this->students = null;
        $this->error = "";
    }
        
    function load($course) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("load roster connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT user FROM permissions WHERE class=? AND role=1"))) {
            echo $this->dbh->error("load roster prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$course)) {
            echo $this->dbh->error("load roster bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("load roster execute");
            return false;
        }
        
        $this->students = array();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            array_push($this->students,$row["user"]);
        }
        $stmt->close();

        return true;
    }
    
    function contains($student) {
        return in_array($student,$this->students);
    }
    
    function delete($course) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("clear roster connect");
            return false;
        }

        # Prepare DELETE statement
        if(!($stmt = $this->dbh->prepare("DELETE FROM permissions WHERE class=? AND role=1"))) {
            echo $this->dbh->error("clear roster prepare");
            return false;
        }
        
        if(!$stmt->bind_param("s",$course)) {
            echo $this->dbh->error("clear roster bind");
            return false;
        }

        if(!$stmt->execute()) {
            echo $this->dbh->error("clear roster execute");
            return false;
        }
        
        $stmt->close();
        return true;
    }
    
    function create($course,$roster) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("cqs connect");
            return false;
        }

        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO permissions VALUES(?,?,?)"))) {
            echo $this->dbh->error("save roster prepare");
            return false;
        }
        
        # Save roster
        foreach($roster as $id => $role) {
            if(!$stmt->bind_param("ssd",$id,$course,$role)) {
                echo $this->dbh->error("save roster bind");
                return false;
            }

            if(!$stmt->execute()) {
                echo $this->dbh->error("save roster execute");
                return false;
            }
        }
        $stmt->close();
        return true;
    }
    
    function members() {
        return $this->students;
    }
    
}
