<script language="php">
class CHANNEL
{
private $channel;
private $lnk;
private $rights;
///////////////

public function filter($a,$ajax=false)
{
//iconv("UTF-8", "WINDOWS-1251", $param);
/*
echo 'filter='.$ajax;
*/

if ($ajax) $a=iconv('UTF-8','WINDOWS-1251',urldecode($a));

$a=trim($a);

$a=preg_replace('~<script.*>.*</script>~im',NULL,$a);
$a=preg_replace('~<iframe.*>.*</iframe>~im',NULL,$a);
$a=preg_replace('~<FRAME\s[^>]*>~im',NULL,$a);
$a=preg_replace('~</?frameset[^>]*>~im',NULL,$a);
$a=preg_replace('~</?NOFRAMES>~im',NULL,$a);
$a=preg_replace('~<([A-z0-9]+)(\s.+)>(.+)</[A-z0-9]>~i','<\\1>\\3</\\1>',$a);
$a=preg_replace("~\s{2,}~im","\n",$a);


$a=mysql_real_escape_string($a);	
return $a;
}

public function __construct($URI,$user,$link)
	{
	if (mysql_ping($link) and get_class($user)=='USER' and preg_match('~^[A-z0-9_\-]+$~',$URI))
		{
		$this->lnk=$link;
		$this->rights=$user->get_user();
//		var_dump($this->rights);
		$qqq='SELECT * FROM channels,c_u WHERE channel_name="'.$this->filter($URI).'" && 
		(
		(c_u_UID="'.$this->rights['UID'].'" and c_u_channel=channel_id and channel_admin_UID!="'.$this->rights['UID'].'")
		||
		(channel_admin_UID="'.$this->rights['UID'].'")
		||
		(
		"'.$rights['admin_channels'].'"=1
		)
		) LIMIT 1';
//		echo $qqq;
		$res=mysql_query($qqq,$this->lnk);
		if(mysql_num_rows($res)==1)
			{
			$this->channel=mysql_fetch_assoc($res);
//			var_dump($this->channel);
			return $this;			
			}
			else
			{
			return false;
			}
		
		}
		else
		{
		return false;
		}
	
	}
	
public 	function show()
	{
	?>
	<script language="javascript">
	setInterval("check_new_comments('<? echo $this->channel['channel_name'];?>','<? echo session_id();?>')",300);
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
	?>
	<form name="post_mesg" action="" method="post">
	<input name="s" value="<? echo session_id(); ?>" type="hidden">
	<textarea name="new_mesg" cols="60" rows="2"  class="msg"></textarea>
	<noscript><p><input name="post_mesg_submit" type="submit"></p></noscript>
	</form>
	<?	
	}

public function post($a,$ajax=false)
	{	
//	echo 'ajax='.$ajax;
	if ($a['s']==session_id())
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
	if (!$a)
	$res=mysql_query('SELECT * FROM mesg RIGHT JOIN users ON (UID=mesg_UID) WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'" ORDER BY mesg_DTS ASC');
	elseif (is_numeric($a))
	$res=mysql_query('SELECT * FROM mesg RIGHT JOIN users ON (UID=mesg_UID) WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'" ORDER BY mesg_DTS DESC LIMIT '.$this->filter($a));
	else return false;
	
	$b=mysql_num_rows($res);
	for($i=0;$i<$b;$i++)
		{
		echo '<div class="msg"';
		if ($a>0) echo ' id="new_comment" ';
		echo '>';
		echo '<p><strong>'.mysql_result($res,$i,'U_login').'</strong> '.mysql_result($res,$i,'mesg_DTS');
		echo ' <a href="https://www.nic.ru/whois/?query='.mysql_result($res,$i,'mesg_IP').'">'.mysql_result($res,$i,'mesg_IP').'</a>';
		echo '</p>';
//		if ($a)
		echo '<p>'.mysql_result($res,$i,'mesg_TXT').'</p>';
//		else echo '<p>'.iconv('UTF-8','WINDOWS-1251',mysql_result($res,$i,'mesg_TXT')).'</p>';
		
		echo '</div>';
		}
	echo '<span id="num_mesg" style="display:none">'.$b.'</span>';
	}


public function list_users()
	{
//	var_dump($this->channel);
//	var_dump($this->rights);
	
	if (($this->channel['channel_admin_UID']==$this->rights['UID'] and $this->rights['moder_channels']==1) or $this->rights['admin_channels']==1)
		{
		
		if ($_POST['rs']==session_id())
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
			<input name="rs" type="hidden" value="<? echo session_id();?>" />
			<input name="removeuser" type="hidden" value="<? echo mysql_result($res,$i,'UID');?>" />
			<input name="" type="submit" value="<? echo mysql_result($res,$i,'U_login');?>" />			
			</form>
			</li>
			<?
			}
		echo '</ol>';			
		echo '<h3>Добавить подписчика канала</h3>';
		?>
		<form action="" method="post">
		<input name="rs" type="hidden" value="<? echo session_id();?>" />
		<input name="adduser2channel" type="text"/>
		<input name="" type="submit" value="Добавить" />			
		</form>
		<?
		}

	}

public function count_mesg()
	{
	$res=mysql_query('SELECT COUNT(mesg_ID) FROM mesg WHERE mesg_channel="'.$this->filter($this->channel['channel_id']).'"');
	return mysql_result($res,0);
	}
}
</script>