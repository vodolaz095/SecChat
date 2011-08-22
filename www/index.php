<script language="php">
session_start();
//header('Content-Type: text/html; charser=windows-1251;');
include("../secchat-classes/lib.php");

$link=nmysql();

$user=new USER($link);
$page=new PAGE($user,$link);
$page->dispatcher($_SERVER['REQUEST_URI']);

mysql_close($link);

</script>