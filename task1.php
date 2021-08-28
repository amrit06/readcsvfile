#!/bin/php

<?php
class UserTask
{
    public $userData = array();


    public function cleanString($value)
    {
        $result = preg_replace('/[0-9\@\.\;\!\" "]+/', "", $value);
        $result =  ucfirst(strtolower($result));
        return $result;
    }


    public function readFromCSV($file)
    {
        $row = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row > 0) {

                    if (filter_var($data[2], FILTER_VALIDATE_EMAIL)) {
                        $this->userData[$row]["name"] = $this->cleanString($data[0]);
                        $this->userData[$row]["surname"] = $this->cleanString($data[1]);
                        $this->userData[$row]["email"] =  strtolower($data[2]);
                    } else {
                        echo $data[2] . " is invalid email\n";
                    }
                }
                $row++;
            }
            fclose($handle);
        }
    }


    public function saveToDB($username, $password, $host, $dbname)
    {
        $tablename = "users";
        $conn = new mysqli($host, $username, $password, $dbname);

        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error;
            exit(1);
        }

        $testQuery = "SELECT * FROM " . $tablename;
        if ($conn->query($testQuery)) {
            // exists

        } else {
            // doesn't exit
            $query =
                "CREATE TABLE users (
            name varchar(255) NOT NULL,
            surname varchar(255) NOT NULL,
            email varchar(255) NOT NULL)";
            $conn->query($query);
        }

        foreach ($this->userData as $items) {
            $query =
                "INSERT INTO users 
                    (name, surname, email) 
                    VALUES 
                    ({$items['name']}, {$items['surname']}, {$items['email']})";
            $conn->query($query);
        }
    }

    public function dryRun()
    {
        print("\nName\t\t" . "Surname\t\t" . "Email\n");
        foreach ($this->userData as $items) {
            print($items["name"] . "\t\t" . $items["surname"] . "\t\t" . $items["email"] . "\n");
        }
    }
}

?>

<?php
$usertask = new UserTask();

$vars = getopt("u:p:h:", ["file:", "create_table::", "dry_run::", "help::"]);

print("***********************************************\n");
if (array_key_exists("help", $vars)) {
    print("This Script allows you to pass csv file along with MySQL info and save data \n" .
        "\n" .
        "\n" .
        "--help: will invoke this page where each directives are explained. \n" .
        "--file [csv file]: name of the csv file. \n" .
        "                   run conjuncion with --create_table or --dry_run\n" .
        "\n" .
        "--create_table: table will be build in MySQL. \n" .
        "                   must supply -u -h -p\n" .
        "\n" .
        "--dry-run: read csv file but do not save into DB \n" .
        "-u [username]: username of the MYSQL \n" .
        "-h [host]: MySQL host \n" .
        "-p [password]: MySQL password \n");
    exit(0);
}

if (array_key_exists("file", $vars) && array_key_exists("create_table", $vars)) {
    if (array_key_exists("u", $vars) && array_key_exists("h", $vars) && array_key_exists("p", $vars)) {
        $usertask->readFromCSV($vars["file"]);
        $usertask->saveToDB($vars["u"], $vars["p"], $vars["h"], "csvDB");
    } else {
        print("please refer to help. press --help \n");
    }
} else if (array_key_exists("file", $vars) && array_key_exists("dry_run", $vars)) {
    $usertask->readFromCSV($vars["file"]);
    $usertask->dryRun();
} else {
    print("please refer to help. press --help \n");
}
print("***********************************************\n");


?>