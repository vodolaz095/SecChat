<?php
class ADMIN
{
private $USER;
private $rights;
private $link;
private $ini;

private function filter($a)
    {
    return CONTROLLER::v_filter($a);
    }

private function filter_txt($a)
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
        showerror('Невозможно создать экземпляр класса администратора');
        }
    }

public function admin_channels()
  {
    if ($this->rights['admin_channels']==1)
    {

    $qqq='SELECT *,COUNT(c_u_id) AS num_users
    FROM channels
    LEFT JOIN c_u ON (c_u_channel=channel_id)
    LEFT JOIN users ON (UID=channel_admin_UID)
    GROUP BY channel_id ORDER BY channel_name ASC';

    if(CONTROLLER::validate_request($_POST['s']))
        {

        if($_POST['create_new_channel'])
            {
            $q2qq='INSERT INTO channels(channel_name,channel_admin_UID) VALUES ("'.$this->filter_txt($_POST['create_new_channel']).'","'.$this->rights['UID'].'")';

            mysql_query($q2qq,$this->link);
            }

            if($_POST['edit'])
            {
            $edit_q='UPDATE channels SET channel_mesg="'.$this->filter_txt($_POST['channel_mesg']).'" WHERE channel_id="'.$this->filter($_POST['edit_channel_id']).'"';
            mysql_query($edit_q,$this->link);
            }

        if($_POST['delete'])
            {
            $del_q='DELETE FROM channels WHERE channel_id="'.$this->filter($_POST['delete_channel_id']).'"';
            mysql_query($del_q,$this->link);
            }


        }

    }
    elseif ($this->rights['moder_channels']==1)
    {
    $qqq='SELECT *,COUNT(c_u_id) AS num_users
    FROM channels
    LEFT JOIN c_u ON (c_u_channel=channel_id)
    LEFT JOIN users ON (UID=channel_admin_UID)
    WHERE channel_admin_UID="'.$this->rights['UID'].'"
    GROUP BY channel_id ORDER BY channel_name ASC';

    if(CONTROLLER::validate_request($_POST['s']))
        {
        if($_POST['create_new_channel'])
            {
            $qq4q='INSERT INTO channels(channel_name,channel_admin_UID) VALUES ("'.$this->filter_txt($_POST['create_new_channel']).'","'.$this->rights['UID'].'")';

            mysql_query($qq4q,$this->link);
            }

            if($_POST['edit'])
            {
            mysql_query('UPDATE channels SET channel_mesg="'.$this->filter_txt($_POST['channel_mesg']).'" WHERE channel_id="'.$this->filter($_POST['edit_channel_id']).'" && channel_admin_UID="'.$this->rights['UID'].'" ',$this->link);
            }

        if($_POST['delete'])
            {
            mysql_query('DELETE FROM channels WHERE channel_id="'.$this->filter($_POST['delete_channel_id']).'" && channel_admin_UID="'.$this->rights['UID'].'" ',$this->link);
            }



        }



    }
    else $error=true;

   if($error)
   {
   echo '<p>У вас недостаточно прав для редактирования каналов!</p>';
   }
    else
    {
    $res=mysql_query($qqq,$this->link);
    $b=mysql_num_rows($res);
    if ($b)
    {
    ?>
    <table border="1" cellpadding="3" cellspacing="0" width="90%">
    <tr>
    <td>Название</td>
    <td>Описание</td>
    <td>Удалить канал</td>
    <td>Администратор</td>
    <td>Количество пользователей</td>
    </tr>
    <?php
    for($i=0;$i<$b;$i++)
        {
    echo '<tr>';
    echo '<td><a href="'.$this->ini['basedir'].'/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></td>';
    ?>
    <td>
    <form action="" method="post">
    <input name="s" type="hidden" value="<?php echo CONTROLLER::create_s(); ?>" />
    <input name="edit_channel_id" type="hidden" value="<?php echo mysql_result($res,$i,'channel_id'); ?>" />
    <input name="channel_mesg" type="text" value="<?php echo mysql_result($res,$i,'channel_mesg'); ?>" />
    <input name="edit" value="OK" type="submit" />
    </form>
    </td>
    <td>
    <form action="" method="post">
    <input name="s" type="hidden" value="<?php echo CONTROLLER::create_s(); ?>" />
    <input name="delete_channel_id" type="hidden" value="<?php echo mysql_result($res,$i,'channel_id'); ?>" />
    <input name="delete" value="Х" type="submit" />
    </form>
    </td>
    <?php
    echo '<td>'.mysql_result($res,$i,'U_login').'</td>';
    echo '<td align="center">'.mysql_result($res,$i,'num_users').'</td>';
    echo '</tr>';
        }
    echo '</table>';
    }
    ?>
    <form action="" method="post" name="create_new_channel_form">
    <input name="s" type="hidden" value="<?php echo CONTROLLER::create_s(); ?>">
    <input name="create_new_channel" type="text">
    <input name="create_new_channel_submit" type="submit" value="Создать новый канал">
    </form>
    <?php
    }
  }
