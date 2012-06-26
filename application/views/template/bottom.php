<?php 
if ($logged):
$img = array('src'=>'images/icons/web/artmaster_logout_mini_icon.png','class'=>'icon');
echo anchor('welcome/logout',img($img)); 
else:
$img = array('src'=>'images/icons/web/artmaster_login_mini_icon.png','class'=>'icon');
echo anchor('welcome',img($img));
endif; 
?>

<br /><i>Powered By MGR - {elapsed_time} seconds</i>

