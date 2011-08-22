<script language="php">
session_start();
include "../../secchat-classes/lib.php";
$link=nmysql();

$user=new USER($link);
if ($user)
	{
	$cu=$user->get_user();
	
	$q="INSERT INTO online (UID) VALUES 
	('".$cu['UID']."') ON DUPLICATE KEY UPDATE onlinesince=CURRENT_TIMESTAMP";
	mysql_query($q,$link);	
	
	}
echo date('H:i:s');
mysql_close($link);
</script>