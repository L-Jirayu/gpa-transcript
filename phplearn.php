<?php
    echo "====== Text Output ======\n";
    echo "Hello PHP\n\n";

    echo "====== Varaible ======";
    $name = "John"; //str
    $age = 30; //int
    $e = 2.72; //float
    $is_student = true; //bool

    echo "\nMy name is ", $name;
    echo "\nI'm ", $age, " years old.";
    echo "\nEuler's Number is ", $e;
    echo "\nIs Student? ", $is_student;

    echo "\n\n====== if-else ======";
    if($is_student == true){
        echo "\nI'm student for study PHP.\n";
    }
    else if($is_student == false){
        echo "\nI'm not student.\n";
        
    }
    else{
        echo "\n Boolean type have true and false only\n";
    }

    echo "\n====== Switch Case ======";
    switch($age){
        case 20:
            echo "\nYou are 20 years old.\n\n";
            break;
        case 30:
            echo "\nYou are 30 years old.\n\n";
            break;
        default:
            echo "\nYour age is neither 20 nor 30.\n\n";
            break;
    }

    echo "====== For Loop ======\n";
    for($i=1; $i<11; $i++){
        echo "Number is $i \n";
    }

    echo "\n====== While Loop ======\n";
    $j = 1;
    while($j<11){
        echo "Number is $j\n";
        $j++;
    } //<11 (1-10), <=10 (1-10) < is better than <=
?>