<?Php
session_start();
if(empty($_SESSION['reset']))
    die(json_encode(array('error'=>'Not logged in')));
require 'adtools/adtools.class.php';

try
{
    $ad_tools=new adtools('reset');
    $ad_tools->connect_and_bind(null,$_SESSION['reset']['username'].'@'.$ad_tools->config['domain'],$_SESSION['reset']['password']);
    if(!isset($_GET['username']) && isset($argv[1]))
        $_GET['username']=$argv[1];

    $user=$ad_tools->find_object($_GET['username'],false,'username',array('mobile','displayName','pwdLastSet'));

    if(!empty($user))
    {
        if(!isset($user['mobile']))
            $user['mobile'][0]='';
        $pwdlastset_timestamp=$ad_tools->microsoft_timestamp_to_unix($user['pwdlastset'][0]);
        $pwdlastset=date('Y-m-d H:i',$pwdlastset_timestamp);
        $diff=time()-$pwdlastset_timestamp;
        $diff_days=$diff/(3600*24);
        echo json_encode(array(
            'mobile'=>$user['mobile'][0],
            'displayName'=>$user['displayname'][0],
            'dn'=>$user['dn'],'error'=>'',
            'pwdlastset'=>$pwdlastset,
            'diff_days'=>(int)$diff_days));
    }
    else
        echo json_encode(array('error'=>' '));
}
catch (Exception $e)
{
    echo json_encode(array('error'=>$e->getMessage()));
}
