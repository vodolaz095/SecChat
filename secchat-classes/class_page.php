<script language="php">
class PAGE
{
private $lnk;
private $USER;
private $rights;
private $template;
////////////
public function filter($a)
{
if (is_numeric($a))
return mysql_real_escape_string($a);
else
return false;
}

public function filter_txt($a)
{
$a=trim($a);
$a=stripslashes($a);
//$a=html_special_characters(
$a=mysql_real_escape_string($a);	
return $a;
}



////////////
public function __construct($user,$link)
{
	if (mysql_ping($link)  and get_class($user)=='USER')
		{
		$this->lnk=$link;
		$this->USER=$user;
		$this->rights=$this->USER->get_user();
		/*
		 * прочие установки
		 */
		}
		else
		{
		return false;
		}
}

private function makeheader($title,$subtitle)
{
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> 
<link rel="icon" href="/favicon.gif" type="image/gif" /> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
<META http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
<link href="/style.css" rel="stylesheet" type="text/css" /> 
<title><? echo $title;?></title> 
<script language="javascript" src="/jquery.min.js" type="text/javascript"></script> 
<script language="javascript" src="/ajax.js" type="text/javascript"></script> 
</head> 
<body> 
<div id="wrap"> 
<div id="header"> 
	<h1><? echo $title;?></h1> 
	<h2><? echo $subtitle;?></h2> 
</div> 
 
<div id="nav"> 
	<ul> 
		<li><a href="/">Главная</a></li> 
		<li><a href="/about">О проекте</a></li> 
<?		
if ($this->rights) 
{
if ($this->rights['admin_users'] or $this->rights['moder_users']) echo '		<li><a href="/admin_users">Пользователи</a></li>';

if ($this->rights['admin_channels'] or $this->rights['moder_channels']) echo '		<li><a href="/admin_channels">Редактировать каналы</a></li>';

if ($this->rights['admin_channels']==1 and $this->rights['admin_users']==1)  echo '		<li><a href="/panic">Тревога</a></li>';
echo '		<li><a href="/log">Протокол доступа</a></li>';
echo '		<li><a href="/change_pwd">Сменить пароль</a></li>';
echo '		<li><a href="/exit">Выход</a></li>';
}
?>
	</ul> 
</div> 
 
<div id="content"> 
<?
}

private function makebottom()
{
?>
</div> 
<div id="footer"> 
&copy; SecChat  - Остроумов Анатолий - 2011 год
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
}

private function mainpage()
{
	if ($this->rights)
	{
	$this->makeheader('Авторизация пройдена','Добро пожаловать, '.$this->rights['U_login'].'!');
	?><h3>Ваши каналы</h3><?
	$res=mysql_query('SELECT * FROM channels WHERE channel_admin_UID="'.$this->filter($this->rights['UID']).'"',$this->lnk);
	$b=mysql_num_rows($res);
	if ($b)
	{
	for($i=0;$i<$b;$i++)
		{
		echo '<p><strong><a href="/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></strong> - ';
		echo ''.mysql_result($res,$i,'channel_mesg').'</p>';
		}
	}
	else
	{
	echo '<p>Нет каналов</p>';
	}
	
	?><h3>Каналы, на которые Вы подписаны</h3><?
	$res=mysql_query('SELECT * FROM c_u LEFT JOIN channels ON (c_u_channel=channel_id) WHERE c_u_UID="'.$this->filter($this->rights['UID']).'" && channel_admin_UID!="'.$this->filter($this->rights['UID']).'"',$this->lnk);
	$b=mysql_num_rows($res);
	if ($b)
	{
	for($i=0;$i<$b;$i++)
		{
		echo '<p><strong><a href="/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></strong> - ';
		echo ''.mysql_result($res,$i,'channel_mesg').'</p>';
		}
	}
	else
	    {
	    echo '<p>Нет каналов</p>';
	    }
	
	$this->makebottom();
	}
else
	{
	$this->makeheader('Авторизация','');
	?>
	<div class="msg" align="center">
	<form action="" method="post">
	<input name="ssu" type="hidden" value="<? echo session_id();?>" />
	<p><input name="UL" type="text" /></p>
	<p><input name="UP" type="password" /></p>
	<p><input type="submit" value="OK" />
	</form>
	</div>
	<?	
	$this->makebottom();
	}
return true;
}

private function show_channel($a)
{
$c=new CHANNEL($a,$this->USER,$this->lnk);
if ($c)
	{
	$this->makeheader('Канал: '.$a,'');
	$c->show();
	$this->makebottom();
	}
else
	{
	header("HTTP/1.0 404 Not Found");
	}
return true;
}

private function iplog()
{
$this->makeheader('Лог доступа','');
$this->USER->ip_log();
$this->makebottom();
}

private function about()
{
$this->makeheader('О проекте SecChat','');
include("func_about.php");
$this->makebottom();
}

private function admin_users()
{
include ('func_admin_users.php');
}

private function admin_channels()
{
include ('func_admin_channels.php');
}

private function change_pwd()
{
$this->makeheader('Изменить свой пароль','');
include ('func_change_pwd.php');
$this->makebottom();
}


private function panic()
{
	if ($this->rights['admin_channels']==1 and $this->rights['admin_users']==1)
	{
		$this->makeheader('Очистка базы данных','');
		if($_POST['s']==session_id())
		{
		if($_POST['channels']) {echo '<p>Каналы очищены!</p>';mysql_query('TRUNCATE channels',$this->lnk);}
		if($_POST['c_u']) {echo '<p>Членство в каналах сброшено!</p>';mysql_query('TRUNCATE c_u',$this->lnk);}		
		if($_POST['mesg']) {echo '<p>Сообщения удалены!</p>';mysql_query('TRUNCATE mesg',$this->lnk);}
		if($_POST['users']) {echo '<p>Пользователи удалены!</p>';mysql_query('TRUNCATE users',$this->lnk);}
		if($_POST['user_iplog']) {echo '<p>Лог доступа очищен!</p>';mysql_query('TRUNCATE user_iplog',$this->lnk);}						
		}
		?>
		<p>Вы собираетесь очистить базу данных, возможно оно того стоит. Выберите, что удалить.</p>
		<form action="/panic" method="post"><input name="s" type="hidden" value="<? echo session_id();?>">
		<p><input name="channels" type="checkbox" value="1">Каналы</p>
		<p><input name="c_u" type="checkbox" value="1">Членство в каналах</p>
		<p><input name="mesg" type="checkbox" value="1">Сообщения</p>
		<p><input name="users" type="checkbox" value="1">Пользователей</p>	
		<p><input name="user_iplog" type="checkbox" value="1">Протокол доступа пользователей</p>
		<p>Подтверждение <input name="confirm" type="text"> (напишите "DELETE").</p>
		<input name="" type="submit" value="Удалить!">	
		</form>
		<p><strong>Внимание!</strong> Данные ещё возможно будет восстановить с жёсткого диска компьютера, 
		их можно удалить, затерев таблицы базы данных mysql, лучше всего физически уничтожить носитель информации.</p>
		<?
		$this->makebottom();
	}
	else header("HTTP/1.0 404 Not Found");
}

private function install()
{
		$this->makeheader('Инициализация базы данных','');
		include ("func_install.php");
		$this->makebottom();
}

public function dispatcher($q)
{
//var_dump($q);
if($q=='/') $this->mainpage();
elseif(preg_match('~^/c/([A-z0-9_\-]+)~',$q,$a) and $this->rights) $this->show_channel($a[1]);
elseif(preg_match('~^/log/?~',$q) and $this->rights) $this->iplog();
elseif(preg_match('~^/about/?~',$q)) $this->about();
elseif(preg_match('~^/admin_users/?~',$q) and ($this->rights['admin_users']==1 or $this->rights['moder_users']==1) ) $this->admin_users();
elseif(preg_match('~^/admin_channels/?~',$q)  and ($this->rights['admin_channels'] or $this->rights['moder_channels'])) $this->admin_channels();
elseif(preg_match('~^/exit/?~',$q)) {session_destroy();header("Location: /");}
elseif(preg_match('~^/panic/?~',$q) and $this->rights['admin_users']==1 and $this->rights['admin_channels']==1) $this->panic();
elseif(preg_match('~^/install/?~',$q)) $this->install();
elseif(preg_match('~^/change_pwd/?~',$q)) $this->change_pwd();
else header("HTTP/1.0 404 Not Found");
}

///////end class
}
</script>