<script language="php">
header( 'Content-Type: text/html; charset=windows-1251;' );
$link=$this->lnk;

$res=mysql_query('SHOW TABLE STATUS',$link);
$b=mysql_num_rows($res);
for ($i=0;$i<$b;$i++)
	{
	if (mysql_result($res,$i,'Name')=='c_u') $ok++;
	elseif (mysql_result($res,$i,'Name')=='channels') $ok++;
	elseif (mysql_result($res,$i,'Name')=='mesg') $ok++;	
	elseif (mysql_result($res,$i,'Name')=='user_iplog') $ok++;
	elseif (mysql_result($res,$i,'Name')=='users') $ok++;	
	else $ok--;
	}

if ($ok==5)
{
?>
<p>База данных успещно инициализирована!</p>
<p>Инициализировать её не надо!</p>
<?
}
else
{
?>
<p>База данных повреждена или же отсутствует.</p>
<p>Для работы с программным продуктов её надо инициализировать!!!</p>
<form action="/install" method="post">
<input name="init_db" value="<? echo session_id();?>" type="hidden" />
<p>Пароль для Администратора ресурса ( имя пользователя - <strong>root</strong> )</p>
<p><input name="root_pwd1" type="password" /></p>
<p><input name="root_pwd2" type="password" /></p>
<input name="" type="submit" value="Инициализировать базу данных" />
</form>
<?
$no_db=true;
}



if ($_POST['init_db']==session_id() and $no_db)
{
if($_POST['root_pwd1']==$_POST['root_pwd2'])
	{
	mysql_query('DROP TABLE IF EXISTS `c_u`',$link);
	mysql_query('CREATE TABLE `c_u` (
	  `c_u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `c_u_channel` int(6) DEFAULT NULL,
	  `c_u_UID` int(6) DEFAULT NULL,
	  PRIMARY KEY (`c_u_id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs',$link);
	
	mysql_query('TRUNCATE `c_u`',$link);
	
	mysql_query('DROP TABLE IF EXISTS `channels`',$link);
	
	mysql_query('CREATE TABLE `channels` (
	  `channel_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
	  `channel_name` char(32) COLLATE cp1251_general_cs DEFAULT NULL,
	  `channel_admin_UID` int(6) DEFAULT NULL,
	  `channel_mesg` text COLLATE cp1251_general_cs,
	  PRIMARY KEY (`channel_id`),
	  UNIQUE KEY `channel_name_UNIQUE` (`channel_name`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs',$link);
	
	
	mysql_query('TRUNCATE `channels`',$link);
	
	mysql_query('DROP TABLE IF EXISTS `mesg`',$link);
	
	mysql_query('CREATE TABLE `mesg` (
	  `mesg_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
	  `mesg_UID` int(3) unsigned DEFAULT NULL,
	  `mesg_IP` char(16) COLLATE cp1251_general_cs DEFAULT NULL,
	  `mesg_channel` int(3) unsigned DEFAULT NULL,
	  `mesg_DTS` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  `mesg_TXT` text COLLATE cp1251_general_cs,
	  PRIMARY KEY (`mesg_id`),
	  KEY `mesg_UID` (`mesg_UID`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs',$link);
	
	mysql_query('TRUNCATE `mesg`',$link);
	
	mysql_query('DROP TABLE IF EXISTS `user_iplog`',$link);
	
	mysql_query('CREATE TABLE `user_iplog` (
	  `UI_key` int(7) unsigned NOT NULL AUTO_INCREMENT,
	  `DTS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `login` char(32) NOT NULL,
	  `IP` char(16) NOT NULL,
	  `status` tinyint(1) NOT NULL,
	  `useragent` text NOT NULL,
	  PRIMARY KEY (`UI_key`)
	) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251',$link);
	
	mysql_query('TRUNCATE `user_iplog`',$link);
	
	mysql_query('DROP TABLE IF EXISTS `users`',$link);
	
	mysql_query('CREATE TABLE `users` (
	  `UID` int(6) unsigned NOT NULL AUTO_INCREMENT,
	  `U_login` char(32) COLLATE cp1251_general_cs DEFAULT NULL,
	  `U_pwd` char(32) COLLATE cp1251_general_cs DEFAULT NULL,
	  `U_active` tinyint(4) DEFAULT NULL,
	  `admin_users` tinyint(1) DEFAULT NULL,
	  `moder_users` tinyint(1) DEFAULT NULL,
	  `admin_channels` tinyint(1) DEFAULT NULL,
	  `moder_channels` tinyint(1) DEFAULT NULL,
	  `U_host` int(6) unsigned DEFAULT NULL,
	  PRIMARY KEY (`UID`),
	  UNIQUE KEY `U_login_UNIQUE` (`U_login`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs',$link);
	
	mysql_query('TRUNCATE `users`',$link);
	mysql_query('INSERT INTO `users` (`UID`, `U_login`, `U_pwd`, `U_active`, `admin_users`, `moder_users`, `admin_channels`, `moder_channels`, `U_host`) VALUES (1,	"root",	md5("'.$_POST['root_pwd1'].'"),	1,	1,	1,	1,	1,	NULL)',$link);
	
	mysql_query('DROP TABLE IF EXISTS `online`',$link);
	
	mysql_query('CREATE TABLE `online` (
  `onlinesince` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UID` int(6) NOT NULL,
  PRIMARY KEY  (`onlinesince`),
  UNIQUE KEY `UID` (`UID`)
) ENGINE=MEMORY DEFAULT CHARSET=cp1251 COLLATE=cp1251_general_cs',$link);
	
	}
	else
	{
	echo '<p>Ошибка! пароли для Администратра ресурса на совпадают!</p>';
	}
}

</script>