<?php
class CONTROLLER
{
/*
 * Главный контролер проекта - аргументы - текущий экземпляр класса USER,
 * экземпляр класса PAGE, $basedir - директория относительно корневой
 * директории вэбсервера
 */
private $basedir;
private $PAGE;
private $USER;
private $rights;
private $classdir;
private $link;
private $ini;
/////////
public function __construct($ini)
{

if ($ini['is_persistent']==1)
    {
    $lnk=@mysql_pconnect($ini['host'],$ini['login'],$ini['password']);
    }
else
    {
    $lnk=@mysql_connect($ini['host'],$ini['login'],$ini['password']);
    }

if ($lnk)
    {
    mysql_select_db($ini['database_name'],$lnk);
    $this->link=$lnk;
    }
else showerror('Невозможно установить соединения с базой данных!');


if(mysql_ping($this->link))
    {
    $this->ini=$ini;


    require ($this->ini['classdir'].'/model/user.php');
    $this->USER=new USER($this->ini,$this->link);

    //$this->USER->test();
    //exit();

    $this->rights=$this->USER->get_user();


    require ($this->ini['classdir'].'/model/page.php');
    $this->PAGE=new PAGE($this->USER,$this->ini,$this->link);

    require ($this->ini['classdir'].'/model/admin.php');
    require ($this->ini['classdir'].'/model/channel.php');
    require ($this->ini['classdir'].'/template/message.php');
    }
    else
    {
    showerror('Невозможно создать класс страницы');
    }

}
////
static public function v_filter($a,$mode='int')
{
if(is_numeric($a) and $mode=='int')
    {
    $b=mysql_real_escape_string($a);
    }
elseif(preg_match('~(*UTF8)^[а-яА-Яa-zA-Z0-9/./-\s]+$~',$a) and $mode=='word')
    {
    $b=mysql_real_escape_string($a);
    }
elseif($mode=='text')
    {
    $a=trim($a);

        $a=$rawtext;
        $inner_link_separator=md5(time());
        //если есть правильная и индексируемая внешняя ссылка - оставляем от неё только адрес!!!
        $a=preg_replace("~(*UTF8)<a[^>]*href=['\"]*(http://[^\'\">]*)['\"]*[^>]*>[\s\S]*</a>~i",' \\1 '.PHP_EOL,$a);
        $a=preg_replace("~(*UTF8)<a[^>]*href=['\"]*(https://[^\'\">]*)['\"]*[^>]*>[\s\S]*</a>~i",' \\1 '.PHP_EOL,$a);

        //если есть локальная (внутренняя) ссылка - оставляем от неё только адрес сайта в правильном формате!!!
        $a=preg_replace("~(*UTF8)<a[^>]*href=['\"](\/[^\'\">]*)['\"]*[^>]*>[\s\S]*</a>~i",$inner_link_separator.' \\1 '.$inner_link_separator.PHP_EOL,$a);

        //удаляем все ссылки вообще, например с джаваскриптами, и base64_encoded, ftp
        $a=preg_replace('~(*UTF8)<a[^>]*>~i',NULL,$a);
        $a=preg_replace('~(*UTF8)</a>~i',NULL,$a);
        //но их тексты оставляем)))

        //убираем все свойства тэгов, но оставляем теги
        $a=preg_replace('~(*UTF8)<([A-Za-z0-9]+)\s*([^<>]*)>~im','<\\1>',$a);

        //удаляем оставшиеся ссылки
        $a=preg_replace("~(*UTF8)<table~i",'<table border="1" ',$a); //показываем таблицы - криво, но уж что с этим поделать

        //убираем опасные элементы
        $a=preg_replace('~(*UTF8)<img[^>]*>~i',NULL,$a);//картинки
        $a=preg_replace('~(*UTF8)<script[^>]*?>.*?</script>~sim',NULL,$a);//скрипты, выполняемые на клиентской стороне

        $a=preg_replace('~(*UTF8)<iframe.*>.*</iframe>~im',NULL,$a);//фреймы
        $a=preg_replace('~(*UTF8)<FRAME\s[^>]*>~im',NULL,$a);//фреймы
        $a=preg_replace('~(*UTF8)</?frameset[^>]*>~im',NULL,$a);//фреймы

        $a=preg_replace('~(*UTF8)</?NOFRAMES>~im',NULL,$a);//тексты, которые показываюься при отсутствии фреймов, показываются всегда

        $a=preg_replace("~(*UTF8)</?div>~i",NULL,$a); //удаляем теги div - чтобы не ломалась вёрстка



        //после того, как удалили всё ненужное из текста

        //делаем локальные ссылки c помощью модуля редиректа из URL внешних адресов в тексте
        $retrans_uri='/away?url='; /*@todo@ доделать.... */
        $a=preg_replace("~(*UTF8) https?://([^\s]*) ~i",'<a href="'.$retrans_uri.'\\1">\\1</a>',$a);


        //делаем локальные ссылки из локальных URL
        $a=preg_replace("~(*UTF8)$inner_link_separator ([^\s]+) $inner_link_separator~i",'<a href="\\1">\\1</a>',$a);

    $b=mysql_real_escape_string($a);
    }
else
    {
    $b=null;
    }
return $b;
}
////

/*
 *
 * защита от сабмита с других сайтов...
 *http://www.codinghorror.com/blog/2008/10/preventing-csrf-and-xsrf-attacks.html
 */
static public function validate_request($v)
    {
        /*
         * csrf защита
         * Проверка, отправлена ли форма с этого же сайта?
         */
        if(isset($_SESSION['bik']))
        {
            if (md5($_SERVER['HTTP_REFERER'].session_id().$_SESSION['bik']) == $v)
                {
                $nextsec=time()+1;
                $_SESSION['bik']=md5($_SESSION['bik']);
                return (true);
                }
            else
                return false;
        }
        else return false;
    }

static public function create_s()
        {
            /*
             * Проверка, отправлена ли форма с этого же сайта?
             */
            if(!isset($_SESSION['bik']))
                {
                    $_SESSION['bik']=md5(time().'secchat');
                }

            if(isset($_SERVER['HTTPS']))
            $a='https://';
            else
            $a='http://';

            $a=$a.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].session_id().$_SESSION['bik'];
        return md5($a);
        }
