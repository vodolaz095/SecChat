<?php
class PAGE
{
private $link;
private $USER;
private $rights;
private $ini;
////////////
private function filter($a)
{
return CONTROLLER::v_filter($a,'word');
}


public function __construct($user,$ini,$link)
{
    if (mysql_ping($link)  and get_class($user)=='USER')
        {
        $this->ini=$ini;
        $this->link=$link;
        $this->USER=$user;
        $this->rights=$this->USER->get_user();
        }
        else
        {
        showerror('Невозможно создать класс страницы');
        }
}

public function makeheader($title,$subtitle)
{
require($this->ini['classdir'].'/template/header.php');
}

public function makebottom()
{
require($this->ini['classdir'].'/template/bottom.php');
}


public function mainpage()
{
    if ($this->rights)
    {
    $this->makeheader('Авторизация пройдена','Добро пожаловать, '.$this->rights['U_login'].'!');
    echo '<h3>Ваши каналы</h3>';
    $res=mysql_query('SELECT * FROM channels WHERE channel_admin_UID="'.$this->filter($this->rights['UID']).'"',$this->link);
    $b=mysql_num_rows($res);
    if ($b)
    {
    for($i=0;$i<$b;$i++)
        {
        echo '<p><strong><a href="'.$this->ini['classdir'].'/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></strong> - ';
        echo ''.mysql_result($res,$i,'channel_mesg').'</p>';
        }
    }
    else
    {
    echo '<p>Нет каналов</p>';
    }

    echo '<h3>Каналы, на которые Вы подписаны</h3>';
    $res=mysql_query('SELECT * FROM c_u LEFT JOIN channels ON (c_u_channel=channel_id) WHERE c_u_UID="'.$this->filter($this->rights['UID']).'" && channel_admin_UID!="'.$this->filter($this->rights['UID']).'"',$this->link);
    $b=mysql_num_rows($res);
    if ($b)
    {
    for($i=0;$i<$b;$i++)
        {
        echo '<p><strong><a href="'.$this->basedir.'/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></strong> - ';
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
    $this->USER->make_login_form();
    $this->makebottom();
    }
return true;
}

public function show_channel($a)
{
$c=new CHANNEL($a,$this->USER,$this->link);
if ($c->is_valid())
    {
    $this->makeheader('Канал: '.$a,'');
    $c->show();
    $this->makebottom();
    }
else
    {
    //header("Location: /");
    }
}

public function iplog()
{
$this->makeheader('Лог доступа','');
$this->USER->ip_log();
$this->makebottom();
}

public function about()
{
$this->makeheader('О проекте SecChat','');
?>
<h1>SecChat</h1>
<p>Простой и очень защищённый сервер для чата в реальном времени. Не требует никакого специализированного клиентского программного обеспечения, работает с любыми современными броузерами. Позволяет Администратору ресурса приглашать пользователей, создавать для них каналы, в которых могут в реальном времени переписываться 2 и более пользователей.</p>

<h2>Почему именно этот чат?</h2>
<p>Vkontakte, facebook, одноклассники - неплохая платфрма для чата, но различные правительственные структуры часто закрывают к ним доступ, а также давно могут получить всю информацию о хозяине любой из анкет.</p>
<p>Клиенты многновенных сообщений ICQ и AOL и т.д. - обладают выделенными серверами, доступ к которым вполне можно прекратить на уровне провайдеров.</p>
<p>Системы на основе протокола jabber сложно настраивать и усталавливать - как клиенты, так и серверную часть .</p>
<p>Secchat -просто устанавливается, потребляет мало ресурсов сервера и клиента, не требует установки клиентского программного обеспечения - (в качестве клиента подойдёт почти любой современный броузер - Google Crome, Mozilla Firefox, Opera и т.д.), по умолчанию ведёт передачу всех сообщений через HTTPS (защищённый протокол, который достаточно сложно перехватить сниферами). работает на OpenSource программном обеспечении, свободном от "прослушки" и транслирует сообщеняи в реальном времени.</p>

<h2>Установка</h2>
<ol>
<li>Загрузите актуальную версию данного пакета</li>
<li>Установите сервер Apache c расширениями PHP+ssl. В сборке <a href="/about#xampp">XAMPP</a> уже всё настроено и заранее установлено. Дальнейшая инструкция предоставленна именно для этой сборки</li>
<li>В файле /opt/lampp/etc/httpd.conf   измените строку <em>DocumentRoot  /opt/lampp/htdocs</em> на <em>DocumentRoot /opt/lampp/htdocs/www</em></li>
<li>В файле /opt/lampp/etc/extra/httpd-ssl.conf  также измените строку <em>DocumentRoot /opt/lampp/htdocs</em> на <em>DocumentRoot /opt/lampp/htdocs/www</em> - этим вы включите работу программы через защищённое соединение https</li>
<li>Создайте пустую базу данных для программы - подробнее - см руководство к lampp и mysql</li>
<li>В файле  <em>/opt/lampp/htdocs/secchat-utf8/secchat.ini </em> укажите реквизиты для доступа к базе данных MySQL - логин, пароль, название базы данных. Если база данных находится на другом сервере, то следует указать is_persistent=1 - так соединение будет оптимизированно для работы с внешней базой данных. Реквизиты указываются в блоке <strong>[Settings for connection to databasae]</strong> </li>
<li>Рекомендуем также указать, чтобы программа всегда требовала защищённого соединения - напишие force_https=1 в блоке <strong>[General security settings]</strong></li>
<li>В файле настроек secchat.ini параметр basedir - директория на сервере, в которой установлен SecChat, если он не установлен в корневую директорию. Например, Вы хотите, чтобы SecChat был доступен по адресу <b>example.com/secchat</b>.Для этого Вам надо создать на сервере директорию secchat в директории html, поместить туда все файлы из папки html. А в файле secchat.ini прописать путь <b>/secchat</b></li>
<li>В файле настроек secchat.ini параметр secchat_classes_dir=/var/www/secchat-utf8 - путь к системным файлам программы</li>
<li>Сайт с чатом теперь должен заработать - осталось только инициализировать базу данных.</li>
<li>Перейдите по этой локальной ссылке на сайте - <a href="<?php echo $this->ini['basedir']; ?>/install"><?php echo 'http://'.$_SERVER['HTTP_HOST'].$this->ini['basedir'].'/install'; ?></a> и инициализируйте базу данных,  введя пароль Администратра Сайта  (root)</li>
<li>Зайдите на сайт от имени администратора, создайте каналы и пригласите пользователей, сообщите пользователям логины и пароли, и они уже могут общаться на сайте</li>
<li>Настройте права доступа пользователям.</li>
</ol>

<h2>Установка прав доступа для  пользователей</h2>
<ul>
<li><strong>Активен</strong> - пользователь может авторизоваться на сайте</li>
<li><strong>Администратор пользователей</strong> - пользователь  может менять права всех других пользователей, а также создавать новых.</li>
<li><strong>Модератор пользователей</strong> - пользователь может создавать новых пользователей с минимальным набором прав, активировать  пользователей, и менять им пароль. Модератор может менять параметры только тех пользователей, которых он создал!</li>
<li><strong>Администратор каналов</strong> - может создавать новые каналы, редактировать все каналы, подписывать на них пользователей.</li>
<li><strong>Модератор каналов</strong> - может создавать новые каналы, редактировать свои каналы, подписывать на них пользователей.</li>
</ul>

<h2>Протокол доступа пользователей к чату - описание возможных записей в базу данных доступа</h2>
<ul>
<li>"Успешно" - пользователь ввёл правильное имя и пароль, при этом он работает с системой с разрешённого IP адреса.</li>
<li>"Ошибка пароля" - пользователь ввёл не правильную пару имени и пароля.</li>
<li>"Кража сессии" - злоумышленик пытался украсть идентификатор сессии авторизированного пользователя. В этом  случае рекомендуется проверить компьютер пострадавшего пользователя  на вирусы, и сообщить IP адрес злоумышленика в правоохранительные органы. К счастью, даже при попытке кражи идентификатора сессии пароль пользователя не будет извесен злоумышленику!</li>
</ul>

<h2>Полезные ссылки</h2>
<ol>
<li><a href="https://github.com/vodolaz095/SecChat">https://github.com/vodolaz095/SecChat</a> - Репозиторий этого чат-сервера</li>
<li><a name="xampp" id="xampp"></a><a href="http://www.apachefriends.org/en/xampp.html">http://www.apachefriends.org/en/xampp.html</a> - Готовый сервер, на котором тестировался данный чат</li>
<li><a href="http://fotobase.org">http://fotobase.org</a> - Социальная сеть для профессионалов фотобизнеса - фотографов, фотомоделей, визажистов, ретушёров</li>
<li><a href="http://www.lissyara.su/articles/freebsd/www/apache_2.2.0+ssl/">http://www.lissyara.su/articles/freebsd/www/apache_2.2.0+ssl/</a> - статья о настройке сервера для этой программы</li>
</ol>

<h2>Лицензинное соглашение</h2>
<p>Данный продукт можно бесплатно распространять на любых носителях и использовать в любых целях, при условии, что исходный код продукта не изменялся.</p>
<p>Автор программы ни при каких условиях не несёт ответственности за любые проблемы, которые могут у Вас возникнуть из-за использования этой программы.</p>
<p>Программа поставляется как есть - её работоспособность не гарантируется.</p>
<p>Программа поставляется по лицензии <strong>GNU LESSER GENERAL PUBLIC LICENSE</strong></p>
<p><a href="http://www.gnu.org/copyleft/lesser.html">http://www.gnu.org/copyleft/lesser.html</a></p>
<p><a href="http://ru.wikisource.org/wiki/GNU_Lesser_General_Public_License">Перевод лицензии</a></p>
<?php
$this->makebottom();
}


public function admin_users()
{
$this->makeheader('Редактировать пользователей','');
$admin=new ADMIN($this->USER,$this->ini,$this->link);
$admin->admin_users();
$this->makebottom();
}

public function admin_channels()
{
$this->makeheader('Редактировать каналы','');
$admin=new ADMIN($this->USER,$this->ini,$this->link);
$admin->admin_channels();
$this->makebottom();
}

public function change_pwd()
{
$this->makeheader('Изменить свой пароль','');
$this->USER->change_pwd();
$this->makebottom();
}


public function panic()
{
$this->makeheader('Очистка базы данных','');
$admin=new ADMIN($this->USER,$this->ini,$this->link);
$admin->panic();
$this->makebottom();
}

private function catch_mysql_error($a)
{

if(mysql_error($this->link))
    {
    echo '<p><b>Ошибка MySQL базы данных</b></p>';
    echo '<p>'.$a.'</p>';
    echo '<p>'.mysql_error($this->link).'</p>';
    }
}

public function install()
{
$this->makeheader('Инициализация базы данных','');
$res=mysql_query('SHOW TABLE STATUS',$this->link);

$b=mysql_num_rows($res);
for ($i=0;$i<$b;$i++)
    {
    if (mysql_result($res,$i,'Name')=='c_u')
        {
        echo '<p>Таблица - "Пользователь-Канал" верна!</p>';
        $ok++;
        }
    elseif (mysql_result($res,$i,'Name')=='channels')
        {
        $ok++;
        echo '<p>Таблица - "Каналы" верна!</p>';
        }
    elseif (mysql_result($res,$i,'Name')=='mesg')
        {
        $ok++;
        echo '<p>Таблица - "Сообщения" верна!</p>';
        }
    elseif (mysql_result($res,$i,'Name')=='user_iplog')
        {
        $ok++;
        echo '<p>Таблица - "Протокол доступа" верна!</p>';
        }

    elseif (mysql_result($res,$i,'Name')=='users')
        {
        $ok++;
        echo '<p>Таблица - "Пользователи" верна!</p>';
        }

    elseif (mysql_result($res,$i,'Name')=='online')
        {
        $ok++;
        echo '<p>Таблица - "Пользователи на сайте" верна!</p>';
        }


    else $ok--;
    }

if ($ok==6)
{
?>
<p>База данных успещно инициализирована!</p>
<p>Инициализировать её не надо!</p>
<?php
}
else
{
if(CONTROLLER::validate_request($_POST['init_db']))
    {
    if($_POST['root_pwd1']==$_POST['root_pwd2'])
    {
    echo '<p>Инициализируем базу данных!</p>';

    mysql_query('SET NAMES utf8',$this->link);


    $this->catch_mysql_error('Не получилось установить нужную кодировку базы данных');
    mysql_query('SET foreign_key_checks = 0',$this->link);
    $this->catch_mysql_error('Не получилось выключить внешние ключи');

    mysql_query('DROP TABLE IF EXISTS `channels`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу channels');
    mysql_query(
'CREATE TABLE `channels` (
  `channel_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `channel_name` char(32) DEFAULT NULL,
  `channel_admin_UID` int(6) DEFAULT NULL,
  `channel_mesg` text,
  PRIMARY KEY (`channel_id`),
  UNIQUE KEY `channel_name_UNIQUE` (`channel_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу channels');

    mysql_query('DROP TABLE IF EXISTS `online`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу mesg');
    mysql_query('CREATE TABLE `online` (
      `onlinesince` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `UID` int(6) NOT NULL,
      PRIMARY KEY (`onlinesince`),
      UNIQUE KEY `UID` (`UID`)
    ) ENGINE=MEMORY DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу online');


    mysql_query('DROP TABLE IF EXISTS `user_iplog`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу user_iplog');
    mysql_query('CREATE TABLE `user_iplog` (
      `UI_key` int(7) unsigned NOT NULL AUTO_INCREMENT,
      `DTS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `login` char(32) NOT NULL,
      `IP` char(16) NOT NULL,
      `status` tinyint(1) NOT NULL,
      `useragent` text NOT NULL,
      PRIMARY KEY (`UI_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу user_iplog');

    mysql_query('DROP TABLE IF EXISTS `users`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу users');
    mysql_query('CREATE TABLE `users` (
      `UID` int(6) unsigned NOT NULL AUTO_INCREMENT,
      `U_login` char(32) NOT NULL,
      `U_pwd` char(32) NOT NULL,
      `U_active` tinyint(4) DEFAULT NULL,
      `admin_users` tinyint(1) DEFAULT NULL,
      `moder_users` tinyint(1) DEFAULT NULL,
      `admin_channels` tinyint(1) DEFAULT NULL,
      `moder_channels` tinyint(1) DEFAULT NULL,
      `U_host` int(6) unsigned DEFAULT NULL,
      PRIMARY KEY (`UID`),
      UNIQUE KEY `U_login_UNIQUE` (`U_login`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу users');


    mysql_query('DROP TABLE IF EXISTS `mesg`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу mesg');
    mysql_query('CREATE TABLE `mesg` (
  `mesg_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `mesg_UID` int(3) unsigned DEFAULT NULL,
  `mesg_IP` char(16) DEFAULT NULL,
  `mesg_channel` int(3) unsigned DEFAULT NULL,
  `mesg_DTS` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mesg_TXT` text,
  PRIMARY KEY (`mesg_id`),
  KEY `mesg_UID` (`mesg_UID`),
  KEY `mesg_channel` (`mesg_channel`),
  CONSTRAINT `mesg_ibfk_4` FOREIGN KEY (`mesg_UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mesg_ibfk_3` FOREIGN KEY (`mesg_channel`) REFERENCES `channels` (`channel_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу mesg');

    mysql_query('DROP TABLE IF EXISTS `c_u`',$this->link);
    $this->catch_mysql_error('Не получилось удалить таблицу c_u');
    mysql_query('CREATE TABLE `c_u` (
  `c_u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `c_u_channel` int(6) unsigned DEFAULT NULL,
  `c_u_UID` int(6) unsigned DEFAULT NULL,
  PRIMARY KEY (`c_u_id`),
  KEY `c_u_channel` (`c_u_channel`),
  KEY `c_u_UID` (`c_u_UID`),
  CONSTRAINT `c_u_ibfk_4` FOREIGN KEY (`c_u_UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `c_u_ibfk_3` FOREIGN KEY (`c_u_channel`) REFERENCES `channels` (`channel_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8',$this->link);
    $this->catch_mysql_error('Не получилось создать таблицу c_u');


    mysql_query('INSERT INTO `users`
    (`U_login`, `U_pwd`, `U_active`, `admin_users`, `moder_users`, `admin_channels`, `moder_channels`, `U_host`)
    VALUES ("root", md5("'.$this->filter($_POST['root_pwd1']).'"), 1, 1, 1, 1, 1, NULL);',$this->link);
    $this->catch_mysql_error('Не получилось создать пользователя root');

    mysql_query('SET foreign_key_checks = ',$this->link);
    $this->catch_mysql_error('Не получилось включить внешние ключи!');


    }
    else
    {
    echo '<p>Ошибка! Пароли для Администратора не совпадают!</p>';
    }

if (!mysql_error($this->link))
echo '<p>База данных успешно инициализирована!</p>';
else
echo '<p>Похоже, ещё не всё заработало...</p><a href="'.$this->ini['basedir'].'/install">Протестировать базу данных ещё раз...</a>';

}
else
    {
?>
<p>База данных повреждена или же отсутствует.</p>
<p>Для работы с программным продуктом её надо инициализировать!!!</p>
<form action="<?php echo $this->ini['basedir']; ?>/install" method="post">
<input name="init_db" value="<?php echo CONTROLLER::create_s(); ?>" type="hidden" />
<p>Пароль для Администратора ресурса ( имя пользователя - <strong>root</strong> )</p>
<p><input name="root_pwd1" type="password" /></p>
<p><input name="root_pwd2" type="password" /></p>
<input name="" type="submit" value="Инициализировать базу данных" />
</form>
<?php
    }
}

$this->makebottom();
}

///////end class
}
?>
