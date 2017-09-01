<?php

class stmTransport {
    private $protocol;
    private $method;
    
    function __construct() {
        # Use filters per reccomended best practice rather than access super 
        # glob $_SERVER["SERVER_PROTOCOL"] directly.
        $rawProtocol = filter_input(INPUT_SERVER,"SERVER_PROTOCOL");
        if($rawProtocol) {
            $this->protocol = $rawProtocol;
        }
        else {
            $this->protocol = "UNKNOWN";
        }
        
        # Use filters per reccomended best practice rather than access super 
        # glob $_SERVER["REQUEST_METHOD"] directly.
        $rawMethod = filter_input(INPUT_SERVER,"REQUEST_METHOD");
        if($rawMethod) {
            $this->method = $rawMethod;
        }
        else {
            $this->method = "UNKNOWN";
        }
    }
    
    # If request method is POST, we expect the JSON payload to be in a 
    # form variable "j"
    function readRequestPost() {
        $data = filter_input(INPUT_POST,"j");
        if(!$data) {
            return null;
        }
        return json_decode($data,true);
    }
    
    # If request method is PUT, we expect the JSON payload to be the body of the
    # request
    function readRequestPut() {
        $data = "";
        $fh = fopen("php://input","r");
        while($buffer = fread($fh,1024)) {
            $data .= $buffer;
        }
        fclose($fh);
        return json_decode($data,true);
    }
    
    # Read request using appropriate help function
    function readRequest() {
        if($this->method == "POST") {
            return $this->readRequestPost();
        }
        else if($this->method == "PUT") {
            return $this->readRequestPut();
        }
        else {
            return null;
        }
    }
    
    function sendResponse($object) {
        header("Content-type: application/json");
        print json_encode($object);
    }
    
    function sendError($code,$message) {
        # Good example of setting HTTP status line as header here 
        # http://php.net/manual/en/function.http-response-code.php#107261
        header($this->protocol.' '.$code.' '.$message);
    }
    
}
