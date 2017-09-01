<?php

class stmCas {
    private $config;
    private $error;
    private $sdao;
    
    function __construct($config,$session) {
        $this->config = $config;
        $this->sdao = $session;
        $this->error = "";
    }
    
    function error() {
        return $this->error;
    }
    
    function collectTicket($ticket) {
        $service = urlencode($this->config->value("app")."?rcas=1");
        $redirect = $this->config->value("cas")."validate?service=".$service."&ticket=".$ticket;
        $validate = file_get_contents($redirect);
        $values = explode("\n",$validate);
        if(count($values) > 0 && $values[0] == "yes") {
            $this->sdao->setUsername($values[1]);
            
            # Redirect a final time to strip CAS query parameters from the 
            # address bar
            header("Location: ".$this->config->value("app"));
            return 1;
        }
        else {
            $this->error = "The CAS server rejected the ticket";
            return 3;
        }
    }
    
    function processCasRedirect($ticket) {
        if($ticket) {
            return $this->collectTicket($ticket);
        }
        else {
            $this->error = "CAS didn't send you back here with a ticket";
            return 3;
        }
    }
    
    function redirectToCas() {
        $service = urlencode($this->config->value("app")."?rcas=1");
        $redirect = $this->config->value("cas")."login?service=".$service;
        header("Location: ".$redirect);
        return 2;
    }
    
    # Look at the flow diagram here to get the gist of how CAS works
    # http://jasig.github.io/cas/4.0.x/protocol/CAS-Protocol.html
    function authenticate() {        
        # Return flag indicates we have been directed back by CAS server with or
        # without a ticket. Auto-logins via CAS need something like this to
        # avoid looping if the user cancels logging into CAS and comes back to
        # the app with no ticket.
        $rcas = filter_input(INPUT_GET,"rcas");
        $ticket = filter_input(INPUT_GET,"ticket");
               
        # User redirected back here from CAS
        if($rcas) {
            return $this->processCasRedirect($ticket);
        }
        
        # User arriving for first time
        else {
            return $this->redirectToCas();
        }
    }
}
    
