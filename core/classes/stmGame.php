<?php
class stmGame {
    private $dbh;
    private $config;
    private $sdao;
    private $error;
    private $id;
    private $owner;
    private $course;
    private $questions;
    private $state;
    private $players;
    
    function __construct($dbh,$sdao,$config) {
        $this->dbh = $dbh;
        $this->sdao = $sdao;
        $this->config = $config;
        $this->error = "";
        $this->id = null;
        $this->owner = null;
        $this->course = null;
        $this->questions = null;
        $this->state = -1;
        $this->players = null;
    }
    
    function newInstance($owner,$course,$questions,$state,$players) {
        $this->owner = $owner;
        $this->course = $course;
        $this->questions = $questions;
        $this->state = $state;
        $this->players = $players;
        $this->id = hash("sha256",$this->owner.$this->course.$this->questions.time()."salt is bad for people but good for hashes");
    }
        
    function load($id) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("load game connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM games WHERE game=?"))) {
            echo $this->dbh->error("load game prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$id)) {
            echo $this->dbh->error("load game bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("load game execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if(isset($row["game"])) {
            $this->id = $row["game"];
            $this->owner = $row["user"];
            $this->course = $row["course"];
            $this->questions = $row["questions"];
            $this->state = $row["state"];
            $this->loadPlayers($id);
            $stmt->close();
            
            $this->loadPlayers($id);
            return true;
        }
        $stmt->close();
        return false;
    }
    
    function loadPlayers($id) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("load players connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM players WHERE game=?"))) {
            echo $this->dbh->error("load players prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$id)) {
            echo $this->dbh->error("load players bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("load players execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $players = array();
        while($row = $res->fetch_assoc()) {
            array_push($players,$row["player"]);
        }
        $stmt->close();
        $this->players = $players;
        return true;
    }
    
    function save() {        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("save game connect");
            return false;
        }
        
        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO games VALUES(?,?,?,?,?,NOW())"))) {
            echo $this->dbh->error("save game prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("ssssi",$this->id,$this->owner,$this->course,$this->questions,$this->state)) {
            echo $this->dbh->error("save game bind");
            return false;
        }
        
        # Create game
        $stmt->execute();
        $stmt->close();
        
        return $this->savePlayers();
    }
    
    function savePlayers() {
        # Load course roster to restrict inviatations to other memebrs of the
        # course
        $roster = new stmRoster($this->dbh, $this->config);
        if(!$roster->load($this->course)) {
            echo "save players load roster";
            return false;
        }
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("save players connect");
            return false;
        }
        
        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO players VALUES(?,?);"))) {
            echo $this->dbh->error("save game prepare");
            return false;
        }
        
        # Bind parameters
        foreach($this->players as $player) {
            # Can't add yourself as a player of your own game
            #if($player == $this->owner) {
            #    continue;
            #}
            
            # You can only add students enrolled in the course as players
            if($player != $this->owner && !$roster->contains($player)) { 
                continue;
            }
            
            if(!$stmt->bind_param("ss",$this->id,$player)) {
                echo $this->dbh->error("save game bind");
                return false;
            }

            # Add player
            $stmt->execute();
        }
        
        $stmt->close();        
        return true;
    }
    
    function isPlayer($player) {
        return in_array($player, $this->players) || $player == $this->owner;
    }
    
    function isOwner($player) {
        return $player == $this->owner;
    }
        
    function enumerate($user) {        
        $myGames = array();
        $myGames["created"] = $this->enumerateOwned($user);
        $myGames["invited"] = $this->enumerateInvited($user);
        return $myGames;
    }
    
    function enumerateOwned($user) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("enumerate created connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM games WHERE user=? ORDER BY date DESC"))) {
            echo $this->dbh->error("enumerate created prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$user)) {
            echo $this->dbh->error("enumerate created bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("enumerate created execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $gamesOwned = array();
        while($row = $res->fetch_assoc()) {
            array_push($gamesOwned,$row);
        }
        $stmt->close();
        return $gamesOwned;
    }
    
    function enumerateInvited($user) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("enumerate invited connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM players CROSS JOIN games ON (players.player=? AND games.game=players.game) ORDER BY games.date DESC;"))) {
            echo $this->dbh->error("enumerate invited prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$user)) {
            echo $this->dbh->error("enumerate invited bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("enumerate invited execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $gamesInvited = array();
        while($row = $res->fetch_assoc()) {
            array_push($gamesInvited,$row);
        }
        $stmt->close();
        return $gamesInvited;
    }
    
    function decline($id,$user) {        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("decline game connect");
            return false;
        }
        
        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("DELETE FROM players WHERE game=? AND player=?"))) {
            echo $this->dbh->error("decline game prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("ss",$id,$user)) {
            echo $this->dbh->error("decline game bind");
            return false;
        }
        
        # Execute statement
        if(!$stmt->execute()) {
            echo $this->dbh->error("decline game execute");
            return false;
        }
        
        $stmt->close();
        return true;
    }
    
    function archive($id) {
        $progress = $this->progress($id);
        $archiveObject = array();
        $archiveObject["progress"] = $progress;
        $archiveObject["id"] = $this->id;
        $archiveObject["owner"] = $this->owner;
        $archiveObject["questions"] = $this->questions;
        $archiveObject["course"] = $this->course;
        $archiveObject["players"] = $this->players;
        $json = json_encode($archiveObject);
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("archive game connect");
            return false;
        }
        
        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO archive VALUES(?,?,?)"))) {
            echo $this->dbh->error("archive game prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("sss",$this->id,$this->course,$json)) {
            echo $this->dbh->error("archive game bind");
            return false;
        }
        
        # Create game
        $stmt->execute();
        $stmt->close();
        
        return true;
    }
    
    function end($id) {
        # Archive the game for research purposes before closing it
        $this->archive($id);
        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("decline game connect");
            return false;
        }
        
        # Prepare DELETE statements
        if(!($stmt1 = $this->dbh->prepare("DELETE FROM players WHERE game=?"))) {
            echo $this->dbh->error("end game prepare 1");
            return false;
        }
        if(!($stmt2 = $this->dbh->prepare("DELETE FROM progress WHERE game=?"))) {
            echo $this->dbh->error("end game prepare 2");
            return false;
        }
        if(!($stmt3 = $this->dbh->prepare("DELETE FROM games WHERE game=?"))) {
            echo $this->dbh->error("end game prepare 3");
            return false;
        }
        
        # Bind parameters
        if(!$stmt1->bind_param("s",$id)) {
            echo $this->dbh->error("end game bind 1");
            return false;
        }
        if(!$stmt2->bind_param("s",$id)) {
            echo $this->dbh->error("end game bind 2");
            return false;
        }
        if(!$stmt3->bind_param("s",$id)) {
            echo $this->dbh->error("end game bind 3");
            return false;
        }
        
        # Execute statement
        if(!$stmt1->execute()) {
            echo $this->dbh->error("end game execute 1");
            return false;
        }
        if(!$stmt2->execute()) {
            echo $this->dbh->error("end game execute 2");
            return false;
        }
        if(!$stmt3->execute()) {
            echo $this->dbh->error("end game execute 3");
            return false;
        }
        
        $stmt1->close();
        $stmt2->close();
        $stmt3->close();
        return true;
    }
    
    function inProgress($id,$user) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("inProgress connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM progress WHERE game=? AND player=?"))) {
            echo $this->dbh->error("inProgress prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("ss",$id,$user)) {
            echo $this->dbh->error("inProgress bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("progress execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if(isset($row["game"])) {
            return $row;
        }
        else {
            return false;
        }
    }
    
    function start($id,$user) {        
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("start game connect");
            return false;
        }
        
        # Prepare INSERT statement
        if(!($stmt = $this->dbh->prepare("INSERT INTO progress VALUES(?,?,?,?)"))) {
            echo $this->dbh->error("start game prepare");
            return false;
        }
        
        # Bind parameters
        $qn = 1;
        $score = 0;
        if(!$stmt->bind_param("ssii",$id,$user,$qn,$score)) {
            echo $this->dbh->error("start game bind");
            return false;
        }
        
        # Create game
        if(!$stmt->execute()) {
            echo $this->dbh->error("start game prepare");
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }
    
    function remaining($qs,$qn) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("remaining connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT question,sort,qobject FROM questions WHERE questionset=? AND sort >= ? ORDER BY sort ASC"))) {
            echo $this->dbh->error("remaining prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("si",$qs,$qn)) {
            echo $this->dbh->error("remaining bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("remaining execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $remaining = array();
        while($row = $res->fetch_assoc()) {
            $q = json_decode($row['qobject'],true);
            $q["id"] = $row["question"];
            array_push($remaining,$q);
        }
        $stmt->close();
        return $remaining;
    }
    
    function play($id,$user) {
        # Either determine where the player quit last time, or if this is the
        # first time they played the game create a new progress entry
        $progress = $this->inProgress($id, $user);
        $qn = 0;
        if($progress === false) {
            if(!$this->start($id,$user)) {
                return false;
            }
            $qn = 1;
        }
        else {
            $qn = $progress["qn"];
        }
        
        # Load the details of the game
        $this->load($id);
        
        # Return the questions that remain
        return $this->remaining($this->questions, $qn);
    }
    
    function mapChoice($choice) {
        return chr(65+$choice);
    }
    
    function answer($gameId,$user,$questionId,$choice) {
        $details = $this->inProgress($gameId, $user);
        $question = new stmQuestion($this->dbh, $this->config);
        $question->load($questionId);
        $mchoice = $this->mapChoice($choice);
        
        if($details["qn"] == $question->sort()) {
            if($mchoice == $question->answer()) {
                return $this->updateProgress($gameId,$user,$details["qn"]+1,$details["score"]+1);
            }
            else {
                return $this->updateProgress($gameId,$user,$details["qn"]+1,$details["score"]);
            }
        }
        else {
            return false;
        }
    }
    
    function updateProgress($game,$user,$qn,$score) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("update progress connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("UPDATE progress SET qn=?,score=? WHERE game=? AND player=?"))) {
            echo $this->dbh->error("update progress prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("iiss",$qn,$score,$game,$user)) {
            echo $this->dbh->error("update progress bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("update progress execute");
            return false;
        }
        
        $stmt->close();
        return true;
    }
    
    function progress($id) {
        # Connect to database
        if(!$this->dbh->qconnect($this->config)) {
            echo $this->dbh->error("progress connect");
            return false;
        }
        
        # Prepare SELECT statement
        if(!($stmt = $this->dbh->prepare("SELECT * FROM progress WHERE game=?"))) {
            echo $this->dbh->error("progress prepare");
            return false;
        }
        
        # Bind parameters
        if(!$stmt->bind_param("s",$id)) {
            echo $this->dbh->error("progress bind");
            return false;
        }
        
        # Execute
        if(!$stmt->execute()) {
            echo $this->dbh->error("progress execute");
            return false;
        }
        
        $res = $stmt->get_result();
        $progress = array();
        while($row = $res->fetch_assoc()) {
            $player = array();
            $player["player"] = $row["player"];
            $player["qn"] = $row["qn"];
            $player["score"] = $row["score"];
            array_push($progress,$player);
        }
        $stmt->close();
        return $progress;
    }
    
}
