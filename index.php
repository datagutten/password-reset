<?php
ini_set('display_errors',true);
session_start();
if(isset($_GET['logout']))
{
	session_destroy();
	header('Location: '.basename(__FILE__));
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Tilbakestill passord</title>
<script type="text/javascript" src="find_user.js"></script></head>

<body>
<?Php
require 'vendor/autoload.php';

use datagutten\sms;

try {
    $adtools = new adtools('reset');
} catch (Exception $e)
{
    die($e->getMessage());
}

$config = require 'config.php';
if(isset($config['sms_gateway']))
{
    $sms=new sms\sms($config['sms_gateway'], 'password-web');
}

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
}

if(isset($_SESSION['reset']['username']))
{
?>

<p><?php printf('Du er logget p&aring som %s og tilkoblet %s',$_SESSION['reset']['username'],$adtools->config['dc']); ?>
<form id="form1" name="form1" method="post">
  <p>
    <label for="username">Brukernavn:</label>
    <input type="text" name="username" id="username" onKeyUp="mobile(this.value)" onChange="mobile(this.value)">
  </p>
  <p id="displayname_text"></p>
  <p id="pwdlastset"></p>
  <?php
 if(isset($config['sms_gateway']))
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

    try
    {
        $adtools->change_password($_POST['dn'],$password);

		//Skriv fil til brukerens hjemmeområde som trigger script for å be brukeren lage nytt passord
		if(isset($config['password_change_dir']))
			file_put_contents(sprintf('%s/%s.txt',$config['password_change_dir'],$_POST['username']),'web_'.date('c'));

		if(!empty($_POST['sms']))
		{
		    try {
                $message=sprintf("Hei %s\nDin bruker %s har fått passord %s\nGi beskjed til IT umiddelbart på telefon 64962020 hvis ditt navn ikke stemmer",
                    $_POST['displayname'],$_POST['username'],$password);
                $sms->send($_POST['sms'], $message);
                echo sprintf('<p>Passordet er sendt på SMS til %s</p>',$_POST['sms']);
            }
            catch (sms\Exception $e)
            {
                printf('Feil ved sending av SMS: %s' % $e->getMessage());
            }
		}
        echo sprintf('Passordet for %s er satt til %s<br />(DN: %s)',$_POST['username'],$password,$_POST['dn']);
	}
	catch (LdapException $e)
	{
		printf("<p>Det oppstod en feil ved endring av passord: %s</p>", $e->getMessage());
	}
}
echo '<p><a href="?logout">Logg ut</a></p>';
}

?>
</body>
</html>