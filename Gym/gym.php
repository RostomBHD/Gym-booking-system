
<?php

function set_pdo_connection() {
    $host = "studdb.csc.liv.ac.uk";
    $user = "sgrbenha"; 
    $passwd = "RstmAlg16";
    $db = "sgrbenha";
    $charset = "utf8mb4";
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $opt = array(
        PDO:: ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION ,
        PDO:: ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC ,
        PDO:: ATTR_EMULATE_PREPARES => false
        );

    try {
        $pdo = new PDO($dsn ,$user ,$passwd , $opt);
        } catch (PDOException $e) {
        echo 'Connection failed: ',$e->getMessage();
        }

    return $pdo ;    
}


function choose_class () {
$pdo = set_pdo_connection() ;
$data = $pdo->query("SELECT DISTINCT CLASS FROM gym") ;

echo'
<form action="gym.php" method = "post">
<label for="class">choose a class:</label> <br>
<select  name="classes" onchange="this.form.submit()">
<option disabled selected > -- select an option -- </option>  ';

    while($row = $data->fetch()) {
    echo' 
    <option name="class_option" >',$row['CLASS'],'</option>';
    } 
echo ' </select> 
     </form> <br> '; 

        $class = $_POST['classes'];
    
    
    
$pdo = null;

return $class ;
} 

$class = choose_class();

function choose_day_time ($class) {
    $pdo = set_pdo_connection() ;
    $data = $pdo->query("SELECT times FROM gym WHERE class='$class' AND capacity>0 ") ;
    echo'
        <form action="gym.php" method="post" >
        <label for="times">choose a day and time:</label> <br>
        <select name="times" >
        <option disabled selected > -- select an option -- </option>  ';

    while ($row = $data->fetch()) {
        echo' 
        <option name="times" selected >',$row['times'],'</option>  ';
    }
    echo ' </select>   <br> ';
    
     $time = $_POST['times'] ;
     $pdo = null;


return $time ;
 
}

$time = choose_day_time($class) ;

function prepare_summary ( ) {
    
    
    echo '
    
    <label>Enter your name: </label> <br>
    <input type ="text" name = "name" > <br>

    <label>Enter your phone number: </label> <br>
    <input type ="text" name = "phone" > <br>
    <input type ="hidden" name = "classes" value =',$_POST['classes'],'>
    <input type ="submit"> </form>';
    
    return $_POST ;

}
$booking = prepare_summary();

function show_summary ($class , $time , $booking) {
    $name = $booking['name'];
    $phone = $booking['phone'];

    $_booking = array() ;

    $pdo = set_pdo_connection();
    $capacity = $pdo->prepare("select capacity from gym where class=? and times=?") ;
    $capacity->execute(array($class , $time)) ;
    $capacity = $capacity->fetch() ;
    $capacity = $capacity['capacity'];

        if ($class!="" && isset($name)) {
            if((preg_match("/^[a-zA-Z'][a-zA-Z ' -]*[a-zA-Z ']$/",$name)) && (preg_match("/^[0-9\-\(\)\/\+\s]*$/",$phone))) {
                echo '
                <p> your choosen class is: ',$class,' </p>
                <p> your choosen time is: ',$time,' </p>
                <p> your name  is: ',$name,' </p> 
                <p> your phone number is: ',$phone,' </p>';
    
                $_booking = array() ;

                $pdo = set_pdo_connection();
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
                $queries = "
                UPDATE gym SET capacity=capacity-1 where class='$class' AND times='$time' ;
                INSERT INTO record VALUES('$class','$time','$name','$phone') ; ";
        
                $pdo->exec($queries) ;
            }
            else {
                echo "Invalid name or phone number please try again !";
            }
        }
    
}   
show_summary($class , $time , $booking) ;

?>