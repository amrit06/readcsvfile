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

    public function createDB($host, $username, $password, $dbname)
    {
        $conn = new mysqli($host, $username, $password);
        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error;
            exit(1);
        }

        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA
        WHERE SCHEMA_NAME = '{$dbname}'";
        if (!$conn->query($query)) {
            $query = "CREATE DATABASE IF NOT EXISTS " . $dbname;
            if ($conn->query($query)) {
                echo "DB created... \n";
            }
        }
        $conn->close();
    }

    public function saveToDB($username, $password, $host)
    {
        $dbname = "useruploadDB";
        $tablename = "users";

        //create DB
        $this->createDB($host, $username, $password, $dbname);

        //check connection
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error;
            $conn->close();
            exit(1);
        }

        //check if table exists if not create it
        $testQuery = "SELECT * FROM " . $tablename;
        if (!$conn->query($testQuery)) { // runs only when table isn't present
            //create table
            $query =
                "CREATE TABLE {$tablename} (
            name varchar(255) NOT NULL,
            surname varchar(255) NOT NULL,
            email varchar(255) NOT NULL)";
            $conn->query($query);
            echo  "Table Created... \n";
        }

        // save csv data to db
        foreach ($this->userData as $items) {
            $query =
                "INSERT INTO {$tablename} (name, surname, email) VALUES
                 ('{$items['name']}', '{$items['surname']}',' {$items['email']}');";
            $conn->query($query);
        }
        echo "Complete inserting \n";
        $conn->close();
    }

    public function printOut()
    {
        print("\nName\t\t" . "Surname\t\t" . "Email\n");
        foreach ($this->userData as $items) {
            print($items["name"] . "\t\t" . $items["surname"] . "\t\t" . $items["email"] . "\n");
        }
    }
}

?>

<?php
//things that are assumed
// Db is already setup
// Db name and table name is provided
// --file directive is executed with either --create_table or --dry_run
// if --create_table is used -u -h -p is also passed


$usertask = new UserTask();

// get directives and their values
$vars = getopt("u:p:h:", ["file:", "create_table::", "dry_run::", "help::"]);

print("***********************************************\n");
if (array_key_exists("help", $vars)) { // case help
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
    // case create table
    if (array_key_exists("u", $vars) && array_key_exists("h", $vars) && array_key_exists("p", $vars)) {
        // make sure you have username, host and password.
        $usertask->readFromCSV($vars["file"]);
        $usertask->saveToDB($vars["u"], $vars["p"], $vars["h"]);
    } else {
        print("please refer to help. press --help \n");
    }
} else if (array_key_exists("file", $vars) && array_key_exists("dry_run", $vars)) {
    //dry_run
    $usertask->readFromCSV($vars["file"]);
    $usertask->printOut();
} else {
    print("please refer to help. press --help \n");
}
print("***********************************************\n");

?>