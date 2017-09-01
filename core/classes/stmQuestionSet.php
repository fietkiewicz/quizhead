<?php
class stmQuestionSet {
    private $course;
    private $questionset;
    private $title;
    private $qsobject;
    private $dbh;
    private $config;
    private $sdao;
    private $error;
    
    function __construct($dbh,$config,$session) {
        $this->dbh = $dbh;
        $this->config = $config;
        $this->sdao = $session;
        $this->error = "";
    }
    
    function newInstance($course, $title) {
        $this->course = $course;
        $this->title = $title;
        $this->questionset = hash("sha256","sea salt ".$title.$course." rock salt");
        $this->qsobject = array();
    }
        
    function load($questionset) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("questionset.load.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM questionsets WHERE questionset=?"))) {
            return $this->dbh->error("questionset.load.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$questionset)) {
            return $this->dbh->error("questionset.load.bind");
        }
        
        # Retrieve the database row
        $res = false;
        if(!($res = $this->dbh->runPrepared($stmt))) {
            return $this->dbh->error("questionset.load.fetch");
        }
        
        # Unpack row into question object
        $row = $res->fetch_assoc();
        $this->questionset=$row["questionset"];
        $this->title = $row["title"];
        $this->course = $row["course"];
        $this->qsobject = json_decode($row["qsobject"],true);
        $stmt->close();
        return true;
    }
    
    function enumerate($course) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("cqs connect");
            return false;
        }
        

        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM questionsets WHERE course=?"))) {
            echo $this->dbh->error("cqs prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$course)) {
            echo $this->dbh->error("cqs bind");
            return false;
        }
        
        if(!$stmt->execute()) {
            echo $this->dbh->error("cqs execute");
            return false;
        }
        
        $questionSets = array();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            $questionSets[$row["questionset"]] = array("title" => $row["title"]);
        }
        $stmt->close();
        
        return $questionSets;
    }
    
    function exists() {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("questionset.exists.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM questionsets WHERE questionset=?"))) {
            return $this->dbh->error("questionset.exists.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$this->questionset)) {
            return $this->dbh->error("questionset.exists.bind");
        }
        
        # Check for existence
        $res = false;
        if(($res = $this->dbh->runPrepared($stmt)) === false) {
            return $this->dbh->error("questionset.exists.fetch");
        }
        $row = $res->fetch_assoc();
        $stmt->close();
        
        return isset($row["questionset"]);
    }
    
    function update() {
        # Check if question already exists
        if(!$this->exists()) { 
            print "questionset.update : questionset does not exist";
            return false;
        }
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("questionset.update.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("UPDATE questionsets SET title=?,course=?,qsobject=? WHERE questionset=?"))) {
            return $this->dbh->error("question.update.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("sss",$this->title,$this->course,json_encode($this->qobject))) {
            return $this->dbh->error("questionset.update.bind");
        }
        
        # Update in database
        if(!($stmt->execute())) {
            return $this->dbh->error("questionset.update.execute");
        }
        
        $stmt->close();
        return true;
    }
    
    function save() {
        # Check if question already exists, and if so perform an update instead
        if($this->exists()) { 
            return $this->update();
        }
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("questionset.save.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO questionsets VALUES(?,?,?,?)"))) {
            return $this->dbh->error("questionset.save.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("ssss",$this->course,$this->questionset,$this->title,json_encode($this->qsobject))) {
            return $this->dbh->error("questionset.save.bind");
        }
        
        # Insert to database
        if(!($stmt->execute())) {
            return $this->dbh->error("questionset.save.execute");
        }
        
        $stmt->close();
        return true;
    }
    
    function delete($id,$course) {       
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("dqs connect");
            return false;
        }
        
        # Prepare DELETE statements
        if(!($stmt1 = $this->dbh->prepare("DELETE FROM questionsets WHERE questionset=?"))) {
            echo $this->dbh->error("dqs prepare delete set");
            return false;
        }
        if(!($stmt2 = $this->dbh->prepare("DELETE FROM questions WHERE questionset=?"))) {
            echo $this->dbh->error("dqs prepare delete questions");
            return false;
        }
        
        # Bind parameters
        if(!$stmt1->bind_param("s",$id)) {
            echo $this->dbh->error("dqs bind set");
            return false;
        }
        if(!$stmt2->bind_param("s",$id)) {
            echo $this->dbh->error("dqs bind questions");
            return false;
        }
        
        # Execute DELETEs
        if(!$stmt1->execute()) {
            echo $this->dbh->error("dqs execute set");
            return false;
        }
        if(!$stmt2->execute()) {
            echo $this->dbh->error("dqs execute questions");
            return false;
        }

        # Release statement handles
        $stmt1->close();
        $stmt2->close();
        return true;
    }
    
    function questionSetId() {
        return $this->questionset;
    }
    
    function addQuestion($question) {
        return array_push($this->qsobject,$question);
    }
    
    function qsObject() {
        return $this->qsobject;
    }
    
    
}