/*
 *
 *
 *
 */



/*
 * AJAX обработчики
 *
 */

private function msg_handler()
{
//var_dump($_POST);
$channel=new CHANNEL($_POST['URI'],$this->USER,$this->link);
if (CONTROLLER::validate_request($_POST['s']) and $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')
    {
    if($_POST['count']==1) echo $channel->count_mesg();
    elseif($_POST['list'])
        {
        $channel->list_mesg($_POST['list']);
        }
    elseif ($_POST['new_mesg'])
        {
        $channel->post($_POST,true);
        $channel->list_mesg(1);
        }
    else header("HTTP/1.0 404 Not Found");
    }
    else header("HTTP/1.0 404 Not Found");
}

private function time_handler()
{
if ($this->rights)
    {
    $q="INSERT INTO online (UID) VALUES
    ('".$this->rights['UID']."')
    ON DUPLICATE KEY UPDATE onlinesince=CURRENT_TIMESTAMP";
    mysql_query($q,$this->link);
    }
echo date('H:i:s');
}

private function validator()
{

if ($_POST['s']==session_id() and $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')
{
    if($_POST['new_U'])
        {
        if(preg_match('~[A-z0-9_\-]+~',$_POST['new_U']))
            {
            $res=mysql_query('
            SELECT * FROM  users
            WHERE U_login="'.$this->v_filter($_POST['new_U'],'word').'"'
            ,$this->link);

            if (mysql_num_rows($res)==0)
            echo 'Имя пользователя свободно!';
            else
            echo 'Ошибка! Такой пользователь уже существует!';
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
            $res=mysql_query('SELECT * FROM  users  WHERE U_login="'.$this->v_filter($_POST['select_U'],'word').'"',$this->link);
            if (mysql_num_rows($res)==1)
            echo 'Такой пользователь существует!';
            else
            echo 'Ошибка! Такого пользователя нет!';
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

}

///
public function dispatcher($q)
{
$a=rand(0,9);
if ($a>4 and $this->rights)
    {
    $d="INSERT INTO online (UID) VALUES
    ('".$this->rights['UID']."')
    ON DUPLICATE KEY UPDATE onlinesince=CURRENT_TIMESTAMP";
    mysql_query($d,$this->link);
    }

if(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/?$~',$q,$a)) $this->PAGE->mainpage();
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/c/([A-z0-9_\-]+)$~',$q,$a) and $this->rights)
    {
    /*
    var_dump($this->rights);
    var_dump($q);
    var_dump($a);
    */
    $this->PAGE->show_channel($a[1]);
    }

elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/about/?$~',$q)) $this->PAGE->about();

//Администрация
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/admin_users/?$~',$q)
    and ($this->rights['admin_users']==1 or $this->rights['moder_users']==1) )

    $this->PAGE->admin_users();

elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/admin_channels/?$~',$q)
    and ($this->rights['admin_channels'] or $this->rights['moder_channels']))
    $this->PAGE->admin_channels();

//Действия с текущим пользователем
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/log/?$~',$q) and $this->rights) $this->PAGE->iplog();
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/exit/?$~',$q)) {session_destroy();header("Location: ".$this->ini['basedir']."/");}
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/change_pwd/?$~',$q)) $this->PAGE->change_pwd();

//Действия с базой данных
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/panic/?$~',$q)) $this->PAGE->panic();
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/install/?$~',$q)) $this->PAGE->install();


//ajax обработчики
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/handlers/msg_handler$~',$q)) $this->msg_handler();
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/handlers/time_handler$~',$q)) $this->time_handler();
elseif(preg_match('~(*UTF8)^'.$this->ini['basedir'].'/handlers/validator$~',$q)) $this->validator();
else header('Location : '.$this->ini['basedir'].'/');
}

public function __destruct()
    {
    mysql_close($this->link);
    }
}
?>
