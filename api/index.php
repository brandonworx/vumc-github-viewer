<?php

    //SIMPLE VUMC PHP API FOR PHP GITHUB REPOS
    //AUTHOR: BRANDON GATHER
    //07-13-2023
    //FOR VANDERBILT UNIVERSITY MEDICAL CENTER

    //SETTINGS
    include "connection.php"; //FETCH DATA STORED OUTSIDE OF ROOT FOR MYSQL DB CONNECTION
    include "settings.php"; //FETCH DATA STORE OUTSIDE OF ROOT FOR GITHUB API    
    $github_endpoint = "https://api.github.com/search/repositories?q="; //API ENDPOINT FOR GITHUB
    
    //CLASS AUTOLOADER
    function autoload($class){
        include './classes/'.$class.'.php';
    }
    spl_autoload_register('autoload');

    //INSTANTIATE
    $han = new Handler($mysql_host, $mysql_user, $mysql_pass);

    //RUNTIME CONFIGURATIONS
    ini_set("allow_url_fopen", 1); //ALLOW PHP TO GRAB JSON DATA FROM GITHUB API URL
    $github_opts = [ //THE CONTEXT TO BE USED WITH file_get_contents ( MUST CONTAIN VALID USER-AGENT )
        'http' => [
            'method'=>'GET',
            'header'=>'User-Agent: '.$github_user,
        ]
    ];
    $github_context  = stream_context_create($github_opts); //CREATE THE STREAM CONTEXT FOR OUR file_get_contents CALL IN retrieve()

    //API RESPONDER
    function respond($responsearray){
        echo json_encode(array("response" => $responsearray));
        die();
    }

    //API FUNCTIONS
    switch($_POST['command']){
        case "update":
            respond($han->update($github_endpoint, $github_language, $github_context));
            break;
        case "fetchstub":
            respond($han->fetchstub());
            break;
        case "fetchdetails":
            respond($han->fetchdetails($_POST['repo_id']));
            break;
        default:
            respond(array("status"=> 0, "statusText"=> "Your request was not understood"));
            break;
    }

?>