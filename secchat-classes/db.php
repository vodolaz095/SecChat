<script language="php">
function nmysql()
{
$lnk=mysql_connect('localhost','root','');
/*
write there your config for accessing database
You can see full description  on google  php+mysql_connect (for connection to db on local server)
or php+mysql_pconnect
*/

mysql_select_db('',$lnk);

if ($lnk) return($lnk);
else return false;
}
</script>