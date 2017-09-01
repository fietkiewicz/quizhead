<?php
    require_once 'classes/stmConfig.php';
    require_once 'classes/stmDbh.php';
    require_once 'classes/stmTransport.php';
    require_once 'classes/stmSession.php';
    require_once 'classes/stmWorker.php';
    require_once 'classes/stmSecurity.php';
    require_once 'classes/stmUploads.php';
    require_once 'classes/stmQuestion.php';
    require_once 'classes/stmQuestionSet.php';
    require_once 'classes/stmRoster.php';
    require_once 'classes/stmGame.php';
    
    # Make session information/storage available immediately
    session_start();
    
    # Services/Objects that we need for any request
    $config = new stmConfig();
    $dbh = new stmDbh();
    $session = new stmSession();
    $transport = new stmTransport();
    $worker = new stmWorker($config,$session,$dbh);
    $security = new stmSecurity($config,$session,$dbh);
    $uploads = new stmUploads($config,$session,$dbh);
    
    # Security is ready when both Authentication and Authorization have
    # been completed successfully
    if(!$security->ready()) {
        die();
    }
    
    # So that we can edit authorization lists on the fly, enable an option to
    # reauthorize users without logging out and back in
    if($security->isRequestingReauthorize()) {
        if(!$security->reauthorize()) {
            die();
        }
    }
    
    # Process file uploads
    if(!$uploads->process()) {
        die();
    }
    
    # Process AJAX request
    $request = $transport->readRequest();
    if($request) {
        $response = $worker->process($request);
        $transport->sendResponse($response);
        die();
    }
    
    # Send AngularJS app
    $route = filter_input(INPUT_GET,"route");
    $templates = $config->value("templates");
    if($route == "admin") {
        if($session->isInstructor()) {
            include("pages/administration.php");
        }
        else {
            echo "You are not authorized for this operation";
            die();
        }
    }
    else if($route == "game") {
        include("pages/game.php");
    }
    else {
        include("pages/dashboard.php");
    }
