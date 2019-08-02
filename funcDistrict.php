<?php
$host ="localhost";
$user = "root";
$passwd = "";
$db = "uft_db";

$conn = mysqli_connect($host,$user,$passwd,$db);
if(!$conn){
    die("Connection failed: ".mysqli_connect_error());
}

#creating district.txt
function create($var)
{
    $dir = "DistrictFiles/$var";
    $f = fopen($dir,"w");
    if($f == false){
        echo "failed to create file";
    }else{
        echo "file created";
    }
         fclose($f);
    exit();
}

#searching district.txt
function searchFile($var)
{
    $path = "District/".$var;
    if((glob($path)) == true)
    {
        return 1;
    }
    else{
        return 0;
    }
}

#inserting district into database
function insertDistrict($var){
    $query ="INSERT INTO Districts(name) VALUES('$var')";

}

#searching  district
function searchDistrict($var)
{
 $query = "SELECT Name FROM Districts WHERE name='$var' ";
 if($query == true){
     return 1;
 }else{
     return 0;
 }
}
