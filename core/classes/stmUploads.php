<?php

class stmUploads {
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
        
    function processRoster($course,$file) {
        $localName = $file['tmp_name'];
        $handle = fopen($localName,"r");
        if(!$handle) {
            return false;
        }
        
        $roster = array();
        while(($line = fgets($handle)) !== false) {
            $tline = trim($line);
            if($tline !== $this->sdao->userName()) {
                $roster[$tline] = 1;
            }
        }
        
        # Complete request with Roster DAO
        $rosterObj = new stmRoster($this->dbh,$this->config);
        if($rosterObj->delete($course)) {
            return $rosterObj->create($course,$roster);
        }
        return false;
        
    }
    
    function processQuestions($course,$title,$file) {
        $localName = $file['tmp_name'];
        $handle = fopen($localName,"r");
        if(!$handle) {
            return false;
        }
        
        $questionset = new stmQuestionSet($this->dbh,$this->config,$this->sdao);
        $questionset->newInstance($course,$title);
        $i = 1;
        while(($line = fgets($handle)) !== false) {
            $tline = trim($line);
            $values = explode("\t",$tline);
            $questionStatement = array_shift($values);
            $answer = array_shift($values);
            $choices = array();
            foreach($values as $choice) {
                if(preg_match('/[A-Z]|[a-z]|[0-9]/', $choice) === 1) {    
                    array_push($choices,$choice);
                }
            }

            $question = new stmQuestion($this->dbh,$this->config);
            $question->newInstance($questionset->questionSetId(), $questionStatement, $answer, $i, $choices);
            $question->save();
            $questionset->addQuestion($question->questionId());
            $i++;
        }
        $questionset->save();
        return true;
    }
    
    function process() {
        if(!isset($_FILES["data"]) || $_FILES["data"] == null ) {
            return true;
        }
        $file = $_FILES["data"];
        
        # Get course and check authority
        $course = filter_input(INPUT_POST,"course");
        if(!$this->sdao->isInstructorOf($course)) {
            echo "You are not authorized for this operation";
            return false;
        }
        
        # Map type to handler
        $type = filter_input(INPUT_POST,"type");
        if($type == "roster") {
            return $this->processRoster($course,$file);
        }
        else if($type == "questions") {
            $title = filter_input(INPUT_POST,"title");
            return $this->processQuestions($course,$title,$file);
        }
        else {
            echo "Invalid type for upload";
            return false;
        }
    }
}
