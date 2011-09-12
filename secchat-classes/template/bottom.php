</div>
<div id="footer">
&copy; <a href="https://github.com/vodolaz095/secchat">SecChat</a>  - Остроумов Анатолий - 2011 год
</div>
<div id="security" align="center">

<?php
echo '<p>Время системы <strong id="server_time">'.date('H:m:s').'</strong>. Ваш IP адрес <a href="https://www.nic.ru/whois/?query='.$_SERVER['REMOTE_ADDR'].'">'.$_SERVER['REMOTE_ADDR'].'</a>';
echo '. Связь через порт '.$_SERVER['SERVER_PORT'].'.</p>';
?>
</div>
</body>
</html>
