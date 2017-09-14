<?Php
session_start();
require 'adtools/adtools.class.php';
$adtools=new adtools;
require 'config.php';

$result=$adtools->connect_and_bind($config['dc'],$_SESSION[$config['dc']]['username'].'@'.$config['upn_suffix'],$_SESSION[$config['dc']]['password'],true);

if($result===false)
	echo json_encode(array('error'=>$adtools->error));
else
{
	if(!isset($_GET['username']) && isset($argv[1]))
		$_GET['username']=$argv[1];

	$mobile=$adtools->find_object($_GET['username'],$config['base_dn'],'username',array('mobile','displayName','pwdLastSet'));
	if(!isset($config['sms']))
		$mobile['mobile'][0]=false;
	elseif(!isset($mobile['mobile']))
		$mobile['mobile'][0]='';
	if(!empty($adtools->error))
		echo json_encode(array('error'=>$adtools->error));
	else
	{
		$pwdlastset=date('Y-m-d H:i',$adtools->microsoft_timestamp_to_unix($mobile['pwdlastset'][0]));
		echo json_encode(array('mobile'=>$mobile['mobile'][0],'displayName'=>$mobile['displayname'][0],'dn'=>$mobile['dn'],'error'=>'','pwdlastset'=>$pwdlastset));
	}
}