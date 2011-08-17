<script language="php">
header('Server: ');
header('X-Powered-By: ');

if ($_SERVER["SERVER_PORT"]!=443) header('Location: https://'.$_SERVER['HTTP_HOST']);
if ($_SERVER['HTTPS']!='on') die('Не настроен SSL!');

include ("db.php");
include ("page.php");
include ("user.php");
include ("channel.php");

</script>