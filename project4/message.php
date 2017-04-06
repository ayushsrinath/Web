<html>
<head></head>
<body>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On');

 try {
	  $dbh = new PDO("mysql:host=127.0.0.1:3306;dbname=<db name>",<password>,"",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	  
	} catch (PDOException $e) {
	  print "Error!: " . $e->getMessage() . "<br/>";
	  die();
	}

//Condition to handle post message requests
if(isset($_POST['message'])) {
    $reply_to = 0;
    if(isset($_POST['reply'])) {
        $reply_to = $_POST['reply'];
    }
    $result = add_message($_POST['message'], $reply_to);
    if($result) {
        header('Location: message.php');
    } else {
        echo 'Could not post message, please try again.';
    }
}


//Method to add message to the message board
function add_message($message, $reply_to) {
    global $dbh;
    $reply_id = null;
    if ($reply_to != 0) {
        $reply_id = $reply_to;
    }

    $dbh->beginTransaction();
    $username = $_SESSION['username'];
    $sql = 'insert into posts values("' . uniqid() .'", "'.$reply_id.'","'.$username.'",now(),"'.$message.'")';
    print $sql;
    $dbh->exec($sql)
              or die(print_r($dbh->errorInfo(), true));
    $dbh->commit();  
    return true;
}


function add_replyto($msgid){
	global $dbh;
	$sql = 'update posts SET ';
}

//Method to retrieve messages
function get_messages(){
	
	global $dbh;
    //$dbh->beginTransaction();
    try {
		$sql = 'select * from posts order by datetime'; 
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	catch(PDOException $e) { 
        print "Error!: " . $e->getMessage() . "<br/>";
	  die();
    }
}

function get_fullname($uname){
	global $dbh;
    //$dbh->beginTransaction();
	try{
		$sql = 'select * from users where username = "'.$uname.'";';
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result['fullname'];
	}catch (PDOException $e) {
	  print "Error!: " . $e->getMessage() . "<br/>";
	  die();
	
	
	
}
}	

//Condition to handle logouts
if(isset($_POST['logout'])){
    session_destroy();
    session_start();
}
    if(!isset($_SESSION['username'])):
        header('Location: board.php');
			
    else:?>
		<form action="message.php" method="POST">
			<input type="hidden" name="logout" value="1"/>
			<input type="submit" value="Logout"/>
		</form>
		Post a message: 
		<form action="message.php" method="POST">
        <?php
        if(isset($_GET['reply'])){
            echo '<input type="hidden" name="reply" value="'.$_GET['reply'].'"/>';
        }
        ?>
        <textarea name="message" rows="10" cols="100"></textarea>
        <br>
        <input type="submit" value="Post Message"/>
    </form>

	<?php
	endif;		
	
	$all_msgs = get_messages();
if(count($all_msgs)>0){	
	foreach($all_msgs as $msg){
		$result = '';
		$result = $result. "<br>-----------Message--------------";
		$result = $result. "<br>Message ID: ".$msg['id'];
		$result = $result. "<br>OP Username: ".$msg['postedby'];
		$result = $result. "<br>OP Full Name: ".get_fullname($msg['postedby']);
		if($msg['replyto']) {
			$result = $result. "<br>Reply To: ".$msg['replyto'];
		}
		$result = $result. "<br>Posted: ".$msg['datetime'];
		$result = $result. "<br>Message: ".$msg['message'];
		$result = $result. '<br><a href="message.php?reply='.$msg['id'].'"><button>Reply</button></a>';
		echo $result;
	}
}
else{
	echo "No Posts yet";
}
	?>
</body>
</html>