<script language="php">
//header('Server: ');
//header('X-Powered-By: ');

$secchat=@parse_ini_file('secchat.ini');
if(!$secchat) die('Конфигурационный файл */secchat-classes/secchat.ini отсутствует!');


if ($secchat['force_https']==1)
{
	if ($_SERVER['SERVER_PORT']!=443) header('Location: https://'.$_SERVER['HTTP_HOST']);
	if ($_SERVER['HTTPS']!='on') die('Не настроен SSL!');
}


function nmysql()
{
global $secchat;

if ($secchat['is_persistent']==1)
    {
    $lnk=@mysql_pconnect($secchat['host'],$secchat['login'],$secchat['password']);
    }
else
    {
    $lnk=@mysql_connect($secchat['host'],$secchat['login'],$secchat['password']);
    }

if ($lnk)
    {
    mysql_select_db($secchat['database_name'],$lnk);
    return($lnk);
    }
    else
    {
	header('Content-Type: text/html; charset=windows-1251;');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> 
<link rel="icon" href="/favicon.gif" type="image/gif" /> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
<META http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
<link href="/style.css" rel="stylesheet" type="text/css" /> 
<title>Ошибка! - нет соединения с базой данных MySQL!</title> 
</head> 
<body> 
<div id="wrap"> 
<div id="header"> 
	<h1>Ошибка!</h1> 
	<h2>Нет соединения с базой данных MySQL</h2> 
</div> 
 
<div id="content"> 
<p>Невозможно установить соединение с базой данных! Укажите реквизиты доступа к MySQL базе данных в файле<strong> /secchat-classes/secchat.ini</strong>!</p>
<p>Чтобы продукт заработал, Вам надо отредактировать этот файл...</p>
<pre style="background-color:#999999">
[Settings for connection to databasae]
host=[IP адрес компьютера, на котором работает MySQL сервер]
login=[Логин для доступа к базе данных]
password=[Пароль для доступа к базе данных]
database_name=[Название базы данных]
is_persistent=0 

;1 - если сервера с Apache/nginx+PHP и MySQL находятся на разных машинах
;0 - если эти сервера на одной машине

[General security settings]
force_https=1
; 0 - допустимы незаифрованные соединения по протоколу http (не рекомендуется)
; 1 - разрешены только зашифрованные соединения по протоколу https (рекомендуется)




</pre>

</div> 
<div id="footer"> 
&copy; <a href="https://github.com/vodolaz095/secchat">SecChat</a>  - Остроумов Анатолий - 2011 год
</div> 
<div id="security" align="center">

<? 
echo '<p>Время системы <strong id="server_time">'.date('H:i:s').'</strong>. Ваш IP адрес <a href="https://www.nic.ru/whois/?query='.$_SERVER['REMOTE_ADDR'].'">'.$_SERVER['REMOTE_ADDR'].'</a>';
echo '. Протокол HTTPS включён через порт '.$_SERVER['SERVER_PORT'].'</p>';
?>
</div>
</body> 
</html> 
<?	
exit();
	}
}

include ("class_user.php");
include ("class_page.php");
include ("class_channel.php");

</script>