<h2>ADMINISTRATION UTILISATEURS</h2>
<hr />
<h3>Liste des utilisateurs</h3>
<table class="fency">
	<THEAD>
		<tr><th>Utilisateur</th><th>Connexion</th><th>Droits</th></tr>
	</THEAD>
	<?php foreach($user_list as $user): ?>
		<tr><td><?=$user->user_email?></td><td><?=$user->user_last_login?></td><td><?=$user->idUsersGroup?></td></tr>
	<?php endforeach; ?>
</table>

<hr />
<?=form_open("admin/user/new")?>
<table class="fency">
	<THEAD>
		<TH colspan="2">Nouvel Utilisateur</th>
	</THEAD>
	<TR>
		<TD><label for="login">Utilisateur</label></TD>
		<TD><?=form_input('login')?></TD>
	</TR>
	<TR>
		<TD><label for="login">Mot de Passe</label></TD>
		<TD><?=form_password('password','')?></TD>
	</TR>
	<TR>
		<TD><label for="login">Confirmation</label></TD>
		<TD><?=form_password('confirm','')?></TD>
	</TR>
	<TR>
		<TD></TD><TD><?=form_submit('submit', 'Enregistrer')?></TD>
	</TR>
</table>
<?=form_close()?>
