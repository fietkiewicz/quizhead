<?php
class stmQuestion {
    private $questionset;
    private $question;
    private $answer;
    private $sort;
    private $qobject;
    private $dbh;
    private $config;
    private $error;
    
    function __construct($dbh,$config) {
        $this->dbh = $dbh;
        $this->config = $config;
        $this->error = "";
    }
    
    function newInstance($questionset, $question, $answer, $sort, $choices) {
        $this->questionset = $questionset;
        $this->question = hash("sha256","salt and ".$questionset.$question.$answer." pepper");
        $this->answer = $answer;
        $this->sort = $sort;
        $this->qobject = array("question"=>$question,
                               "choices" => $choices);
    }
    
    function load($question) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("question.load.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM questions WHERE question=?"))) {
            return $this->dbh->error("question.load.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$question)) {
            return $this->dbh->error("question.load.bind");
        }
        
        # Retrieve the database row
        $res = false;
        if(!($res = $this->dbh->runPrepared($stmt))) {
            return $this->dbh->error("question.load.fetch");
        }
        
        # Unpack row into question object
        $row = $res->fetch_assoc();
        $this->questionset=$row["questionset"];
        $this->question=$row["question"];
        $this->answer = $row["answer"];
        $this->sort = $row["sort"];
        $this->qobject = json_decode($row["qobject"],true);
        $stmt->close();
        return true;
    }
    
    function exists() {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("question.exists.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM questions WHERE questionset=? AND question=?"))) {
            return $this->dbh->error("question.exists.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("ss",$this->questionset,$this->question)) {
            return $this->dbh->error("question.exists.bind");
        }
        
        # Check for existence
        $res = false;
        if(($res = $this->dbh->runPrepared($stmt)) === false) {
            return $this->dbh->error("question.exists.fetch");
        }
        $row = $res->fetch_assoc();
        $stmt->close();
        
        return isset($row["question"]);
    }
    
    function update() {
        # Check if question already exists
        if(!$this->exists()) { 
            print "question.update : question does not exist";
            return false;
        }
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            return $this->dbh->error("question.update.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("UPDATE questions SET answer=?,sort=?,qobject=? WHERE questionset=? AND question=?"))) {
            return $this->dbh->error("question.update.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("sdsss",$this->answer,$this->sort,json_encode($this->qobject),$this->questionset,$this->question)) {
            return $this->dbh->error("question.save.bind");
        }
        
        # Update in database
        if(!($stmt->execute())) {
            return $this->dbh->error("question.update.execute");
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
            return $this->dbh->error("question.save.connect");
        }
        
        # Prepare statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO questions VALUES(?,?,?,?,?)"))) {
            return $this->dbh->error("question.save.prepare");
        }
        
        # Bind parameters
        if(!$stmt->bind_param("sssds",$this->questionset,$this->question,$this->answer,$this->sort,json_encode($this->qobject))) {
            return $this->dbh->error("question.save.bind");
        }
        
        # Insert to database
        if(!($stmt->execute())) {
            return $this->dbh->error("question.save.execute");
        }
        
        $stmt->close();
        return true;
    }
    
    function questionSet() {
        return $this->questionset;
    }
    
    function questionId() {
        return $this->question;
    }
    
    function answer() {
        return $this->answer;
    }
    
    function sort() {
        return $this->sort;
    }
    
    function qObject() {
        return $this->qobject;
    }    
}
