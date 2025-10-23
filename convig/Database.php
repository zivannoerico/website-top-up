<?php
class Database{
    private $connection;
    public function getConnection(){
        $this->connection = null;
        try{
            $this->connection = new mysqli("localhost", "root", '', "db_aksara");

            if($this->connection->connect_error){
                die("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8");
            return $this->connection;
        }catch(Exception $e){
            echo 'Connection failed : '.$e->getMessage();
        }
    }
}
