<?php
class CHANNEL
{
private $channel;
private $lnk;
private $rights;
private $valid;
///////////////

public function filter($a,$ajax=false)
{
if ($ajax) $a=urldecode($a);
return CONTROLLER::v_filter($a,'text');
}

private function plural($n, &$plurals)
{
  $plural =
      ($n % 10 == 1 && $n % 100 != 11 ? 0 :
          ($n % 10 >= 2 && $n % 10 <= 4 &&
          ($n % 100 < 10 or $n % 100 >= 20) ? 1 : 2));
  return $plurals[$plural];
}


private function relativeTime($dt, $precision = 2)
{
    /*
     * Время и дата в формате твиттера
     */
  $times = array(
    365*24*60*60    =>  array("год", "года", "лет"),
    30*24*60*60     =>  array("месяц", "месяца", "месяцев"),
        7*24*60*60      =>  array("неделя", "недели", "недель"),
        24*60*60        =>  array("день", "дня", "дней"),
        60*60           =>  array("час", "часа", "часов"),
        60              =>  array("минута", "минуты", "минут"),
        1               =>  array("секунда", "секунды", "секунд"),
  );

  $passed = time() - $dt;

  if($passed < 5)
  {
    $output='менее 5 секунд назад';
  }
  else
  {
    $output = array();
    $exit = 0;
        foreach($times as $period => $name)
        {

          if($exit >= $precision || ($exit > 0 && $period < 60)) break;
            $result = floor($passed / $period);

            if ($result > 0)
            {
                $output[] = $result . ' ' .$this->plural($result, $name);
                $passed -= $result * $period;
                $exit++;
            } else if ($exit > 0) $exit++;
        }
        $output = implode(' и ', $output).' назад';
    }
    return $output;
}

public function is_valid()
    {
        /* Проверка
         * Допущен ли пользователь до просмотра
         * этого канала - true/false
         */
    return $this->valid;
    }


////////////
public function __construct($URI,$USER,$link)
    {
    if (mysql_ping($link) and get_class($USER)=='USER')
        {
        $this->lnk=$link;
        $this->rights=$USER->get_user();
        $this->ini['ajax_delay']=300;

        if ($this->rights['admin_channels']==1)
        {
                $qqq='SELECT * FROM channels WHERE channel_name="'.$this->filter($URI).'"';
                echo $qqql;
                $res=mysql_query($qqq,$this->lnk);
        }

else
{
        $qqq='SELECT * FROM channels,c_u WHERE channel_name="'.$this->filter($URI).'" &&
        (
        (c_u_UID="'.$this->rights['UID'].'" and c_u_channel=channel_id and channel_admin_UID!="'.$this->rights['UID'].'")
        ||
        (channel_admin_UID="'.$this->rights['UID'].'")
        ) LIMIT 1';
        $res=mysql_query($qqq,$this->lnk);
}

        if(mysql_num_rows($res)==1)
            {
            $this->channel=mysql_fetch_assoc($res);
            $this->valid=true;
            return $this;
            }
            else
            {
            $this->valid=false;
//            return false;
            }

        }
        else
        {
//        return false;
        }
    }

public  function show()
    {
/*Показывает весь канал - рендеринг в статичный текст
 *
 *
 */
    ?>
    <script language="javascript">
    setInterval("check_new_comments('<?php echo $this->channel['channel_name'];?>','<?php echo CONTROLLER::create_s(); ?>')",<?php echo $this->ini['ajax_delay'];?>);
    </script>
    <?
    echo '<h3 id="URI">'.$this->channel['channel_name'].'</h3>';
    echo '<div id="channel_memo">'.$this->channel['channel_mesg'].'</div>';
    echo '<hr>';
    $this->post($_POST);
    echo '<div id="channel_mesg_placeholder">';
    $this->list_mesg();
    echo '</div>';
    $this->form();
    $this->list_users();
    }

private function form()
    {
/*
 *Показывает форму для ввода сообщения
 *
 */
    ?>
    <form name="post_mesg" action="" method="post">
    <input name="s" value="<?php echo CONTROLLER::create_s();?>" type="hidden">
    <textarea name="new_mesg" cols="60" rows="2"  class="msg"></textarea>
    <p>Нажмите Enter чтобы отправить сообщение</p>
    <noscript><p><input name="post_mesg_submit" type="submit"></p></noscript>
    </form>
    <?php
    }


public function post($a,$ajax=false)
    {
    /*
     * Создать одно сообщение в канале
     */

    if (CONTROLLER::validate_request($a['s']))
        {
        mysql_query('INSERT INTO mesg(mesg_UID,mesg_IP,mesg_channel,mesg_TXT) VALUES
        (
        "'.$this->rights['UID'].'",
        "'.$_SERVER['REMOTE_ADDR'].'",
        "'.$this->channel['channel_id'].'",
        "'.$this->filter($a['new_mesg'],$ajax).'")',$this->lnk);
        $s=mysql_error($this->lnk);
        if (!$s) return true;
        else return $s;
        }
        return false;
    }

public function list_mesg($a=false)
    {
    /*
     *Вывод сообщений - если $a - задано, то последние $a сообщений,
     *если нет,  то все сообщения этого канала
     *
     *
     */
    if (!$a)
    $res=mysql_query('SELECT *, UNIX_TIMESTAMP(mesg_DTS) AS DTS,UNIX_TIMESTAMP(onlinesince) AS online_DTS FROM mesg LEFT JOIN users ON (UID=mesg_UID) LEFT JOIN online ON (users.UID=online.UID) WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'" ORDER BY mesg_DTS ASC');
    elseif (is_numeric($a))
    $res=mysql_query('SELECT *, UNIX_TIMESTAMP(mesg_DTS) AS DTS, UNIX_TIMESTAMP(onlinesince) AS online_DTS  FROM mesg LEFT JOIN users ON (UID=mesg_UID) LEFT JOIN online ON (users.UID=online.UID) WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'" ORDER BY mesg_DTS DESC LIMIT '.$this->filter($a));
    else return false;

    $b=mysql_num_rows($res);
    for($i=0;$i<$b;$i++)
        {
         if (!$a) $reltime=$this->relativeTime(mysql_result($res,$i,'DTS'));
         show_message($res,$i,$reltime);
        }
    echo '<span id="num_mesg" style="display:none">'.$b.'</span>';
    }

public function list_users()
    {
/*
 *Перечисление подписчиков на канал
 *
 */
    if (($this->channel['channel_admin_UID']==$this->rights['UID'] and $this->rights['moder_channels']==1) or $this->rights['admin_channels']==1)
        {

        if (CONTROLLER::validate_request($_POST['rs']))
            {

            if($_POST['removeuser']) mysql_query('DELETE FROM c_u WHERE c_u_channel="'.$this->channel['channel_id'].'" && c_u_UID="'.$this->filter($_POST['removeuser']).'"',$this->lnk);

            if($_POST['adduser2channel'])
                {
                $UID=mysql_result(mysql_query('SELECT UID FROM users WHERE U_login="'.$this->filter($_POST['adduser2channel']).'"',$this->lnk),0);
                if ($UID)
                {
                mysql_query('INSERT INTO c_u(c_u_channel,c_u_UID) VALUES("'.$this->channel['channel_id'].'", '.$UID.')',$this->lnk);
                 echo '<p>Пользователь добавлен!</p>';
                }
                else
                {
                 echo '<p>Такого пользователя нет!</p>';
                }
                }
            }
        echo '<h3>Подписчики канала</h3>';
        $res=mysql_query('SELECT * FROM c_u LEFT JOIN users ON (c_u_UID=UID) WHERE c_u_channel="'.$this->channel['channel_id'].'" ORDER BY U_login ASC',$this->lnk);
        $b=mysql_num_rows($res);
        echo '<ol>';
        for($i=0;$i<$b;$i++)
            {
            ?>
            <li>
            <form action="" method="post">
            <input name="rs" type="hidden" value="<?php echo CONTROLLER::create_s();?>" />
            <input name="removeuser" type="hidden" value="<?php echo mysql_result($res,$i,'UID');?>" />
            <input name="" type="submit" value="<?php echo mysql_result($res,$i,'U_login');?>" />
            </form>
            </li>
            <?php
            }
        echo '</ol>';
        echo '<h3>Добавить подписчика канала</h3>';
        ?>
        <form action="" method="post">
        <input name="rs" type="hidden" value="<?php echo CONTROLLER::create_s();?>" />
        <input name="adduser2channel" type="text"/>
        <input name="" type="submit" value="Добавить" />
        </form>
        <?php
        }

    }

public function count_mesg()
    {
/*
 * Подсчёт количества сообщений в канале
 *
 */
    $res=mysql_query('SELECT COUNT(mesg_ID) FROM mesg WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'"');
    return mysql_result($res,0);
    }
}

?>
