<?php
/*
 *шаблон для вывода 1 сообщения в  чате
 */

function show_message($res,$i,$reltime)
{
echo "\n\n\n".'<div class="msg"';
if ($a>0) echo ' id="new_comment" ';
echo '>';
echo '<p><strong ';
if (time()-mysql_result($res,$i,'online_DTS')<5) echo ' style="color:#003300;" title="Пользователь на сайте"';
else echo ' style="color:#990000;" title="Пользователь отключился"';
echo '>'.mysql_result($res,$i,'U_login');
echo '</strong> ';
echo $reltime;


echo '  IP: <a href="https://www.nic.ru/whois/?query='.mysql_result($res,$i,'mesg_IP').'">';
echo mysql_result($res,$i,'mesg_IP').'</a>';
echo '</p>';
echo '<p>'.mysql_result($res,$i,'mesg_TXT').'</p>';
echo '</div>';
}

?>
