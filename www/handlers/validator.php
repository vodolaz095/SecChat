<script language="php">
session_start();
include "../../secchat-classes/lib.php";

function filter_txt($a)
{
$a=trim($a);
$a=stripslashes($a);
$a=mysql_real_escape_string($a);		
return $a;	
}


if ($_POST['s']==session_id() and $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')
{
	if($_POST['new_U'])
		{
		if(preg_match('~[A-z0-9_\-]+~',$_POST['new_U']))
			{
			header('Content-Type: text/html; charset=windows-1251;');
			$link=nmysql();
			$res=mysql_query('SELECT * FROM  users  WHERE U_login="'.filter_txt($_POST['new_U']).'"',$link);
			if (mysql_num_rows($res)==0)
			echo 'Имя пользователя свободно!';
			else		
			echo 'Ошибка! Такой пользователь уже существует!';
			mysql_close($link);				
			}
			else
			{
			echo 'Ошибка! Имя пользователя может состоять только из строчных и заглавных латинских букв и символов _ и - !';
			}
		
		}
	
	if($_POST['select_U'])
		{
		if(preg_match('~[A-z0-9_\-]+~',$_POST['select_U']))
			{
			header('Content-Type: text/html; charset=windows-1251;');
			$link=nmysql();
			$res=mysql_query('SELECT * FROM  users  WHERE U_login="'.filter_txt($_POST['select_U']).'"',$link);
			if (mysql_num_rows($res)==1)
			echo 'Такой пользователь существует!';
			else		
			echo 'Ошибка! Такого пользователя нет!';
			mysql_close($link);				
			}
			else
			{
			echo 'Ошибка! Имя пользователя может состоять только из строчных и заглавных латинских букв и символов _ и - !';
			}
		
		}
		
	if($_POST['pwd'])
		{
		if(preg_match('~[a-z]+~', $_POST['pwd'])) $s++;
        if(preg_match('~[A-Z]+~', $_POST['pwd'])) $s++; 
        if(preg_match('~[0-9]+~', $_POST['pwd'])) $s++; 
        if(preg_match('~[\w]+~', $_POST['pwd'])) $s++; 
		if(preg_match('~[\W]+~', $_POST['pwd'])) $s++;
		if(preg_match('~[\W]{6}~', $_POST['pwd'])) $s++;
		if(preg_match('~[\W]{8}~', $_POST['pwd'])) $s++;		
		if(preg_match('~[\W]{9}~', $_POST['pwd'])) $s++;		
		if(preg_match('~[\W]{13}~', $_POST['pwd'])) $s++;	
		if(preg_match('~[\W]{16}~', $_POST['pwd'])) $s++;				
		echo $s;
		}
}
else
{
header("HTTP/1.0 404 Not Found");
}
</script>