private function draw_option($res,$i,$str)
{
    echo '<td>';
    if (mysql_result($res,$i,$str)==1)
    {
        echo '<form action="" method="post">';
        echo '<input name="s" value="'.CONTROLLER::create_s().'" type="hidden">';
        echo '<input name="unmake_'.$str.'" value="'.mysql_result($res,$i,'UID').'"  type="hidden">';
        echo '<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_green"></p>';
        echo '</form>';
    }
    else
    {
        echo '<form action="" method="post">';
        echo '<input name="s" value="'.CONTROLLER::create_s().'" type="hidden">';
        echo '<input name="make_'.$str.'" value="'.mysql_result($res,$i,'UID').'"  type="hidden">';
        echo '<p><input name="" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button_red"></p>';
        echo '</form>';
    }
    echo '</td>';
 }
/*
 *
 *
 *
 *
 * */
public function admin_users()
    {
    if ($this->rights['admin_users']==1) $s=2;
    elseif ($this->rights['moder_users']==1)  $s=1;
    else  $s=0;

    if ($s>0)
    {
    if($s>0)
    {
    if (CONTROLLER::validate_request($_POST['s']))
        {
        if ($_POST['unmake_admin_users'] and $this->rights['admin_users']==1)
            {
            $qqq='UPDATE users SET admin_users=0 WHERE UID="'.$this->filter($_POST['unmake_admin_users']).'"';
            mysql_query($qqq,$this->link);
            }

        if ($_POST['make_admin_users'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET admin_users=1 WHERE UID="'.$this->filter($_POST['make_admin_users']).'"',$this->link);

        if ($_POST['unmake_moder_users'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET moder_users=0 WHERE UID="'.$this->filter($_POST['unmake_moder_users']).'"',$this->link);

        if ($_POST['make_moder_users'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET moder_users=1 WHERE UID="'.$this->filter($_POST['make_moder_users']).'"',$this->link);

        if ($_POST['make_admin_channels'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET admin_channels=1 WHERE UID="'.$this->filter($_POST['make_admin_channels']).'"',$this->link);

        if ($_POST['unmake_admin_channels'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET admin_channels=0 WHERE UID="'.$this->filter($_POST['unmake_admin_channels']).'"',$this->link);

        if ($_POST['make_moder_channels'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET moder_channels=1 WHERE UID="'.$this->filter($_POST['make_moder_channels']).'"',$this->link);

        if ($_POST['unmake_moder_channels'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET moder_channels=0 WHERE UID="'.$this->filter($_POST['unmake_moder_channels']).'"',$this->link);

            if ($_POST['make_active'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET U_active=1 WHERE UID="'.$this->filter($_POST['make_active']).'"',$this->link);

        if ($_POST['unmake_active'] and $this->rights['admin_users']==1)
            mysql_query('UPDATE users SET U_active=0 WHERE UID="'.$this->filter($_POST['unmake_active']).'"',$this->link);

        if ($_POST['create_new_user'])
            {
            $pwd=strtoupper(substr((md5(date('c'))),0,8));
            echo '<p>Пользователь создан! Логин <strong>'.$_POST['create_new_user'].'</strong>. Пароль <strong>'.$pwd.'</strong></p>';
            $qqq='INSERT INTO users(U_login,U_pwd) VALUES ("'.$this->filter_txt($_POST['create_new_user']).'","'.md5($pwd).'")';
//          echo $qqq;
            mysql_query($qqq,$this->link);
            }
            echo mysql_error($this->link);
        }

    if (CONTROLLER::validate_request($_POST['s']) and $_POST['new_pwd_val'])
    {
    if ($s==2)
    $qqq='UPDATE users SET U_pwd="'.md5($_POST['new_pwd_val']).'" WHERE UID="'.$this->filter($_POST['new_pwd']).'"';
    else
    $qqq='UPDATE users SET U_pwd="'.md5($_POST['new_pwd_val']).'" WHERE UID="'.$this->filter($_POST['new_pwd']).'" && U_host="'.$this->rights['UID'].'"';

//    echo $qqq;
    mysql_query($qqq,$this->link);
    }
////////////////////////////
//
    if ($s==2) $res=mysql_query('SELECT *, U_active AS active FROM users WHERE (UID!="'.$this->rights['UID'].'") ORDER BY U_login ASC',$this->link);
    elseif ($s==1) $res=mysql_query('SELECT *, U_active AS active FROM users WHERE  U_host="'.$this->rights['UID'].'" ORDER BY U_login ASC',$this->link);

    $d=mysql_num_rows($res);
    if($d)
        {

echo '      <table border="1" cellpadding="3" cellspacing="0" align="center">';
echo '      <tr>';
echo '      <td>Имя</td>';
echo '      <td>Пароль</td>';
echo '      <td>Активен</td>';


        if($s==2)
        {
        echo '      <td>Адм. пользователей</td>';
        echo '      <td>Модератор пользователей</td>';
        echo '      <td>Адм. каналов</td>';
        echo '      <td>Модератор каналов</td>';
        }
             echo '     </tr>';

        for($i=0;$i<$d;$i++)
            {

echo '      <tr align="center" valign="middle">';
echo '      <td><p>'.mysql_result($res,$i,'U_login').'</p></td>';
echo '      <td>';
?>
<form action="" method="post" name="new_pwdf">
<input name="s" value="<?php echo CONTROLLER::create_s();?>" type="hidden" id="new_pwd_s">
<input name="new_pwd" value="<?php echo mysql_result($res,$i,'UID');?>"  type="hidden">
<p><input name="new_pwd_val" type="password"><input name="" type="submit" value="Задать"></p>
</form>

<?php
echo '</td>';



        if($s==2)
        {
        $this->draw_option($res,$i,'active');
        $this->draw_option($res,$i,'admin_users');
        $this->draw_option($res,$i,'moder_users');
        $this->draw_option($res,$i,'admin_channels');
        $this->draw_option($res,$i,'moder_channels');
        }
             echo '     </tr>';


            }
        echo '      </table>';
        }

    }

    ?>
    <h3>Создать нового пользователя</h3>
    <form action="" method="post">
    <input name="s" type="hidden" value="<?php echo CONTROLLER::create_s();?>">
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
    <?php
    }
    else
    {
    echo '<p>У вас недстаточно прав для редактирования пользователей!</p>';
    }
}
/*
 *
 *
 *
 * */
public function panic()
 {
    if ($this->rights['admin_channels']==1 and $this->rights['admin_users']==1)
    {
        if(CONTROLLER::validate_request($_POST['s']))
        {
        if($_POST['channels']) {echo '<p>Каналы очищены!</p>';mysql_query('TRUNCATE channels',$this->link);}
        if($_POST['c_u']) {echo '<p>Членство в каналах сброшено!</p>';mysql_query('TRUNCATE c_u',$this->link);}
        if($_POST['mesg']) {echo '<p>Сообщения удалены!</p>';mysql_query('TRUNCATE mesg',$this->link);}
        if($_POST['users']) {echo '<p>Пользователи удалены!</p>';mysql_query('TRUNCATE users',$this->link);}
        if($_POST['user_iplog']) {echo '<p>Лог доступа очищен!</p>';mysql_query('TRUNCATE user_iplog',$this->link);}
        }
        ?>
        <p>Вы собираетесь очистить базу данных, возможно оно того стоит. Выберите, что удалить.</p>
        <form action="" method="post"><input name="s" type="hidden" value="<?php echo CONTROLLER::create_s();?>">
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
        <?php

    }
 }

}
?>
