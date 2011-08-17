<script language="php">
header( 'Content-Type: text/html; charset=windows-1251;' );
session_start();
include "../../secchat-classes/lib.php";
//var_dump($_POST);


$link=nmysql();

$user=new USER($link);
$channel=new CHANNEL($_POST['URI'],$user,$link);

if ($_POST['s']==session_id())
	{
	if($_POST['count']==1) echo $channel->count_mesg();
	elseif($_POST['list']) $channel->list_mesg($_POST['list']);
	elseif ($_POST['new_mesg']) 
		{

//		echo '///'.$_POST['new_mesg'].'/'.$channel->filter($_POST['new_mesg']);
		

		$channel->post($_POST,true);
		$channel->list_mesg(1);		
		}
	else header("HTTP/1.0 404 Not Found");
	}
	else header("HTTP/1.0 404 Not Found");

mysql_close($link);
</script>