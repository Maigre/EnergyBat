<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img src="<?=base_url()?>/images/icons/loader.gif" width="32px" height="auto" />&nbsp;&nbsp; Renouvellement du cache...

<?=form_open(base_url()."index.php/consult/",array('id' => 'reload'))?>
<?=form_close()?>

<script type="text/javascript">
	document.getElementById('reload').submit();
</script>