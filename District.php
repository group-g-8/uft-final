<?php
require "connection.php";
if($_POST["district"])
 $name = $_POST["district"];
 $filename = $name.".txt";

 #FUNCTIONING FROM HERE
#alert availability
 if((searchFile($filename)) == 1 && (searchDistrict($name))== 1)
 {
     echo"<script>alert('$filename and $name exist')</script>";
 }
 
 #creating district.txt
 if((searchFile($filename)) == 0  && (searchDistrict($name))== 1)
 {
    create($filename);
 }

 #inserting district
 if((searchFile($filename)) == 1 && (searchDistrict($name))== 0)
 {
    insertDistrict($name);
 }

 #inserting district as well as creating the file name
 if((searchFile($filename)) == 0 && (searchDistrict($name))== 0)
 {
     insertDistrict($name);
     create($filename);
 }
