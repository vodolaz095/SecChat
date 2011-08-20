<script language="php">

	if ($this->rights['admin_channels']==1) 
	{

	$qqq='SELECT *,COUNT(c_u_id) AS num_users FROM channels 
	LEFT JOIN c_u ON (c_u_channel=channel_id) 
	LEFT JOIN users ON (UID=channel_admin_UID) 
	GROUP BY channel_id ORDER BY channel_name ASC';

	if($_POST['s']==session_id())
		{

		if($_POST['create_new_channel']) 
			{
			$q2qq='INSERT INTO channels(channel_name,channel_admin_UID) VALUES ("'.$this->filter_txt($_POST['create_new_channel']).'","'.$this->rights['UID'].'")';
			
			mysql_query($q2qq,$this->lnk);
			}
			
			if($_POST['edit']) 
			{
			mysql_query('UPDATE channels SET channel_mesg="'.$this->filter_txt($_POST['channel_mesg']).'" WHERE channel_id="'.$this->filter($_POST['edit_channel_id']).'"',$this->lnk);
			}
		
		if($_POST['delete']) 
			{
			mysql_query('DELETE FROM channels WHERE channel_id="'.$this->filter($_POST['delete_channel_id']).'"',$this->lnk);
			}

		
		}

	}
	elseif ($this->rights['moder_channels']==1)
	{
	$qqq='SELECT *,COUNT(c_u_id) AS num_users FROM channels 
	LEFT JOIN c_u ON (c_u_channel=channel_id) 
	LEFT JOIN users ON (UID=channel_admin_UID) 
	WHERE channel_admin_UID="'.$this->rights['UID'].'"
	GROUP BY channel_id ORDER BY channel_name ASC';

	if($_POST['s']==session_id())
		{
		if($_POST['create_new_channel']) 
			{
			$qq4q='INSERT INTO channels(channel_name,channel_admin_UID) VALUES ("'.$this->filter_txt($_POST['create_new_channel']).'","'.$this->rights['UID'].'")';
			
			mysql_query($qq4q,$this->lnk);
			}
			
			if($_POST['edit']) 
			{
			mysql_query('UPDATE channels SET channel_mesg="'.$this->filter_txt($_POST['channel_mesg']).'" WHERE channel_id="'.$this->filter($_POST['edit_channel_id']).'" && channel_admin_UID="'.$this->rights['UID'].'" ',$this->lnk);
			}

		if($_POST['delete']) 
			{
			mysql_query('DELETE FROM channels WHERE channel_id="'.$this->filter($_POST['delete_channel_id']).'" && channel_admin_UID="'.$this->rights['UID'].'" ',$this->lnk);
			}



		}



	}
	else $error=true;

	$this->makeheader('Редактировать каналы','');
	
	if($error)
	{
	?>
	<p>У вас недостаточно прав для редактирования каналов!</p>
	<?
	}
	else
	{
	$res=mysql_query($qqq,$this->lnk);
	$b=mysql_num_rows($res);
	if ($b)
	{
//var_dump($this->rights);
//var_dump($_POST);
	?>
	<table border="1" cellpadding="3" cellspacing="0" width="90%">
	<tr>
	<td>Название</td>
	<td>Описание</td>
	<td>Удалить канал</td>
	<td>Администратор</td>
	<td>Количество пользователей</td>
	</tr>
	<?
	for($i=0;$i<$b;$i++)
		{
	echo '<tr>';
	echo '<td><a href="/c/'.mysql_result($res,$i,'channel_name').'">'.mysql_result($res,$i,'channel_name').'</a></td>';
	?>
	<td>
	<form action="/admin_channels" method="post">
	<input name="s" type="hidden" value="<? echo session_id(); ?>" />
	<input name="edit_channel_id" type="hidden" value="<? echo mysql_result($res,$i,'channel_id'); ?>" />
	<input name="channel_mesg" type="text" value="<? echo mysql_result($res,$i,'channel_mesg'); ?>" />
	<input name="edit" value="OK" type="submit" />
	</form>	
	</td>
	<td>
	<form action="/admin_channels" method="post">
	<input name="s" type="hidden" value="<? echo session_id(); ?>" />
	<input name="delete_channel_id" type="hidden" value="<? echo mysql_result($res,$i,'channel_id'); ?>" />
	<input name="delete" value="Х" type="submit" />
	</form>
	</td>
	<?
	echo '<td>'.mysql_result($res,$i,'U_login').'</td>';
	echo '<td align="center">'.mysql_result($res,$i,'num_users').'</td>';
	echo '</tr>';
		}
	echo '</table>';
	}
	?>
	<form action="/admin_channels" method="post" name="create_new_channel_form">
	<input name="s" type="hidden" value="<? echo session_id(); ?>">
	<input name="create_new_channel" type="text">
	<input name="create_new_channel_submit" type="submit" value="Создать новый канал">	
	</form>

	<?
	}
	$this->makebottom();


</script>