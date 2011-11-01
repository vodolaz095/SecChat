<?php
class USER
{
private $lnk;
private $user;
private $error;
private $ini;
////////////////////
public function get_user()
    {
    /*
     * Возвращает ассоциированный массив со всеми правами залогиненного
     * пользователя, или же false, если пользователь незалогинен*
     */
    if ($this->user)
        {
        $ans=$this->user;
        $ans['U_pwd']='***';
        }
        else $ans=false;

    return $ans;
    }

public function get_error()
{
    /*
     * Возвращает ошибку авторизации
     */
    return $this->error;
}
////////////////////////////////////
private function filter_txt($a)
    {
    $a=CONTROLLER::v_filter($a,'word');
    return $a;
    }
////////////////////////////////////

public function test()
{
echo '<hr>this';
echo '<pre>';
var_dump($this);
echo '</pre><pre>';
echo '<hr>POST';
var_dump($_POST);
echo '</pre><pre>';
echo '<hr>SESSION';
var_dump($_SESSION);
echo '</pre><pre>';
echo '<hr>cookie ';
var_dump ($_COOKIE);
echo '</pre>';
}

public function __construct($ini,$link)
{
//*****
if (mysql_ping($link) and session_id())
{
$this->lnk=$link;
$this->ini=$ini;

    if (!isset($_SESSION['UA'])) $_SESSION['UA']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);

    if($_POST['UL'] and $_POST['UP'] and CONTROLLER::validate_request($_POST['ssu']))
        {
            $_SESSION['U_login']=$_POST['UL'];
            $_SESSION['U_pwd']=md5(session_id().md5($_POST['UP']));
            $log=true;
        }


    if (isset($_SESSION['U_login']) and isset($_SESSION['U_pwd']))
    {

    if (md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])==$_SESSION['UA'])
        {
        $qqq='SELECT * FROM  users  WHERE U_login="'.$this->filter_txt($_SESSION['U_login']).'" && MD5(CONCAT("'.session_id().'",U_pwd))="'.$this->filter_txt($_SESSION['U_pwd']).'" && U_active=1';
        //echo $qqq;
        $res=mysql_query($qqq,$this->lnk);
        echo mysql_error($this->lnk);
        //var_dump($res);
        if (mysql_num_rows($res)==1)
            {
                $this->user=mysql_fetch_assoc($res);
                $this->user['U_pwd']='***';
                if ($log  and $ini['log_users']==1) mysql_query('INSERT INTO user_iplog(login,IP,status,useragent) VALUES ("'.$this->filter_txt($_SESSION['U_login']).'","'.$_SERVER['REMOTE_ADDR'].'","0","'.$_SERVER['HTTP_USER_AGENT'].'")',$this->lnk);
                unset($log);
                $this->error=false;
                return $this;
            }
            else
            {

                if ($log and $ini['log_users']==1) mysql_query('INSERT INTO user_iplog(login,IP,status,useragent) VALUES ("'.$this->filter_txt($_SESSION['U_login']).'","'.$_SERVER['REMOTE_ADDR'].'","1","'.$_SERVER['HTTP_USER_AGENT'].'")',$this->lnk);
                unset($log);

                $this->user=false;
                $this->error="Не правильнй логин или пароль Пользователя!";
                session_destroy();
                return false;
            }

        }
        else
        {
        $this->error="Кража сессии?";
        if ($ini['log_users']==1) mysql_query('INSERT INTO user_iplog(login,IP,status,useragent) VALUES ("'.$this->filter_txt($_SESSION['crewLOGIN']).'","'.$_SERVER['REMOTE_ADDR'].'","3","'.$_SERVER['HTTP_USER_AGENT'].'")',$this->lnk);
        $this->user=false;
        session_destroy();
        return(false);
        }

    }


}
else
{
$this->error="Сессия не инициализирована или же нет соединения с базой данных";
$this->user=false;
return false;
}

//*****
}

