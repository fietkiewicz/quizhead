<?php
class stmWorker {
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
        
    function myGames() {
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        return $gameObj->enumerate($this->sdao->userName());
    }
    
    function myInfo() {
        $myInfo = array();
        $myInfo["user"]=$this->sdao->username();
        $myInfo["auth"]=$this->sdao->authorizationList();
        $myInfo["admin"]=$this->sdao->isAdmin();
        $myInfo["instructor"]=$this->sdao->isInstructor();
        $myInfo["games"]=$this->myGames();
        return $myInfo;
    }
    
    function listQuestionSets($request) {
        $course = $request["course"];
        if(!$this->sdao->isInCourse($course)) {
            echo "you are not authorized for this operation";
            return false;
        }
        
        $qs = new stmQuestionSet($this->dbh,$this->config,$this->sdao);
        return $qs->enumerate($course);
    }
    
    function deleteQuestionSet($request) {
        $course = $request["course"];
        if(!$this->sdao->isInstructorOf($course)) {
            echo "you are not authorized for this operation";
            return false;
        }
        
        $qs = new stmQuestionSet($this->dbh,$this->config,$this->sdao);
        return $qs->delete($request["id"],$request["course"],$request["sig"]);
    }
    
    function createGame($request) {
        $course = $request["course"];
        if(!$this->sdao->isInCourse($course)) {
            echo "you are not authorized for this operation";
            return false;
        }
        $owner= $this->sdao->username();
        $questions = $request["questions"];
        $players = $request["players"];
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        $gameObj->newInstance($owner, $course, $questions, 1, $players);
        return $gameObj->save();
    }
    
    function declineGame($request) {
        $id = $request["id"];
        $user = $this->sdao->username();
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        return $gameObj->decline($id,$user);
    }
    
    function endGame($request) {
        $id = $request["id"];
        $user = $this->sdao->username();
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        $gameObj->load($id);
        if($gameObj->isOwner($user)) {
            return $gameObj->end($id);
        }
        else {
            return false;
        }
    }
    
    function courseRoster($request) {
        $course = $request["course"];
        if(!$this->sdao->isInstructorOf($course)) {
            echo "you are not authorized for this operation";
            return false;
        }
        
        # Complete request with roster DAO
        $rosterObj = new stmRoster($this->dbh,$this->config);
        if($rosterObj->load($course)) {
            return $rosterObj->members();
        }
        return null;
    }
    
    function play($request) {
        $id = $request["game"];
        $user = $this->sdao->userName();
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        
        # Creating the game enforces players are in the coure roster, so here we
        # only check if they are a player
        $gameObj->load($id);
        if(!$gameObj->isPlayer($user)) {
            return false;
        }
        return $gameObj->play($id,$user);
    }
    
    function progress($request) {
        $id = $request["game"];
        $user = $this->sdao->userName();
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        
        # Creating the game enforces players are in the coure roster, so here we
        # only check if they are a player
        $gameObj->load($id);
        if(!$gameObj->isPlayer($user)) {
            return false;
        }
        return $gameObj->progress($id);
    }
    
    function answer($request) {
        $gameId = $request["game"];
        $user = $this->sdao->userName();
        $questionId = $request["question"];
        $choice = $request["choice"];
        
        # handle request with Game DAO
        $gameObj = new stmGame($this->dbh,$this->sdao,$this->config);
        
        # Creating the game enforces players are in the coure roster, so here we
        # only check if they are a player
        $gameObj->load($gameId);
        if(!$gameObj->isPlayer($user)) {
            return false;
        }
        return $gameObj->answer($gameId,$user,$questionId,$choice);
    }
    
    function process($request) {
        # Players info
        if($request["action"] == "myinfo") {
            return $this->myInfo();
        }
        
        # Create a game
        else if($request["action"] == "cgame") {
            return $this->createGame($request);
        }
        
        # Decline a game
        else if($request["action"] == "dcgame") {
            return $this->declineGame($request);
        }
        
        # End a game
        else if($request["action"] == "endgame") {
            return $this->endGame($request);
        }
        
        # List question sets
        else if($request["action"] == "qs") {
            return $this->listQuestionSets($request);
        }
        
        # Delete a question set
        else if($request["action"] == "dqs") {
            return $this->deleteQuestionSet($request);
        }
        
        # List roster
        else if($request["action"] == "roster") {
            return $this->courseRoster($request);
        }
        
        # Play game
        else if($request["action"] == "play") {
            return $this->play($request);
        }
        
        # Progress of all players in game
        else if($request["action"] == "progress") {
            return $this->progress($request);
        }
        
        # Answer a question
        else if($request["action"] == "answer") {
            return $this->answer($request);
        }
        
        else {
            return null;
        }  
    }
}
