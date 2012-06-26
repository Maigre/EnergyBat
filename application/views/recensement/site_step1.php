<?=validation_errors()?>

<div class="label"><h1>NOUVEAU SITE</h1></div>
<table class="fency">
	<?=form_open(uri_string());?>
	<THEAD class="vertical">
		<TR>		
			<TH>NOM</TH><TD><?=$Site['Nom']?></TD>
			<TH>MINISTERE</TH><TD><?=$Site['idMinistere']?></TD>
		</TR>
		<TR>
			<TH>ADRESSE</TH><TD><?=$Site['Adresse1']?></TD>
			<TH>VILLE</TH><TD><?=$Site['idVille']?></TD>
		</TR>
		<TR>
			
			<TH>CONTACT RESP.</TH><TD><?=$Site['ContactRespNom']?></TD>
			<TH>RESP. TEL</TH><TD><?=$Site['ContactRespTel']?></TD>
		</TR>
		<TR>
			<TH>CONTACT TECH.</TH><TD><?=$Site['ContactTechNom']?></TD>
			<TH>TECH. TEL</TH><TD><?=$Site['ContactTechTel']?></TD>
		</TR>
		<TR>
			<TH>EQUIPE VISITE</TH><TD><?=$Site['idEquipe']?> - visite prévue le <?=$Site['DatePrevue']?></TD>
			<TH>
				<div align="center"><?=$Site['SubmitBTN']?></div>
			</TH>
		</TR>
	</THEAD>
	<?=form_close();?>
</table>