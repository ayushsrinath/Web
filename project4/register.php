<html>
<head></head>
<body>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');
    if(isset($_SESSION['username'])) {
        header('Location: message.php');
    }
 try {
	  $dbh = new PDO("mysql:host=127.0.0.1:3306;dbname=<db_name>",<password>,"",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	  
	} catch (PDOException $e) {
	  print "Error!: " . $e->getMessage() . "<br/>";
	  die();
	}
 
 
 function add_user($username, $password, $fullname, $email) {
    global $dbh;
    $dbh->beginTransaction();
    try {
        $sql = 'insert into users values("' . $username .'", "'.md5($password).'","'.$fullname.'","'.$email.'")';
        #print $sql;
        $dbh->exec($sql);
        $dbh->commit();  
        #print $dbh->lastInsertId();
        if ($dbh->lastInsertId()):
            return true;
        endif;
    } catch(PDOException $e) { 
        if ($e->errorInfo[1] == 1062) {
            return 1062;
        }
    }
    #return true;
}
?>

<center><h2>Register to the Message Board</h2></center>
<?php 
    if (isset($_POST['username'])):
        $username = $_POST['username'];    
        $password = $_POST['password'];    
        $full_name = $_POST['full_name'];    
        $email = $_POST['email'];    
        $registration_success = add_user($username, $password, $full_name, $email);
        #echo $registration_success;
        if ($registration_success != 1062) {
            header('Location: board.php');
        } elseif ($registration_success == 1062) {
            echo "<center><span style='color:red'>Username already taken, please try a different username and register</span></center>";          
            $show_ui = true;
        }
    endif;
    if (!isset($_POST['username'])):
?>
        <center>
        <form method="POST" action="register.php">
        <table>
        <tr>
            <td>Username:</td>
            <td><input type="text" name="username" id="username"></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type="password" name="password" id="password"></td>
        </tr>
        <tr>
            <td>Full Name:</td>
            <td><input type="text" name="full_name" id="full_name"></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><input type="text" name="email" id="email"></td>
        </tr>
        <tr>
            <td></td><td>
            <input type="submit" value="Register">
            </td>
        </tr>
        </table>
        </form>
        </center>
<?php
    endif;
?>
</body>
</html>