<h1>Bienvenue sur le systeme de suivi energetique.</h1>



<?=form_open("welcome/login")?>
<table  class="fency">
	<THEAD>
		<TR><TH colspan="2">IDENTIFICATION</TH></TR>
	</THEAD>
	<TR>
		<TD><label for="login">Utilisateur</label></TD>
		<TD><?=form_input('login')?></TD>
	</TR>
	<TR>
		<TD><label for="login">Mot de Passe</label></TD>
		<TD><?=form_password('password')?></TD>
	</TR>
	<TR>
		<TD></TD><TD><?=form_submit('submit', 'Login')?></TD>
	</TR>
</table>
<?=form_close()?>
