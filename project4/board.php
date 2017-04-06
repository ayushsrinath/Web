 <html>
<head><title>Message Board</title></head>
<body>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');


	try {
	  $dbh = new PDO("mysql:host=127.0.0.1:3306;dbname=<dbname>",<password>,"",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	  print "</pre>";
	} catch (PDOException $e) {
	  print "Error!: " . $e->getMessage() . "<br/>";
	  die();
	}

	
//Login Method
function login($uname,$pwd){
	
	global $dbh;
	$sql = 'select * from users where username="'.$uname.'" and password="'.md5($pwd).'"';
	//print_r ($sql);
	$st = $dbh->prepare($sql);
	$st->execute();
	if($st->fetch()){
		return true;
	}
	else{
		return false;
	}	
	
}

	if(isset($_POST['username'])){
		$username = $_POST['username'];
		if (login($_POST['username'], $_POST['password'])) {
			$_SESSION['username'] = $username;
		} else {
			echo 'Login Failed';
		}
	}

	if(!isset($_SESSION['username'])):?>

		<form method="POST" action="board.php">
				<table>
					<input type="hidden" name="login" value="1"/>
						<td>Username:</td>
						<td><input type="text" name="username"></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" name="password"></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" value="Login"></td>
					</tr>
				</table>
		</form>
		<br>
				<a href="register.php"><button>Register</button></a>
				
	 <?php else:
			header('Location: message.php');
		endif;?>
</body>
</html>
