<?php
 
error_reporting(E_ALL);
// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit(0);

require_once("DropboxClient.php");

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$dropbox = new DropboxClient(array(
	'app_key' => <dropbox app key>,      // Put your Dropbox API key here
	'app_secret' => <dropbox app secret>,   // Put your Dropbox API secret here
	'app_full_access' => false,
),'en');


// first try to load existing access token
$access_token = load_token("access");
if(!empty($access_token)) {
	$dropbox->SetAccessToken($access_token);
}
elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
{
	// then load our previosly created request token
	$request_token = load_token($_GET['oauth_token']);
	if(empty($request_token)) die('Request token not found!');
	
	// get & store access token, the request token is not needed anymore
	$access_token = $dropbox->GetAccessToken($request_token);	
	store_token($access_token, "access");
	delete_token($_GET['oauth_token']);
}

// checks if access token is required
if(!$dropbox->IsAuthorized())
{
	// redirect user to dropbox auth page
	$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
	$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
	$request_token = $dropbox->GetRequestToken();
	store_token($request_token, $request_token['t']);
	die("Authentication required. <a href='$auth_url'>Click here.</a>");
}


function store_token($token, $name)
{
	if(!file_put_contents("tokens/$name.token", serialize($token)))
		die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}

function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
	@unlink("tokens/$name.token");
}

echo "<pre>";
echo "<b>Welcome:</b>\r\n";
print_r($dropbox->GetAccountInfo()->display_name);

$files = $dropbox->GetFiles("",false);



echo "\r\n\r\n<b>Files:</b>\r\n";

if(empty($files)) {
   echo "No Files to Display";
 }

echo '<table border = 1>';
foreach($files as $item){
		$each_item = explode("/",$item->path);
		echo '<tr><td>';
		echo '<br><a href="album.php?download='.$item->path.'">'.$each_item[1].'</a></td><td>';
		echo '&nbsp<a href="album.php?delete='.$each_item[1].'"><button>Delete</button></a></td></tr>';
		
}
echo '</table>';


if(!empty($_FILES['userfile'])){
	$file = $_FILES['userfile']['name'];
	$dropbox->UploadFile($_FILES['userfile']['tmp_name'],$file);
	echo "\r\n done!";
	header('Location: album.php');
	
}

if (isset($_GET['download'])){
	$to_download = $_GET['download'];
	$files = $dropbox->GetFiles("",false);

	foreach($files as $item){
		
		
		if($item->path == $to_download){
			$file = current($files);
			echo "<img src='".$dropbox->GetLink($file,false)."'/></br>";
		}
		next($files);
		
		
	}
	$dropbox->DownloadFile($to_download,"");
	
}

if (isset($_GET['delete'])){
	$to_delete = $_GET['delete'];
	$dropbox->Delete($to_delete);	
	echo 'Deleted';
	header('Location: album.php');
}

?>
<form enctype="multipart/form-data" action="album.php" method="POST">
<input type="hidden" name="upload" value="1"/>
<input type="hidden" name="MAX FILE SIZE" value="3000000" />
Submit this File : <input name="userfile" type="file" /><br/>
<input type="submit" value="Upload" />
</form>

