<?php

    class Handler{

        private $mysqli;

        //Handler Constructor | Sets our MySQLI object for further use in the class and attempts to create the required schema if it doesn't already exist
        public function __construct($mysql_host, $mysql_user, $mysql_pass) {
            $this->mysqli = mysqli_connect($mysql_host, $mysql_user, $mysql_pass);
            if ( file_get_contents($_SERVER['DOCUMENT_ROOT']."/api/classes/createschema.txt") == 1 ){ //To prevent unreasonable database hits, we access a txt file flag prior to creating schema
                $createdb = mysqli_query($this->mysqli, "CREATE DATABASE IF NOT EXISTS `vumc`;");
                mysqli_select_db($this->mysqli, 'vumc');
                $createtable = mysqli_query($this->mysqli, 
                    "CREATE TABLE IF NOT EXISTS `github` (
                        `Repository ID` varchar(64) NOT NULL DEFAULT '',
                        `Name` varchar(64) NOT NULL,
                        `URL` text NOT NULL,
                        `Created date` varchar(30) NOT NULL DEFAULT '',
                        `Last push date` varchar(30) NOT NULL DEFAULT '',
                        `Description` text NOT NULL,
                        `Number of stars` int NOT NULL,
                        PRIMARY KEY (`Repository ID`)
                    )
                    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Contains data relating to the top starred projects on GitHub';"
                );
                file_put_contents($_SERVER['DOCUMENT_ROOT']."/api/classes/createschema.txt", 0); //We only need to create schema once, so to prevent db hits on construct, we set the txt flag to 0
            }
        }

        //Sanitize inputs for use with MySQL
        private function clean($data){
            return mysqli_real_escape_string($this->mysqli, $data);
        }

        //Update data from the Github $endpoint using given project $language and stream $context
        public function update($endpoint, $language, $context){
            mysqli_select_db($this->mysqli, "vumc");
            //PARSE THE RETURNED JSON FROM GITHUB AND STORE IN USABLE ARRAY
            $request_result = json_decode(file_get_contents($endpoint.'language:'.$language.'&sort=stars', false, $context), true); 
            //STORE UPDATED DATA IN THE DATABASE
            $items = $request_result['items']; //CONDENSE ARRAY
            for( $i = 0; $i < count($items); $i++ ){ //ITERATE THROUGH THE ITEMS WE ARE WORKING WITH FROM GITHUB
                $id = $this->clean($items[$i]['id']);
                $name = $this->clean($items[$i]['name']);
                $url = $this->clean($items[$i]['html_url']);
                $created = $this->clean($items[$i]['created_at']);
                $pushed = $this->clean($items[$i]['pushed_at']);
                $desc = $this->clean($items[$i]['description']);
                $stars = $this->clean($items[$i]['stargazers_count']);
                //INSERT DATA INTO MYSQL DATABASE, UPDATE IF REQUIRED
                $result = mysqli_query($this->mysqli, 
                    "INSERT INTO github ( 
                        `Repository ID`, 
                        Name, 
                        URL, 
                        `Created date`, 
                        `Last push date`,
                        Description, 
                        `Number of stars` 
                    ) VALUES (
                        '$id', 
                        '$name', 
                        '$url', 
                        '$created', 
                        '$pushed', 
                        '$desc', 
                        '$stars'
                    ) ON DUPLICATE KEY UPDATE 
                        name = VALUES(name), 
                        URL = VALUES(URL), 
                        `Created date` = VALUES(`Created date`), 
                        `Last push date` = VALUES(`Last push date`), 
                        Description = VALUES(Description), 
                        `Number of stars` = VALUES(`Number of stars`)"
                );
            }
            return array("status" => 1, "statusText" => "Updating complete");
        }

        //Fetches name and star count of current data stored in MySQL instance
        public function fetchstub(){
            mysqli_select_db($this->mysqli, "vumc");
            //QUERY THE DATABASE FOR ALL REQUIRED INFORMATION
            $result = mysqli_query($this->mysqli, "SELECT `Repository ID`, Name, `Number of stars` FROM github ORDER BY `Number of stars` DESC"); 
            if ($result){
                if ( mysqli_num_rows($result) < 1 ){
                    //If the table in MySQL contains no data, we assume this is the first time use. Return the welcome message.
                    return array("status" => 0, "statusText" => "Welcome to the Github Viewer! To grab the latest data, use the refresh button in the top right corner."); 
                }
                else{
                    //There *IS* data in the table, we then return the data it contains to be displayed by the front end
                    $stubdata = array();
                    while($row = mysqli_fetch_assoc($result)){
                        array_push($stubdata, array(
                            "id" => $row['Repository ID'], //Since this is only stub data, the only items we need are id, name, and stars
                            "name" => $row['Name'],
                            "stars" => $row['Number of stars']
                        ));
                    }
                    return array("status" => 1, "statusText" => $stubdata); //RETURN THE DATA
                }
            }
            else{
                return array("status" => 0, "statusText" => "There was an error while fetching database information"); //AN ERROR PREVENTED THE DATA FROM BEING RETURNED
            }
        }

        //Fetches all details of current data stored in MySQL instance
        public function fetchdetails($repoid){
            mysqli_select_db($this->mysqli, "vumc");
             //QUERY THE DATABASE FOR ALL REQUIRED INFORMATION
            $result = mysqli_query($this->mysqli, "SELECT URL, `Created date`, `Last push date`, Description FROM github WHERE `Repository ID` = '$repoid'");
            if ($result){
                if ( mysqli_num_rows($result) > 0 ){ //As long as we find the requested entry in the table, return it's information
                    $row = mysqli_fetch_assoc($result);
                    $details = array(
                        "url" => $row['URL'], //Since we already have id, name, and stars, we only need url, created, pushed, and desc
                        "created" => $row['Created date'],
                        "pushed" => $row['Last push date'],
                        "description" => $row['Description'] 
                    );
                    return array("status" => 1, "statusText" => $details); //RETURN THE DATA
                }
                else{
                    return array("status" => 0, "statusText" => "No repository with given ID was found in the database. Try refreshing data from Github.");
                }
            }
            else{
                return array("status" => 0, "statusText" => "There was an error while fetching database information"); //AN ERROR PREVENTED THE DATA FROM BEING RETURNED
            }
        }
    }

?>