public function ip_log()
{
    /*
     * генерирует контент страницы с логом доступа пользователя
     *
     * */
if ($this->get_user())
    {

$res=mysql_query('SELECT * FROM user_iplog WHERE login="'.$this->user['U_login'].'" ORDER BY DTS DESC',$this->lnk);
$b=mysql_num_rows($res);
?>
<h1>Доступ к панели управления</h1>
<table border="1" cellpadding="1" cellspacing="0" align="center">
<tr align="center">
<td>Время</td>
<td>Логин</td>
<td>IP</td>
<td>Результат</td>
<td>Параметры системы клиента</td>
</tr>
<?php

for ($i=0;$i<$b;$i++)
    {
    echo '
    <tr align="center">
    <td>'.date('j M y',strtotime(mysql_result($res,$i,'DTS'))).'  /  '.date('H:i:s',strtotime(mysql_result($res,$i,'DTS'))).'</td>
    <td>'.mysql_result($res,$i,'login').'</td>
    <td><a href="https://www.nic.ru/whois/?query='.mysql_result($res,$i,'IP').'">'.mysql_result($res,$i,'IP').'</a></td>';

    if (mysql_result($res,$i,'status')==0) echo '<td  bgcolor="#33FF33">УСПЕШНО</td>';
    elseif (mysql_result($res,$i,'status')==1) echo '<td  bgcolor="#999999">Ошибка пароля</td>';
    elseif (mysql_result($res,$i,'status')==2) echo '<td  bgcolor="#FFFF00">Запрещённый IP адрес.</td>';
    elseif (mysql_result($res,$i,'status')==3) echo '<td  bgcolor="#FF0000">Кража сессии</td>';
    else echo '<td bgcolor="#F9595E">ВЗЛОМ?</td>';

    echo '<td><textarea name="" cols="40" rows="3" disabled="disabled">'.mysql_result($res,$i,'useragent').'</textarea></td>';
    echo '</tr>';

    }
echo '</table>';
if ($ini['log_users']!=1) echo '<h3>Протоколирование авторизаций выключено!</h3><p>Вы можете включить его, отредактировав файл secchat.ini</p>';
?>
<h3>Помощь</h3>
<p>На этой странице можно узнать о протоколе доступа к панели управления сайта. В таблице представлены введённые имена пользователей, IP адреса, параметры броузера и компьютера пользователя панели управления а также результат доступа к панели управления.</p>
<p>Описание возможных результатов доступа пользователей</p>
<ul>
<li>"Успешно" - пользователь ввёл правильное имя и пароль, при этом он работает с системой с разрешённого IP адреса.</li>
<li>"Ошибка пароля" - пользователь ввёл не правильную пару имени и пароля.</li>
<li>"Кража сессии" - злоумышленик пытался украсть идентификатор сессии авторизированного пользователя. В этом  случае рекомендуется проверить компьютер пострадавшего пользователя  на вирусы, и сообщить IP адрес злоумышленика в правоохранительные органы. К счастью, даже при попытке кражи идентификатора сессии пароль пользователя не будет извесен злоумышленику!</li>
</ul><?php
    }
}

public function make_login_form()
{
    ?>
    <div class="msg" align="center">
    <form action="" method="post">
    <input name="ssu" type="hidden" value="<?php echo CONTROLLER::create_s();?>" />
    <p><input name="UL" type="text" /></p>
    <p><input name="UP" type="password" /></p>
    <p><input type="submit" value="OK" />
    </form>
    </div>
    <?php
}

public function log_off()
{
session_destroy();
$this->user=false;
}


public function change_pwd()
{
if($this->user)
    {
if(CONTROLLER::validate_request($_POST['s']))
    {
    if($_POST['new_pwd_val']==$_POST['new_pwd_val1'])
        {
        $q='UPDATE users SET U_pwd="'.md5($this->filter_txt($_POST['new_pwd_val'])).'" WHERE UID="'.$this->rights['UID'].'"';
        mysql_query($q,$this->lnk);
        $_SESSION['U_pwd']=md5(session_id().md5($_POST['new_pwd_val']));
        ?>
        <p>Пароль успешно изменён!</p>
        <?php
        }
    else
        {
        ?>
        <p><strong>Ошибка!</strong> Пароли не совпадают!</p>
        <?php
        }

    }
?>
<h1>Сменить пароль</h1>
<form action="" method="post">
<input name="s" type="hidden" value="<?php echo CONTROLLER::create_s(); ?>">
<p>Введите новый пароль</p>
<p><input name="new_pwd_val" type="password"></p>
<p>Повторие новый пароль ещё 1 раз</p>
<p><input name="new_pwd_val1" type="password"></p>
<p><input name="" type="submit" value="Сменить пароль"></p>
</form>
<?php
    }
}
////////////////////////end class
}
?>
