<?php

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $con = mysqli_connect('localhost', 'root', '', 'swaf');
} else {
    $con = mysqli_connect('localhost', 'u361889932_msent', 'R4n!WMyfYvt!', 'u361889932_msent');
}


// if($con){
//     echo " connected ";
// }else{
//     echo " not connected :" . mysqli_connect_error();
// }
