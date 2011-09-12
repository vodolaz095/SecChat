<?php
if (get_class($this)=='PAGE')
{
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<link rel="shortcut icon" href="<?php echo $this->ini['basedir'];?>/favicon.ico" type="image/x-icon" />
<link rel="icon" href="<?php echo $this->ini['basedir'];?>/favicon.gif" type="image/gif" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $this->ini['basedir'];?>/style.css" rel="stylesheet" type="text/css" />
<title><?php echo $title;?></title>
<script language="javascript" type="text/javascript">
var basedir="<?php echo $this->ini['basedir'];?>";
</script>
<script language="javascript" src="<?php echo $this->ini['basedir'];?>/jquery.min.js" type="text/javascript"></script>
<script language="javascript" src="<?php echo $this->ini['basedir'];?>/ajax.js?a=<? echo date('U');?>" type="text/javascript"></script>
</head>
<body>
<div id="wrap">
<div id="header">
    <h1><?php echo $title;?></h1>
    <h2><?php echo $subtitle;?></h2>
</div>

<div id="nav">
    <ul>
        <li><a href="<?php echo $this->ini['basedir'];?>/">Главная</a></li>
        <li><a href="<?php echo $this->ini['basedir'];?>/about">О проекте</a></li>
<?php
if ($this->rights)
{
if ($this->rights['admin_users'] or $this->ini['basedir']['moder_users'])
echo '        <li><a href="'.$this->ini['basedir'].'/admin_users">Пользователи</a></li>';

if ($this->rights['admin_channels'] or $this->ini['basedir']['moder_channels'])
echo '      <li><a href="'.$this->ini['basedir'].'/admin_channels">Редактировать каналы</a></li>';

if ($this->rights['UID']==1 and $this->ini['basedir']['admin_channels']==1 and $this->rights['admin_users']==1)
echo '     <li><a href="'.$this->ini['basedir'].'/panic">Тревога</a></li>';

echo '      <li><a href="'.$this->ini['basedir'].'/log">Протокол доступа</a></li>';
echo '      <li><a href="'.$this->ini['basedir'].'/change_pwd">Сменить пароль</a></li>';
echo '      <li><a href="'.$this->ini['basedir'].'/exit">Выход</a></li>';
}
?>
    </ul>
</div>
<div id="content">
<?php
}

