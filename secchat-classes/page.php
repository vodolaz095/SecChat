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
		die("No connection to db");
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
	echo 'Ляляля, 3 рубля!';
	$this->makebottom();
}

private function admin_users()
{
	$this->makeheader('Редактировать пользователей','');
	if ($this->rights['admin_users']==1) $s=2;
	elseif ($this->rights['moder_users']==1)  $s=1;
	else  $s=0;


	if($s>0)
	{
	if ($_POST['s']==session_id())
		{
		if ($_POST['unmake_admin_users'] and $this->rights['admin_users']==1)
			{
			$qqq='UPDATE users SET admin_users=0 WHERE UID="'.$this->filter($_POST['unmake_admin_users']).'"';
			mysql_query($qqq,$this->lnk);
			}

		if ($_POST['make_admin_users'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET admin_users=1 WHERE UID="'.$this->filter($_POST['make_admin_users']).'"',$this->lnk);

		if ($_POST['unmake_moder_users'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET moder_users=0 WHERE UID="'.$this->filter($_POST['unmake_moder_users']).'"',$this->lnk);

		if ($_POST['make_moder_users'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET moder_users=1 WHERE UID="'.$this->filter($_POST['make_moder_users']).'"',$this->lnk);

		if ($_POST['make_admin_channels'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET admin_channels=1 WHERE UID="'.$this->filter($_POST['make_admin_channels']).'"',$this->lnk);

		if ($_POST['unmake_admin_channels'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET admin_channels=0 WHERE UID="'.$this->filter($_POST['unmake_admin_channels']).'"',$this->lnk);

		if ($_POST['make_moder_channels'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET moder_channels=1 WHERE UID="'.$this->filter($_POST['make_moder_channels']).'"',$this->lnk);

		if ($_POST['unmake_moder_channels'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET moder_channels=0 WHERE UID="'.$this->filter($_POST['unmake_moder_channels']).'"',$this->lnk);
			
			if ($_POST['make_active'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET U_active=1 WHERE UID="'.$this->filter($_POST['make_active']).'"',$this->lnk);

		if ($_POST['unmake_active'] and $this->rights['admin_users']==1)
			mysql_query('UPDATE users SET U_active=0 WHERE UID="'.$this->filter($_POST['unmake_active']).'"',$this->lnk);

		if ($_POST['create_new_user'])	
			{
			$pwd=strtoupper(substr((md5(date('c'))),0,8));
			echo '<p>Пользователь создан! Логин <strong>'.$_POST['create_new_user'].'</strong>. Пароль <strong>'.$pwd.'</strong></p>';
			$qqq='INSERT INTO users(U_login,U_pwd) VALUES ("'.$this->filter_txt($_POST['create_new_user']).'","'.$pwd.'")';
//			echo $qqq;
			mysql_query($qqq,$this->lnk);
			}
			echo mysql_error($this->lnk);
		}
////////////////////////////	
	
	if ($s==2) $res=mysql_query('SELECT * FROM users WHERE UID!="'.$this->rights['UID'].'" ORDER BY U_login ASC',$this->lnk);	
	elseif ($s==1) $res=mysql_query('SELECT * FROM users WHERE U_host="'.$this->rights['UID'].'" ORDER BY U_login ASC',$this->lnk);	
	
	$d=mysql_num_rows($res);
	if($d)
		{

echo '		<table border="1" cellpadding="3" cellspacing="0" align="center">';
echo '		<tr>';
echo '		<td>Имя</td>';
echo '		<td>Пароль</td>';
echo '		<td>Активен</td>';


		if($s==2)
		{
		echo '		<td>Адм. пользователей</td>';		
		echo '		<td>Модератор пользователей</td>';
		echo '		<td>Адм. каналов</td>';
		echo '		<td>Модератор каналов</td>';				
		}
			 echo '		</tr>';

		for($i=0;$i<$d;$i++)		
			{

echo '		<tr align="center" valign="middle">';
echo '		<td><p>'.mysql_result($res,$i,'U_login').'</p></td>';
echo '		<td>';
?>
<form action="" method="post" name="new_pwdf">
<input name="s" value="<? echo session_id();?>" type="hidden" id="new_pwd_s">
<input name="new_pwd" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="new_pwd_val" type="password"><input name="" type="submit" value="Задать"></p>
</form>

<?
echo '</td>';
echo '<td>';
			if (mysql_result($res,$i,'U_active')==1)
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="unmake_active" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>
</form>
<?
			}
			else
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="make_active" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>
</form>
<?
			
			}
			echo '</td>';


		if($s==2) 
		{
			echo '<td>';
			if (mysql_result($res,$i,'admin_users')==1)
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="unmake_admin_users" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>
</form>
<?
			}
			else
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="make_admin_users" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>
</form>
<?
			
			}
			echo '</td>';
			echo '<td>';

			if (mysql_result($res,$i,'moder_users')==1)
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="unmake_moder_users" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>
</form>
<?
			}
			else
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="make_moder_users" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>
</form>
<?
			
			}
		echo '</td>';
		echo '<td>';
			if (mysql_result($res,$i,'admin_channels')==1)
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="unmake_admin_channels" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>
</form>
<?
			}
			else
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="make_admin_channels" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>
</form>
<?
			
			}
		echo '</td>';		
		echo '<td>';
			if (mysql_result($res,$i,'moder_channels')==1)
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="unmake_moder_channels" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>
</form>
<?
			}
			else
			{
?>
<form action="" method="post">
<input name="s" value="<? echo session_id();?>" type="hidden">
<input name="make_moder_channels" value="<? echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>
</form>
<?
			
			}
			echo '</td>';
		}
				
		
			 echo '		</tr>';

			
			}
		echo '		</table>';
		}	
	
	}
	
	?>
	<h3>Создать нового пользователя</h3>
	<form action="" method="post">
	<input name="s" type="hidden" value="<? echo session_id();?>">
	<p><input name="create_new_user" type="text"><input name="" type="submit" value="OK"></p>
	</form>
	<h3>Помощь</h3>
	<p>На этой странице можно редактировать пользователей, которых Вы создали если Вы- Модератор Пользователей, или же всех пользователей, если Вы - Администратор Пользователей.</p>
	<p>Описание прав пользователей</p>
	<ul>
	<li><strong>Активен</strong> - пользователь может авторизоваться на сайте</li>
	<li><strong>Администратор пользователей</strong> - пользователь  может менять права всех других пользователей, а также создавать новых.</li>
	<li><strong>Модератор пользователей</strong> - пользователь может создавать новых пользователей с минимальным набором прав, активировать  пользователей, и менять им пароль. Модератор может менять параметры толлько тех пользователей, которых он создал!</li>	
	<li><strong>Администратор каналов</strong> - может создавать новые каналы, редактировать все каналы, подписывать на них пользователей.</li>	
	<li><strong>Модератор каналов</strong> - может создавать новые каналы, редактировать свои каналы, подписывать на них пользователей.</li>
	</ul>
	<?
	
	
		
	$this->makebottom();
}

private function admin_channels()
{
	if ($this->rights['admin_channels']==1) 
	$qqq='SELECT *,COUNT(c_u_id) AS num_users FROM channels 
	LEFT JOIN c_u ON (c_u_channel=channel_id) 
	LEFT JOIN users ON (UID=channel_admin_UID) 
	GROUP BY channel_id ORDER BY channel_name ASC';

	elseif ($this->rights['moder_channels']==1)
	$qqq='SELECT *,COUNT(c_u_id) AS num_users FROM channels 
	LEFT JOIN c_u ON (c_u_channel=channel_id) 
	LEFT JOIN users ON (UID=channel_admin_UID) 
	WHERE channel_admin_UID="'.$this->rights['UID'].'"
	GROUP BY channel_id ORDER BY channel_name ASC';
	else die('error 580');

	$this->makeheader('Редактировать каналы','');
	
		if($_POST['s']==session_id())
		{
		if($_POST['edit']) {echo 'edit';}
		if($_POST['new']) {echo 'edit';}
		if($_POST['del']) {echo 'edit';}		
		}

	
	$res=mysql_query($qqq,$this->lnk);
	$b=mysql_num_rows($res);
	if ($b)
	{
	?>
	<table border="1" cellpadding="3" cellspacing="0" width="90%">
	<tr>
	<td>Название</td>
	<td>Описание</td>
	<td>Администратор</td>
	<td>Количество пользователей</td>
	</tr>
	<?
	for($i=0;$i<$b;$i++)
		{
	echo '<tr>';
	echo '<td><a href="/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></td>';
	echo '<td>'.mysql_result($res,$i,'channel_mesg').'</td>';
	echo '<td>'.mysql_result($res,$i,'U_login').'</td>';
	echo '<td align="center">'.mysql_result($res,$i,'num_users').'</td>';
	echo '</tr>';
		}
	echo '</table>';
	}

	
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
else header("HTTP/1.0 404 Not Found");
}
}
</script>