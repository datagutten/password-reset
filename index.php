<?php
ini_set('display_errors',true);
session_start();
if(isset($_GET['logout']))
{
	session_destroy();
	header('Location: '.basename(__FILE__));
}
require 'config.php';
require 'adtools/adtools.class.php';
$adtools=new adtools;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Tilbakestill passord</title>
<?php
if(isset($config['sms']))
	require '../sms/sms.php';
?>

<script type="text/javascript" src="find_user.js"></script></head>

<body>
<?Php
require 'vendor/autoload.php';
try {
    $adtools = new adtools('reset');
} catch (Exception $e)
{
    die($e->getMessage());
}

$config = require 'config.php';

if(!isset($_SESSION['reset']['username']))
{
	if(isset($_POST['submit']))
	{
		try
		{
            $adtools->connect_and_bind(null,$_POST['username'].'@'.$adtools->config['domain'],$_POST['password']);
			$_SESSION['reset']['username']=$_POST['username'];
			$_SESSION['reset']['password']=$_POST['password'];
		}
		catch (Exception $e)
        {
            echo "<p>Kunne ikke koble til AD: {$e->getMessage()}</p>";
        }
	}
	else
	{
		echo "<p>Logg p&aring; med en bruker som har tilgang til &aring; endre passord i {$adtools->config['domain']}</p>\n";
		echo $adtools->login_form();
	}
	if(isset($username) && isset($password))
	{
		$return=$adtools->connect_and_bind($config['dc'],$username.'@'.$config['upn_suffix'],$password,true);
		if($return===false)
		{
			unset($_SESSION[$config['dc']]);
			echo "<p>Kunne ikke koble til AD: {$adtools->error}</p>";
		}
	}
}

if(isset($username))
{
?>

	<p><?php printf('Du er logget p&aring som %s og tilkoblet %s',$username,$config['dc']);?></p>
<form id="form1" name="form1" method="post">
  <p>
    <label for="username">Brukernavn:</label>
    <input type="text" name="username" id="username" onKeyUp="mobile(this.value)" onChange="mobile(this.value)">
  </p>
  <p id="displayname_text"></p>
  <p id="pwdlastset"></p>
  <?php
 if(isset($config['sms']))
 {
?>
  <p>
    <input name="sms" type="checkbox" disabled="disabled" id="sms">
  <span id="sms_text">Send SMS</span></p>
<?Php
}
?>
   <p>
    <input type="submit" name="submit_password" id="submit_password" value="Submit" disabled="disabled">
  </p>
  <input type="hidden" name="dn" id="dn">
  <input type="hidden" name="displayname" id="displayname">
</form>
<?php
$passwords=array('Skorpion','Flaggermus','Edderkopp','Grevling','Moskus','Leopard','Tiger'); //Ordliste for pasord (minst 5 tegn)

if(isset($_POST['submit_password']))
{
	//$user=$adtools->find_object($_POST['username'],$adtools->dn,'username');
	//$random=true;
	
	if(isset($random))
	{
		$characters=8;
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$password='';
		for($i=0; $i<=$characters; $i++)
		{
			$password.=$alphabet[mt_rand(0,61)];
		}
	}
	else
		$password=$passwords[mt_rand(0,count($passwords)-1)].mt_rand(100,999); //Lag tilfeldig passord med ordliste og tall
	
	if($adtools->change_passord($_POST['dn'],$password)!==false) //Endre passordet
	{
		//Skriv fil til brukerens hjemmeområde som trigger script for å be brukeren lage nytt passord
		if(isset($config['password_change_dir']))
			file_put_contents(sprintf('%s/%s.txt',$config['password_change_dir'],$_POST['username']),'web_'.date('c'));

		if(!empty($_POST['sms']))
		{
			echo sprintf('<p>Passordet er sendt på SMS til %s</p>',$_POST['sms']);
			$message=sprintf("Hei %s\nDin bruker %s har fått passord %s\nGi beskjed til IT umiddelbart på telefon 64962020 hvis ditt navn ikke stemmer",$_POST['displayname'],$_POST['username'],$password);
			sms($_POST['sms'],$message,'password_web');	//Send melding med nytt passord
		}
		if(isset($_GET['nybruker']))
			echo sprintf('Bruker for %s er opprettet med brukernavn %s og førstegangspassord %s',$_POST['displayname'],$_POST['username'],$password);
		else
			echo sprintf('Passordet for %s er satt til %s<br />(DN: %s)',$_POST['username'],$password,$_POST['dn']);
	}
	else
	{
		echo "<p>Det oppstod en feil ved endring av passord</p>";
	}
}
echo '<p><a href="?logout">Logg ut</a></p>';
}

?>
</body>
</html>