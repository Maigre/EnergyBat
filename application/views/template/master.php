<html>
<head>
	<?=$header;?>
	<SCRIPT language="JavaScript">
		var damn = '';
		function alertOnLoad() {if (damn != '') alert(damn);}
	</SCRIPT>
</head>
<body>
	<?=form_open('',array('id'=>'form_goto'))?>
	<?=form_close()?>
	
	<div class="banner"><div id="banner_image"></div></div>
	
	<div id="top"><?=$top;?></div>
	<div id="master">
		<div id="left"><?=$left;?></div>
		<?php if($error): ?>
			<div id="error"><?=$error;?></div>
		<?php endif; ?>
		<?php if($success): ?>
			<div id="success"><?=$success;?></div>
		<?php endif; ?>
		<?php if($info): ?>
			<div id="info"><?=$info;?></div>
		<?php endif; ?>
		<div id="content"><?=$content;?></div>
		<div id="right"><?=$right;?></div>
		<div id="bottom"><?=$bottom;?></div>
	
	<SCRIPT language="JavaScript">alertOnLoad();</SCRIPT>
</body>
</html>
