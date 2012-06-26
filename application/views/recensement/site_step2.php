<?=validation_errors()?>
<?=form_open(uri_string());?>

<div class="label"><h1>SITE</h1></div>
<table>
	<TR>
		<TD>
			<TABLE class="fency">
				<THEAD class="vertical">
					<TR>
						<TH>MINISTERE</TH><TD><?=$Site['idMinistere']?></TD>
					</TR>
					<TR>
						<TH>NOM</TH><TD><?=$Site['Nom']?></TD>
					</TR>
					<TR>	
						<TH>ADRESSE</TH><TD><?=$Site['Adresse1']?></TD>
					</TR>
					<TR>
						<TH>COMPLEMENT</TH><TD><?=$Site['Adresse2']?></TD>
					</TR>
					<TR>
						<TH>VILLE</TH><TD><?=$Site['idVille']?></TD>
					</TR>
				</THEAD>
			</TABLE>
		</TD>
		<TD rowspan="1" style="vertical-align:top;">
			<TABLE class="fency">
				<THEAD class="vertical">
					<TR>		
						<TH>EQUIPE VISITE</TH><TD><?=$Site['idEquipe']?></TD>
					</TR>
					<?php if ($Site['DatePrevue'] != ''): ?>
					<TR>		
						<TH>DATE PREVUE</TH><TD><?=$Site['DatePrevue']?></TD>
					</TR>
					<?php endif; ?>
					<TR>		
						<TH>DATE VISITE</TH><TD><?=$Site['DateVisite']?></TD>
					</TR>
					<TR>
						<TH>TYPE DE SITE</TH><TD><?=$Site['idSiteType']?></TD>
					</TR>
					<TR>
						<TH>NBR BATIMENT</TH><TD><?=$Site['BatimentNbr']?></TD>
					</TR>
					<TR>
						<TH>PLAN</TH><TD><?=$Site['Plan']?></TD>
					</TR>
					<TR>
						<TH>PHOTO</TH><TD><?=$Site['Photo']?></TD>
					</TR>
				</THEAD>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD colspan="2">
			<TABLE class="fency">
				<THEAD class="vertical">
					<TR>
						<TH>RESPONSABLE</TH><TD><?=$Site['ContactRespNom']?></TD>
						<TH>TEL.</TH><TD><?=$Site['ContactRespTel']?></TD>
						<TH>FONCTION</TH><TD><?=$Site['ContactRespFonc']?></TD>
					</TR>
					<TR>
						<TH>CONTACT 1</TH><TD><?=$Site['ContactTechNom']?></TD>
						<TH>TEL.</TH><TD><?=$Site['ContactTechTel']?></TD>
						<TH>FONCTION</TH><TD><?=$Site['ContactTechFonc']?></TD>
					</TR>
					<TR>
						<TH>CONTACT 2</TH><TD><?=$Site['ContactSup2Nom']?></TD>
						<TH>TEL.</TH><TD><?=$Site['ContactSup2Tel']?></TD>
						<TH>FONCTION</TH><TD><?=$Site['ContactSup2Fonc']?></TD>
					</TR>
					<TR>
						<TH>CONTACT 3</TH><TD><?=$Site['ContactSup3Nom']?></TD>
						<TH>TEL.</TH><TD><?=$Site['ContactSup3Tel']?></TD>
						<TH>FONCTION</TH><TD><?=$Site['ContactSup3Fonc']?></TD>
					</TR>			
				</THEAD>
			</table>
		</TD>
	</TR>
	<TR>
		<TD colspan="2">
			<TABLE class="fency">
				<THEAD class="vertical">	
					<TR>
						<TH>OBSERVATION</TH><TD colspan="5"><?=$Site['Commentaire']?></TD>
						<TH><?=$Site['SubmitBTN']?><?=$Site['DeleteBTN']?></TH>
					</TR>
				</THEAD>
			</table>
		</TD>
	</TR>
</table>

<?=form_close();?>