<?php
function showerror($a)
{
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ошибка SecChat</title>
</head>
<body>
<h1>В программе SecChat произошла ошибка</h1>
<p><?php echo $a; ?></p>
<hr>
<p><a href="https://github.com/vodolaz095/secchat">SecChat</a>
- Остроумов Анатолий - 2011 год</p>
</body>
</html>
<?php
//exit();
}



$secchat=@parse_ini_file('secchat.ini');
if(!$secchat) showerror('Конфигурационный файл secchat.ini отсутствует!');

if ($secchat['force_https']==1)
{
    if ($_SERVER['SERVER_PORT']!=443) header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    if ($_SERVER['HTTPS']!='on') die('Не настроен SSL!');
}

session_start();

#var_dump($secchat);

if($secchat['obfuscate_server'])
{
header('Server: ');
header('X-Powered-By: ');
}


require ($secchat['classdir'].'/model/controller.php');
$SC=new CONTROLLER($secchat);
$SC->dispatcher($_SERVER['REQUEST_URI']);
/*
echo '<pre>';
var_dump($SC);
echo '</pre>';
*/
?>
