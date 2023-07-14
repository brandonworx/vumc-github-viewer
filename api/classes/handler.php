<?php

    class Handler{
        
        private $mysqli;

        public function __construct($mysql_host, $mysql_user, $mysql_pass, $mysql_data) {
            $this->mysqli = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_data);
        }

        //Sanitize inputs for use with MySQL
        private function clean($data){
            return mysqli_real_escape_string($this->mysqli, $data);
        }

        //Update data from the Github $endpoint using given project $language and stream $context
        public function update($endpoint, $language, $context){
            $request_result = json_decode(file_get_contents($endpoint.'language:'.$language.'&sort=stars', false, $context), true); //PARSE THE RETURNED JSON FROM GITHUB AND STORE IN USABLE ARRAY
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
                $result = mysqli_query($this->mysqli, "INSERT INTO github ( `Repository ID`, Name, URL, `Created date`, `Last push date`, Description, `Number of stars` ) VALUES ('$id', '$name', '$url', '$created', '$pushed', '$desc', '$stars') ON DUPLICATE KEY UPDATE name = VALUES(name), URL = VALUES(URL), `Created date` = VALUES(`Created date`), `Last push date` = VALUES(`Last push date`), Description = VALUES(Description), `Number of stars` = VALUES(`Number of stars`)");
            }
            return array("status" => 1, "statusText" => "Updating complete");
        }

        //Fetches name and star count of current data stored in MySQL instance
        public function fetchstub(){
            $result = mysqli_query($this->mysqli, "SELECT `Repository ID`, Name, `Number of stars` FROM github ORDER BY `Number of stars` DESC"); //QUERY THE DATABASE FOR ALL REQUIRED INFORMATION
            if ($result){
                $stubdata = array();
                while($row = mysqli_fetch_assoc($result)){
                    array_push($stubdata, array(
                        "id" => $row['Repository ID'],
                        "name" => $row['Name'],
                        "stars" => $row['Number of stars']
                    ));
                }
                return array("status" => 1, "statusText" => $stubdata); //RETURN THE DATA
            }
            else{
                return array("status" => 0, "statusText" => "There was an error while fetching database information"); //AN ERROR PREVENTED THE DATA FROM BEING RETURNED
            }
        }

        //Fetches all details of current data stored in MySQL instance
        public function fetchdetails($repoid){
            $result = mysqli_query($this->mysqli, "SELECT URL, `Created date`, `Last push date`, Description FROM github WHERE `Repository ID` = '$repoid'"); //QUERY THE DATABASE FOR ALL REQUIRED INFORMATION
            if ($result){
                if ( mysqli_num_rows($result) > 0 ){
                    $row = mysqli_fetch_assoc($result);
                    $details = array(
                        "url" => $row['URL'],
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