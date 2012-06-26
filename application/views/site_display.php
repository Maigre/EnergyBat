<?=validation_errors()?>

<?=form_open(base_url().'index.php/consult/site/'.$Site['idSite'].'/printInfo');?>
<div style="float:left;margin-top:15px;margin-right:10px;">
	<input type="image" src="<?=base_url()?>images/icons/selection/printer.png" />
</div>
<?=form_close();?>

<?=form_open(uri_string());?>
<div class="label">
	<div style="float:right;margin-top:8px"><div class="statut"><?=$Statut?></div><?=$Site['DeleteBTN'].$Site['EditBTN'].$Site['ValidBTN'].$Site['UnValidBTN']?></div>
	<h1>SITE</h1>
</div>

<div id="SITEtable">
	<table class="fency">
		<THEAD class="vertical">
			<TR>		
				<TH>NOM</TH><TD style="width:240px;font-size:14px"><strong><?=$Site['Nom']?></strong></TD>
				<TH>MINISTERE</TH><TD colspan="3"><?=$Site['idMinistere']?></TD>
				<TH>RESPONSABLE</TH><TD><?=$Site['ContactRespNom']?> <?=($Site['ContactRespFonc'] != '')?'('.$Site['ContactRespFonc'].')':''?></TD>
				<TH>TEL.</TH><TD><?=$Site['ContactRespTel']?></TD>
			</TR>
			<TR>
				<TH rowspan="2">ADRESSE</TH><TD rowspan="2"><?=$Site['Adresse1']?><br /><?=$Site['Adresse2']?></TD>
				<TH>DATE VISITE</TH><TD><?=$Site['DateVisite']?></TD>
				<TH>EQUIPE</TH><TD><em><?=$Site['idEquipe']?></em></TD>
				<TH>CONTACT 1</TH><TD><?=$Site['ContactTechNom']?> <?=($Site['ContactTechFonc'] != '')?'('.$Site['ContactTechFonc'].')':''?></TD>
				<TH>TEL.</TH><TD><?=$Site['ContactTechTel']?></TD>
			</TR>
			<TR>
				<TH>TYPE</TH><TD><em><?=$Site['idSiteType']?></em></TD>
				<TH>BAT.</TH><TD><em><?=$Site['BatimentNbr']?></em></TD>
				<TH>CONTACT 2</TH><TD><?=$Site['ContactSup2Nom']?> <?=($Site['ContactSup2Fonc'] != '')?'('.$Site['ContactSup2Fonc'].')':''?></TD>
				<TH>TEL.</TH><TD><?=$Site['ContactSup2Tel']?></TD>
			</TR>
			<TR>
				<TH>VILLE</TH><TD><em><?=$Site['idVille']?></em></TD>
				<TH>PHOTO</TH><TD><?=$Site['Photo']?></TD>
				<TH>PLAN</TH><TD><?=$Site['Plan']?></TD>
				<TH>CONTACT 3</TH><TD><?=$Site['ContactSup3Nom']?> <?=($Site['ContactSup3Fonc'] != '')?'('.$Site['ContactSup3Fonc'].')':''?></TD>
				<TH>TEL.</TH><TD><?=$Site['ContactSup3Tel']?></TD>
			</TR>
			<TR>
				<TH>OBSERVATIONS</TH>
				<TD colspan="9"><div style="height:40px;overflow:auto;"><?=$Site['Commentaire']?></div></TD>
			</TR>
		</THEAD>
	</table>
</div>

<?=form_close();?>
<hr />
<div class="tabber" id="generalTABS">
<?php if ($Suivi): ?>
<div class="tabbertab">
	<h3>Suivi</h3>
	<div class="label">
		<!--<div style="float:right;margin-top:8px"><button onclick="switchview('AUtable');">Afficher/Cacher</button></div>-->
		<h1>SUIVI / AUDIT</h1>
	</div>
	<TABLE id="AUtable" class="fency">	
		<THEAD>
			<TR><TH>ELECTRICITE</TH></TR>
		</THEAD>
		<THEAD class="vertical">
		<TR>
			<TD>
				<div class="tabber" id="tabResidenceA">
					<div class="tabbertab">
					<h3>Conso. 13 mois (MWh)</h3>
						<TABLE>
							<TR><TD><?=$Site['Graph']['ConsoElec1'];?></TD></TR> <!--BARGRAPH avec BT /MT pointe /MT hors pointe-->
						</TABLE>
					</div>
				</div>
			</TD>
			<TD>
				<?=form_open(uri_string());?>
				<TABLE>
					<TR><TH>Superficie</TH>		<TD><?=$Site['Surface'];?> m²</TD>					<TH colspan="2">Facture 12 Mois</TH>	<TD><?=$Site['Ratio']['elec']['12m']['MFm']*12;?> MCFA</TD>
					<TR><TH><?php if ($Site['idSiteType']=='Ecole'): ?>Administratifs<?php else: ?>Occupants<?php endif; ?></TH>		<TD><?=$Site['Occupants'];?> pers</TD>					<TH colspan="2">Conso 12 Mois</TH> 	<TD><?=round($Site['Ratio']['elec']['12m']['KWh/J']*365/10)/100;?> MWh</TD>
					<TR><TH><?php if ($Site['idSiteType']=='Ecole'): ?>Elèves<?php endif; ?></TH>		<TD><?php if ($Site['idSiteType']=='Ecole'): ?><?=$Site['Occupants2'];?> pers<?php endif; ?></TD>					<TH colspan="2"></TH> 	<TD></TD>					
					<TR><TD></TD></TR>
					<TR><TD></TD>				<TH><?=$SelectMois;?></TH>										<TH><?=$MoisPrecedent;?></TH>										<TH><?=$MoisAnPrecedent;?></TH>										<TH>Moy. 1 an</TH></TR> 
					<TR><TH>Facture (kCFA)</TH>	<TD><?=$Site['Ratio']['elec']['30j']['MFm']*1000;?></TD> 		<TD><?=$Site['Ratio']['elec']['30+30j']['MFm']*1000;?></TD>			<TD><?=$Site['Ratio']['elec']['365+30j']['MFm']*1000;?></TD>		<TD><?=$Site['Ratio']['elec']['12m']['MFm']*1000;?></TD></TR>
					<TR><TH>Cout KWh (CFA)</TH>	<TD><?=$Site['Ratio']['elec']['30j']['CK'];?></TD>				<TD><?=$Site['Ratio']['elec']['30+30j']['CK'];?></TD>				<TD><?=$Site['Ratio']['elec']['365+30j']['CK'];?></TD>				<TD><?=$Site['Ratio']['elec']['12m']['CK'];?></TD></TR>
					<TR><TH>PA / PS (KW)</TH>	<TD><?=$Site['Ratio']['elec']['30j']['PAt'];?> 
														/ <?=$Site['Ratio']['elec']['30j']['PSt'];?></TD>		<TD><?=$Site['Ratio']['elec']['30+30j']['PAt'];?> 
																															/ <?=$Site['Ratio']['elec']['30+30j']['PSt'];?></TD>	<TD><?=$Site['Ratio']['elec']['365+30j']['PAt'];?> 
																																															/ <?=$Site['Ratio']['elec']['365+30j']['PSt'];?></TD>		<TD><?=$Site['Ratio']['elec']['12m']['PAt'];?> 
																																																															/ <?=$Site['Ratio']['elec']['12m']['PSt'];?></TD></TR>
					<TR><TH>KWh</TH>			<TD><?=$Site['Ratio']['elec']['30j']['KWh/J']*30;?></TD>		<TD><?=$Site['Ratio']['elec']['30+30j']['KWh/J']*30;?></TD>			<TD><?=$Site['Ratio']['elec']['365+30j']['KWh/J']*30;?></TD>		<TD><?=$Site['Ratio']['elec']['12m']['KWh/J']*30;?></TD></TR>
					<TR><TH>KWh /jour</TH>		<TD><?=$Site['Ratio']['elec']['30j']['KWh/J'];?></TD>			<TD><?=$Site['Ratio']['elec']['30+30j']['KWh/J'];?></TD>			<TD><?=$Site['Ratio']['elec']['365+30j']['KWh/J'];?></TD>			<TD><?=$Site['Ratio']['elec']['12m']['KWh/J'];?></TD></TR>
					<TR><TH>KWh /m²</TH>		<TD><?=$Site['Ratio']['elec']['30j']['KWh/m²'];?></TD>			<TD><?=$Site['Ratio']['elec']['30+30j']['KWh/m²'];?></TD>			<TD><?=$Site['Ratio']['elec']['365+30j']['KWh/m²'];?></TD>			<TD><?=$Site['Ratio']['elec']['12m']['KWh/m²'];?></TD></TR>
					<TR><TH>KWh /pers</TH>		<TD><?=$Site['Ratio']['elec']['30j']['KWh/hab'];?></TD>			<TD><?=$Site['Ratio']['elec']['30+30j']['KWh/hab'];?></TD>			<TD><?=$Site['Ratio']['elec']['365+30j']['KWh/hab'];?></TD>			<TD><?=$Site['Ratio']['elec']['12m']['KWh/hab'];?></TD></TR>
				</TABLE>
				<?=form_close();?>
			</TD>
		</TR>
		</THEAD>
		<THEAD>
			<TR><TH>EAU</TH></TR>
		</THEAD>
		<THEAD class="vertical">
		<TR>
			<TD>
				<div class="tabber" id="tabResidenceA">
					<div class="tabbertab">
					<h3>Conso. 13 mois (m3)</h3>
						<TABLE>
							<TR><TD><?=$Site['Graph']['ConsoEau1'];?></TD></TR> <!--BARGRAPH avec conso eau-->
						</TABLE>
					</div>
				</div>
			</TD>
			<TD>
				<TABLE>
					<TR><TH>Superficie</TH>		<TD><?=$Site['Surface'];?> m²</TD>					<TH colspan="2">Facture 12 Mois</TH>	<TD><?=$Site['Ratio']['eau']['12m']['MFm']*12;?> MCFA</TD>
					<TR><TH><?php if ($Site['idSiteType']=='Ecole'): ?>Administratifs<?php else: ?>Occupants<?php endif; ?></TH>		<TD><?=$Site['Occupants'];?> pers</TD>					<TH colspan="2">Conso 12 Mois</TH> 	<TD><?=$Site['Ratio']['eau']['12m']['m3']*12;?> m3</TD>
					<TR><TH><?php if ($Site['idSiteType']=='Ecole'): ?>Elèves<?php endif; ?></TH>		<TD><?php if ($Site['idSiteType']=='Ecole'): ?><?=$Site['Occupants2'];?> pers<?php endif; ?></TD>					<TH colspan="2"></TH> 	<TD></TD>
					<TR><TD></TD></TR>
					<TR><TD></TD>			<TH><?=$MoisCourant;?></TH>						<TH><?=$MoisPrecedent;?></TH>						<TH><?=$MoisAnPrecedent;?></TH>						<TH>Moy. 1 an</TH></TR> 
					<TR><TH>Facture (kCFA)</TH>	<TD><?=$Site['Ratio']['eau']['30j']['MFm']*1000;?></TD> 		<TD><?=$Site['Ratio']['eau']['30+30j']['MFm']*1000;?></TD>		<TD><?=$Site['Ratio']['eau']['365+30j']['MFm']*1000;?></TD>		<TD><?=$Site['Ratio']['eau']['12m']['MFm']*1000;?></TD></TR>
					<TR><TH>Cout m3 (CFA)</TH>	<TD><?=$Site['Ratio']['eau']['30j']['CM3'];?></TD>			<TD><?=$Site['Ratio']['eau']['30+30j']['CM3'];?></TD>			<TD><?=$Site['Ratio']['eau']['365+30j']['CM3'];?></TD>			<TD><?=$Site['Ratio']['eau']['12m']['CM3'];?></TD></TR>
					<TR><TH>m3</TH>			<TD><?=$Site['Ratio']['eau']['30j']['m3'];?></TD>			<TD><?=$Site['Ratio']['eau']['30+30j']['m3'];?></TD>			<TD><?=$Site['Ratio']['eau']['365+30j']['m3'];?></TD>			<TD><?=$Site['Ratio']['eau']['12m']['m3'];?></TD></TR>
					<TR><TH>L/pers/jour</TH>	<TD><?=$Site['Ratio']['eau']['30j']['L/hab/J'];?></TD>			<TD><?=$Site['Ratio']['eau']['30+30j']['L/hab/J'];?></TD>		<TD><?=$Site['Ratio']['eau']['365+30j']['L/hab/J'];?></TD>		<TD><?=$Site['Ratio']['eau']['12m']['L/hab/J'];?></TD></TR>
				</TABLE>
			</TD>
		</TR>
		</THEAD>
		<THEAD>
			<TR><TH>Données</TH></TR>
		</THEAD>
		<THEAD class="vertical">
			<TR>
				<TD>
					<TABLE>					
						<TR><TD></TD>
							<?php foreach($Site['Datas'] as $nam=>$val) echo '<TH>'.$nam.'</TH>'; ?>
						</TR>
						<?php 
							foreach(current($Site['Datas']) as $mois=>$val): 
								echo '<TR><TH>'.$mois.'</TH>';
								foreach($Site['Datas'] as $nam=>$datas) echo '<TD>'.$datas[$mois].'</TD>';
								echo '</TR>';
							endforeach;
						?>
					</TABLE>
				</TD>
			</TR>
		</THEAD>
	</TABLE>
</div>	
<?php endif; ?>
	
<?php if ($Diagnostic): ?>
<div class="tabbertab">
		<h3>Diagnostic
		</h3>
	
	<div class="label">
		<?=form_open(base_url().'index.php/consult/site/'.$Site['idSite'].'/printDiag');?>
		<div style="float:left;margin-top:8px;margin-right:10px;">
			<input type="image" src="<?=base_url()?>images/icons/selection/printer.png" />
		</div>
		<?=form_close();?>
		<!--<div style="float:right;margin-top:8px"><button onclick="switchview('DIAGtable');">Afficher/Cacher</button></div>-->
		<h1>DIAGNOSTIC</h1>
	</div>
	<TABLE id="DIAGtable">
		<TR>
			<TD style="vertical-align:top">
				<TABLE class="fency">	
					<THEAD>
						<TR><TH>Site Complet</TH></TR>
					</THEAD>
					<THEAD class="vertical">
					<TR>
						<TD>
							<TABLE>
								<?php foreach($Site['Values'] as $key=>$val): ?>
									<TR><TH><?=$key;?></TH><TD><?=$val;?></TD></TR>
								<?php endforeach;?>
							</TABLE>				
						</TD>
					</TR>
					</THEAD>
				</TABLE>
			</TD>
			<TD>
				<?php foreach($Batiments['Values'] as $name=>$diag): ?>
				<TABLE class="fency">	
					<THEAD>
						<TR><TH>Batiment : <?=$name;?></TH></TR>
					</THEAD>
					<THEAD class="vertical">
					<TR>
						<TD>
							<TABLE>
								<?php 
									$th = true;
									foreach($diag as $key=>$Aval): 
										if ((array_sum($Aval) > 0)or(trim($key) =='')): ?>
										<TR>
											<TH><?=$key;?></TH>
											<?php if($th) {foreach($Aval as $val){ ?> <TH><?=$val;?></TH> <?php } $th= false;}
											else {foreach($Aval as $val){ ?> <TD><?=$val;?></TD> <?php }}?>
										</TR>
								<?php 		endif;
									endforeach;?>
							</TABLE>
						</TD>
					</TR>
					</THEAD>
				</TABLE>
				<?php endforeach;?>
			</TD>
		</TR>
	</TABLE>
</div>
<?php endif; ?>

<div class="tabbertab">
	<h3>PLs</h3>
	<div class="label">
		<!--<div style="float:right;margin-top:8px"><button onclick="switchview('PLtable');">Afficher/Cacher</button></div>-->
		<h1>POINTS DE LIVRAISON : <?=(isset($PLelecList['new']))?(count($PLelecList)-1):count($PLelecList);?> Elec 
						- <?=(isset($PLeauList['new']))?(count($PLeauList)-1):count($PLeauList);?> Eau 
						- <?=(isset($PLgasoilList['new']))?(count($PLgasoilList)-1):count($PLgasoilList);?> Gasoil</h1>
	</div>
	<?php 
	if (($Site['PLcount'] == 0)&&($mode=='consult')) echo 'aucun point de livraison';
	else { ?>
	<TABLE cellspacing="0" id="PLtable" <?php //if (($idBatiment !== null)||($Suivi)) echo 'style="display:none;"'; ?>>
		<TR onMouseOver="opacity(this,1)" onMouseOut="opacity(this,0.8)" onclick="this.onmouseout = '';" class="blur">
			<TD>
				<?php if(is_array($PLelecList)): ?>
				<div class="label"><h4>Electricité</h4></div>
				<table class="fency">
					<THEAD>
						<TH>No PL</TH>
						<TH>No Compteur</TH>
						<TH>Etat</TH>
						<TH>Tension</TH>
						<TH>Transfo</TH>
						<TH>Année</TH>
						<TH>Puissance</TH>
						<TH>Reglage Disjoncteur</TH>
						<TH>Observations</TH>
					</THEAD>
						
					<!--Liste des PL existants-->
					<?php foreach($PLelecList as $id=>$PL): ?>
					<TR>
						<?=form_open(uri_string());?>
						<?=$PL['idSite_PL_elec']?>
						<TD><?=$PL['NoPL']?></TD>
						<TD><?=$PL['NoCompteur']?></TD>
						<TD><?=$PL['idEtat']?></TD>
						<TD><?=$PL['idTension']?></TD>
						<TD><?=$PL['Transfo']?></TD>
						<TD><?=$PL['Annee']?></TD>
						<TD><?=$PL['Puissance']?> kW</TD>
						<TD><?=$PL['DisjoncteurRegle']?></TD>
						<TD><?=$PL['Commentaire']?></TD>
						<TD><?=$PL['SubmitBTN'].' '.$PL['DeleteBTN'].' '.$PL['EditBTN']?></TD>
						<?=form_close();?>
					</TR>
					<?php endforeach; ?>
					
				</table>
				<?php endif; ?>
			</TD>
		</TR>
		<TR onMouseOver="opacity(this,1)" onMouseOut="opacity(this,0.8)" onclick="this.onmouseout = '';" class="blur">	
			<TD>
				<?php if(is_array($PLeauList)): ?>
				<div class="label"><h4>Eau</h4></div>
				<table class="fency">
					<THEAD>
						<TH>No PL</TH>
						<TH>No Compteur</TH>
						<TH>Usages</TH>
						<TH>Observations</TH>
					</THEAD>
						
					<!--Liste des PL existants-->
					<?php foreach($PLeauList as $id=>$PL): ?>
					<TR>
						<?=form_open(uri_string());?>
						<?=$PL['idSite_PL_eau']?>
						<TD><?=$PL['NoPL']?></TD>
						<TD><?=$PL['NoCompteur']?></TD>
						<TD><?=$PL['UsageEau']?></TD>
						<TD><?=$PL['Commentaire']?></TD>
						<TD><?=$PL['SubmitBTN'].' '.$PL['DeleteBTN'].' '.$PL['EditBTN']?></TD>
						<?=form_close();?>
					</TR>
					<?php endforeach; ?>
					
				</table>
				<?php endif; ?>
			</TD>
		</TR>
		<TR onMouseOver="opacity(this,1)" onMouseOut="opacity(this,0.8)" onclick="this.onmouseout = '';" class="blur">	
			<TD>
				<?php if(is_array($PLgasoilList)): ?>
				<div class="label"><h4>Gasoil</h4></div>
				<table class="fency">
					<THEAD>
						<TH>No Contrat</TH>
						<TH>Conso/An</TH>
						<TH>Cuves</TH>
						<TH>Materiel</TH>
						<TH>Observations</TH>
					</THEAD>
						
					<!--Liste des PL existants-->
					<?php foreach($PLgasoilList as $id=>$PL): ?>
					<TR>
						<?=form_open(uri_string());?>
						<?=$PL['idSite_PL_gasoil']?>
						<TD><?=$PL['NoContrat']?></TD>
						<TD><?=$PL['ConsoAnnuelle']?> m3 <br /><?=($PL['ConsoReel'])?'':'Non ';?> Verifiée</TD>
						<TD>
							<table>
								<?php $i=0; $mod = 'Site_PL_gasoil_Cuves'; 
								while(isset($PL[$mod.'_Volume_'.$i])):?> 
										<TR><TD><?=$PL[$mod.'_Volume_'.$i]?> m3</TD><TD><?=$PL[$mod.'_Niveau_'.$i]?> Niveau</TD><TD><?=$PL[$mod.'_Releve_'.$i]?> Relevés</TD></TR>
								<?php $i++; endwhile;?>	
							</table>
						</TD>
						<TD>
							<table>
								<?php $i=0; $mod = 'Site_PL_gasoil_Materiel'; 
								while(isset($PL[$mod.'_Type_'.$i])||isset($PL[$mod.'_Marque_'.$i])):?> 
										<?php if ($i == 0): ?>
										<TR>
											<TH>Type</TH>
											<TH>Marque</TH>
											<TH>Puissance</TH>
											<TH>Annee</TH>
											<TH>Nbr. Heures</TH>
											<TH></TH>
										</TR>
										<?php endif;?>
										<TR>
											<TD><?=$PL[$mod.'_idMaterielGasoil_'.$i]?></TD>
											<TD><?=$PL[$mod.'_Marque_'.$i]?></TD>
											<TD><?=$PL[$mod.'_Puissance_'.$i]?> kW</TD>
											<TD><?=$PL[$mod.'_Annee_'.$i]?></TD>
											<TD><?=$PL[$mod.'_HeureNbr_'.$i]?> h</TD>
											<TD><?=($PL[$mod.'_CompteurEauChaude_'.$i])?$PL[$mod.'_CompteurEauChaude_'.$i].'Compt. eau chaude':''?></TD>
										</TR>
								<?php $i++; endwhile;?>	
							</table>
						</TD>
						<TD><?=$PL['Commentaire']?></TD>
						<TD><?=$PL['SubmitBTN'].'<br />'.$PL['DeleteBTN'].'<br />'.$PL['EditBTN']?></TD>
						<?=form_close();?>
					</TR>
					<?php endforeach; ?>
					
				</table>
				<?php endif; ?>
			</TD>
		</TR>
	</TABLE>
	<?php } ?>
</div>

<div class="tabbertab">
	<h3>Factures</h3>
	<div class="label">
		<!--<div style="float:right;margin-top:8px"><button onclick="switchview('PLtable');">Afficher/Cacher</button></div>-->
		<h1>Factures : <?=(isset($PLelecList['new']))?(count($PLelecList)-1):count($PLelecList);?> Elec 
						- <?=(isset($PLeauList['new']))?(count($PLeauList)-1):count($PLeauList);?> Eau 
						- <?=(isset($PLgasoilList['new']))?(count($PLgasoilList)-1):count($PLgasoilList);?> Gasoil</h1>
	</div>
	<?php 
	if (($Site['PLcount'] == 0)&&($mode=='consult')) echo 'aucun point de livraison';
	else { ?>
	
				<div class="tabber" id="generalTABS">				
				<?php if(is_array($PLelecList)): ?>
					<?php if(is_array($Facture['BT'])): ?>
					<div class="tabbertab">
						<h3>BT</h3>
						<?php foreach($Facture['BT'] as $numeroPL=>$PLdate): ?>
						<div class="label"><h4>PL n°:<?=$numeroPL?></h4></div>
						<INPUT id='buttondetail2' TYPE=BUTTON OnClick="switchdisplay('hidefacture',2);" value='Afficher détails'>
						<table class="fency">
							<THEAD>
								<TH>Date début</TH>
								<TH>Date fin</TH>
								<TH class="hidefacture" style="display: none">N° client</TH> 	
								<TH class="hidefacture" style="display: none">N° personne</TH> 	
								<TH class="hidefacture" style="display: none">N° de facture</TH> 	
								<TH class="hidefacture" style="display: none">Nature</TH> 	
								<TH class="hidefacture" style="display: none">Catégorie client</TH> 	
								<TH>Code tarif</TH> 	
								<TH class="hidefacture" style="display: none">N° compteur</TH> 	
								<TH class="hidefacture" style="display: none">N° police</TH> 	
								<TH class="hidefacture" style="display: none">Point de livraison</TH> 	
								<TH>Puissance souscrite</TH> 	
								<TH class="hidefacture" style="display: none">Nom prénom</TH> 	
								<TH class="hidefacture" style="display: none">Adresse</TH> 	
								<TH class="hidefacture" style="display: none">Localisation</TH> 	
								<TH class="hidefacture" style="display: none">Code Activite</TH> 	
								<TH>Ancien index</TH> 	
								<TH>Nouvel index</TH> 	
								<TH>Consommation mensuelle</TH> 	
								<TH>Redevance</TH> 	
								<TH>Contribution Spéciale</TH> 	
								<TH>Montant PF</TH> 	
								<TH>Montant HT</TH> 	
								<TH>Montant tva</TH> 	
								<TH>Montant net</TH> 	
								<TH class="hidefacture" style="display: none">Date abonnement</TH> 	
								<TH class="hidefacture" style="display: none">Nb jours</TH>
						
							</THEAD>
						
							<!--Liste des factures BT existantes-->
							<?php foreach($PLdate as $id=>$PL): ?>
								<TR>
								<?php if (isset($PL['N° client'])):?>
									<?=form_open(uri_string());?>
									<TD><?=$PL['Date debut']?></TD>
									<TD><?=$PL['Date_index']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° client']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° personne']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° de facture']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nature']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Catégorie client']?></TD>
									<TD><?=$PL['Code tarif']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° compteur']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° police']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Point_de_livraison']?></TD>
									<TD><?=$PL['Puisance souscrite']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nom prénom']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Adresse']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Localisation']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Code Activite']?></TD>
									<TD><?=$PL['Ancien index']?></TD>
									<TD><?=$PL['Nouvel index']?></TD>
									<TD><?=$PL['Consommation mensuelle']?></TD>
									<TD><?=$PL['Redevance']?></TD>
									<TD><?=$PL['Contribution Spéciale']?></TD>
									<TD><?=$PL['Montant PF']?></TD>
									<TD><?=$PL['Montant HT']?></TD>
									<TD><?=$PL['Montant tva']?></TD>
									<TD><?=$PL['Montant net']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Date abonnement']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nb_jours']?></TD>
									<TD><?=$PL['SubmitBTN'].' '.$PL['DeleteBTN'].' '.$PL['EditBTN']?></TD>
									<?=form_close();?>						
								<?php else:?>
								<TR>
									<TD COLSPAN="13"><center><font color="#bf1c19"><strong><?=$PL['alerte']?></strong></font></center></TD>
								</TR>
								<?php endif ?>
								</TR>
							<?php endforeach; ?>
						</table>
						<?php endforeach; ?>
					</div>
				
					<?php endif; ?>
				
				<?php if(is_array($Facture['MT'])): ?>
				<div class="tabbertab">
					<h3>MT</h3>
					<?php foreach($Facture['MT'] as $numeroPL=>$PLdate): ?>
						<div class="label"><h4>PL n°:<?=$numeroPL?></h4></div>
						
						<INPUT id='buttondetail1' TYPE=BUTTON OnClick="switchdisplay('hidefacture',1);" value='Afficher détails'>
						<table class="fency">
							<THEAD>
								<TH>Date début</TH>
								<TH>Date fin</TH>
								<TH class="hidefacture" style="display: none">N° client</TH> 	
								<TH class="hidefacture" style="display: none">N° personne</TH>
								<TH class="hidefacture" style="display: none">N° de facture</TH>
								<TH class="hidefacture" style="display: none">Nature</TH>
								<TH class="hidefacture" style="display: none">Catégorie client</TH>
								<TH>Tarif</TH>
								<TH class="hidefacture" style="display: none">N° compteur</TH>
								<TH class="hidefacture" style="display: none">N° police</TH>
								<TH class="hidefacture" style="display: none">Point de livraison</TH>
								<TH>Puissance souscrite</TH>
								<TH class="hidefacture" style="display: none">Nom prénom</TH>
								<TH class="hidefacture" style="display: none">Adresse</TH>
								<TH class="hidefacture" style="display: none">Localisation</TH>
								<TH class="hidefacture" style="display: none">Code Activite</TH>
								<TH>Coefficient PA</TH>
								<TH>Conso PA</TH>		
								<TH>Ancien Index Pointe</TH>
								<TH>Nouvel index Pointe</TH>
								<TH>Conso Pointe</TH>
								<TH>Montant HT Pointe</TH>
								<TH>Contribution Spéciale Pointe</TH>
								<TH>Montant Net Pointe</TH>
								<TH>Ancien Index Hors Pointe</TH>
								<TH>Nouvel Index Hors Pointe</TH>
								<TH>Conso Hors Pointe</TH>
								<TH>Montant HT Hors Pointe</TH>
								<TH>Contribution Spéciale Hors Pointe</TH>
								<TH>Montant Net Hors Pointe</TH>
								<TH>Ancien Index Réactif</TH>
								<TH>Nouvel Index Réactif</TH>
								<TH>Conso Energie Réactive</TH>
								<TH>Montant prime HT</TH>
								<TH>Montant Prime TTC</TH>
								<TH class="hidefacture" style="display: none">Ancien Index Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Nouvel Index Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Conso Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Contribution Spéciale Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Montant HT Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Montant Net Pertes Cuivre</TH>
								<TH class="hidefacture" style="display: none">Ancien Index Pertes fer</TH>
								<TH class="hidefacture" style="display: none">Nouvel Index Pertes Fer</TH>
								<TH class="hidefacture" style="display: none">Conso Pertes Fer</TH>
								<TH class="hidefacture" style="display: none">Montant HT Pertes Fer</TH>
								<TH class="hidefacture" style="display: none">Contribution Spéciale Pertes Fer</TH>
								<TH class="hidefacture" style="display: none">Montant Net Pertes Fer</TH>
								<TH>Conso Dépassement PS</TH>
								<TH>Montant HT Pénalité Dépassement PS</TH>
								<TH>Montant Net Pénalité Dépassement PS</TH>
								<TH>Cosinus phi</TH>
								<TH>Montant HT Cosinus PHI</TH>
								<TH>Montant Net Cosinus PHI</TH>
								<TH>MT REDEVANCE HT</TH>
								<TH>Montant net</TH>
								<TH class="hidefacture" style="display: none">Date abonnement</TH>
								<TH class="hidefacture" style="display: none">Nb jours</TH>
							</THEAD>
						
							<!--Liste des factures MT existantes-->
							<?php foreach($PLdate as $id=>$PL): ?>
							<TR>
								<?php if (isset($PL['N° client'])):?>
									<?=form_open(uri_string());?>
									<TD><?=$PL['Date debut']?></TD>
									<TD><?=$PL['Date_index']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° client']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° personne']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° de facture']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nature']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Catégorie client']?></TD>
									<TD><?=$PL['Tarif']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° compteur']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['N° police']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Point_de_livraison']?></TD>
									<TD><?=$PL['Puisance souscrite']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nom prénom']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Adresse']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Localisation']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Code Activite']?></TD>
									<TD><?=$PL['Coefficient PA']?></TD>
									<TD><?=$PL['Conso PA']?></TD>		
									<TD><?=$PL['Ancien Index Pointe']?></TD>
									<TD><?=$PL['Nouvel Index Pointe']?></TD>
									<TD><?=$PL['Conso Pointe']?></TD>
									<TD><?=$PL['Montant HT Pointe']?></TD>
									<TD><?=$PL['Contribution Spéciale Pointe']?></TD>
									<TD><?=$PL['Montant Net Pointe']?></TD>
									<TD><?=$PL['Ancien Index Hors Pointe']?></TD>
									<TD><?=$PL['Nouvel Index Hors Pointe']?></TD>
									<TD><?=$PL['Conso Hors Pointe']?></TD>
									<TD><?=$PL['Montant HT Hors Pointe']?></TD>
									<TD><?=$PL['Contribution Spéciale Hors Pointe']?></TD>
									<TD><?=$PL['Montant Net Hors Pointe']?></TD>
									<TD><?=$PL['Ancien Index Réactif']?></TD>
									<TD><?=$PL['Nouvel Index Réactif']?></TD>
									<TD><?=$PL['Conso Energie Réactive']?></TD>
									<TD><?=$PL['Montant prime HT']?></TD>
									<TD><?=$PL['Montant Prime TTC']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Ancien Index Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nouvel Index Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Conso Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Contribution Spéciale Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Montant HT Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Montant Net Pertes Cuivre']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Ancien Index Pertes fer']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nouvel Index Pertes Fer']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Conso Pertes Fer']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Montant HT Pertes Fer']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Contribution Spéciale Pertes Fer']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Montant Net Pertes Fer']?></TD>
									<TD><?=$PL['Conso Dépassement PS']?></TD>
									<TD><?=$PL['Montant HT Pénalité Dépassement PS']?></TD>
									<TD><?=$PL['Montant Net Pénalité Dépassement PS']?></TD>
									<TD><?=$PL['Cosinus phi']?></TD>
									<TD><?=$PL['Montant HT Cosinus PHI']?></TD>
									<TD><?=$PL['Montant Net Cosinus PHI']?></TD>
									<TD><?=$PL['MT REDEVANCE HT']?></TD>
									<TD><?=$PL['Montant net']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Date abonnement']?></TD>
									<TD class="hidefacture" style="display: none"><?=$PL['Nb_jours']?></TD>
									<TD><?=$PL['SubmitBTN'].' '.$PL['DeleteBTN'].' '.$PL['EditBTN']?></TD>
									<?=form_close();?>
								<?php else:?>
								<TR>
									<TD COLSPAN="14"><center><font color="#bf1c19"><strong><?=$PL['alerte']?></strong></font></center></TD>
								</TR>
								<?php endif ?>
							</TR>
							<?php endforeach; ?>
					
						</table>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<?php endif; ?>

				<?php if(is_array($PLeauList)): ?>
					<?php if(is_array($Facture['EAU'])): ?>
					<div class="tabbertab">
						<h3>EAU</h3>
						<?php foreach($Facture['EAU'] as $numeroPL=>$PLdate): ?>
						<div class="label"><h4>PL n°:<?=$numeroPL?></h4></div>
						<INPUT id='buttondetail1' TYPE=BUTTON OnClick="switchdisplay('hidefacture',1);" value='Afficher détails'>
						<table class="fency">
							<THEAD>
								<TH>Date début</TH>
								<TH>Date fin</TH>
								<TH class="hidefacture" style="display: none">N° client</TH> 	
								<TH class="hidefacture" style="display: none">N° personne</TH> 	
								<TH class="hidefacture" style="display: none">N° de facture</TH> 	
								<TH class="hidefacture" style="display: none">Nature</TH> 	
								<TH class="hidefacture" style="display: none">Catégorie client</TH> 	
								<TH>Code tarif</TH> 	
								<TH class="hidefacture" style="display: none">N° compteur</TH> 	
								<TH class="hidefacture" style="display: none">N° police</TH> 	
								<TH class="hidefacture" style="display: none">Point de livraison</TH> 	
								<TH>Puissance souscrite</TH> 	
								<TH class="hidefacture" style="display: none">Nom prénom</TH> 	
								<TH class="hidefacture" style="display: none">Adresse</TH> 	
								<TH class="hidefacture" style="display: none">Localisation</TH> 	
								<TH class="hidefacture" style="display: none">Code Activite</TH> 	
								<TH>Ancien index</TH> 	
								<TH>Nouvel index</TH> 	
								<TH>Consommation mensuelle</TH> 	
								<TH>Redevance</TH> 	
								<TH>Contribution Spéciale</TH> 	
								<TH>Montant PF</TH> 	
								<TH>Montant HT</TH> 	
								<TH>Montant tva</TH> 	
								<TH>Montant net</TH> 	
								<TH class="hidefacture" style="display: none">Date abonnement</TH> 	
								<TH class="hidefacture" style="display: none">Nb jours</TH>
							</THEAD>
						
							<!--Liste des factures EAU existantes-->
							<?php foreach($PLdate as $id=>$PL): ?>
							<TR>
								<?php if (isset($PL['N° client'])):?>
									<?=form_open(uri_string());?>
										<TD><?=$PL['Date debut']?></TD>
										<TD><?=$PL['Date_index']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['N° client']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['N° personne']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['N° de facture']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Nature']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Catégorie client']?></TD>
										<TD><?=$PL['Code tarif']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['N° compteur']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['N° police']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Point_de_livraison']?></TD>
										<TD><?=$PL['Puisance souscrite']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Nom prénom']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Adresse']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Localisation']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Code Activite']?></TD>
										<TD><?=$PL['Ancien index']?></TD>
										<TD><?=$PL['Nouvel index']?></TD>
										<TD><?=$PL['Consommation mensuelle']?></TD>
										<TD><?=$PL['Redevance']?></TD>
										<TD><?=$PL['Contribution Spéciale']?></TD>
										<TD><?=$PL['Montant PF']?></TD>
										<TD><?=$PL['Montant HT']?></TD>
										<TD><?=$PL['Montant tva']?></TD>
										<TD><?=$PL['Montant net']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Date abonnement']?></TD>
										<TD class="hidefacture" style="display: none"><?=$PL['Nb_jours']?></TD>
										<TD><?=$PL['SubmitBTN'].' '.$PL['DeleteBTN'].' '.$PL['EditBTN']?></TD>
									<?=form_close();?>
								<?php else:?>
								<TR>
									<TD COLSPAN="14"><center><font color="#bf1c19"><strong><?=$PL['alerte']?></strong></font></center></TD>
								</TR>
								<?php endif ?>
							</TR>
							<?php endforeach; ?>
					
						</table>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

	<?php } ?>
</div>

<div class="tabbertab">
	<h3>Batiments</h3>
	<div class="label"><!--
		<div style="float:right;margin-top:8px"><button onclick="switchview('BATtable');">Afficher/Cacher</button></div>-->
		<h1>BATIMENTS  (<?=$Site['BATcount']?>)</h1>
	</div>
	<table id="BATtable" <?php //echo (!($idBatiment > 0) and ($idBatiment != 'new'))?'style="display:none"':''; ?>>
		<TR>
			<TD style="vertical-align:top;border:0px solid black;padding:0px;" rowspan="2">
				<ul class="tab_left">
					<?php 
					if (is_array($BatimentsList))
					foreach($BatimentsList as $id=>$Nom): 
						if ($id == $idBatiment) $class = ' class="active"';
						else $class = 'onclick="goto(\''.$mode.'/site/'.$idSite.'/'.$id.'\')"';
					?>
					<li <?=$class?>> <?=$Nom?> </li>
					<?php endforeach; ?>
				</ul>
			</TD>
			<TD style="vertical-align:top;height:50px;">
				<br />
				<?php if (!is_null($idBatiment)): ?>
				<?=form_open(uri_string());?>
				<TABLE class="fency">
					<THEAD class="vertical">
						<TR>
							<TH>Bâtiment <?=($Batiment['Statut'] > 2)?$Batiment['Nom']:''?></TH>
						</TR>
						<TR>
							<TD><?=$Batiment['SubmitBTN']?><?=$Batiment['EditBTN']?><?=$Batiment['DeleteBTN']?></TD>
						</TR>
						<TR>
							<TD><?=$Batiment['StepBTN']?></TD>
						</TR>
					</THEAD>
				</TABLE>
			</TD>
			
			<TD>	
				<?php if ($Batiment['Statut'] > 1): ?>
				<TABLE class="fency">
					<THEAD class="vertical">
						<TR>
							<TH>Observations Générales</TH>
						</TR>
						<TR>
							<TD><?=$Batiment['Commentaire']?></TD>
						</TR>
						
					</THEAD>
				</TABLE>
				<?php endif; ?>
				
			</TD>	
		</TR>
		<TR>
			<TD  style="height:250px;vertical-align:top" colspan="2">
			<br />
				<div class="tabber" id="tabResidenceA">
					<div class="tabbertab">
					<h3>Generalités</h3>
						<TABLE class="fency">
							<THEAD class="vertical">
								<TR>
									<TH>NOM</TH><TD><strong><?=$Batiment['Nom']?></strong></TD>
								<?php if ($Batiment['Statut'] > 1): ?>
								</TR>
								<TR>
									<TH>Année</TH><TD><?=$Batiment['Annee']?></TD>
									<TH>Occupation</TH><TD><?=$Batiment['Occupation']?></TD>
									<TH>Etat Apparent</TH><TD><strong><?=$Batiment['idEtatApparent']?></strong></TD>
								</TR>
								<TR>	
								<?php endif; ?>
									<TH>Nbr. Niveau</TH><TD><?=$Batiment['NiveauNbr']?></TD>
								<?php if ($Batiment['Statut'] > 1): ?>	
									<TH>Horaires</TH><TD><?=$Batiment['Horaires']?></TD>
									<TH>Forme de Toiture</TH><TD><?=$Batiment['ToitureForme']?></TD>								
								</TR>
								<TR>	
									<TH>Surface Sol</TH><TD><?=$Batiment['SurfaceSol']?> m2 - <?=$Batiment['SurfaceSolReel']?> Réel</TD>
									<TH>Jour / Semaine</TH><TD><?=$Batiment['JoursNbr']?></TD>
									<TH>Type de Toiture</TH><TD><?=$Batiment['ToitureType']?></TD>
								</TR>
								<TR>
									<TH>Surface Totale</TH><TD><?=$Batiment['SurfaceTotal']?> m2 - <?=$Batiment['SurfaceTotalReel']?> Réel</TD>
									<TH><?php if ($Site['idSiteType']=='Ecole'): ?>Administratifs<?php else: ?>Nbr. Occupant<?php endif; ?></TH><TD><?=$Batiment['OccupantNbr']?></TD>
									<TH>Evacuation Pluie</TH><TD><?=$Batiment['EvacuationPluie']?></TD>
								</TR>
								<?php if ($Site['idSiteType']=='Ecole'): ?>
								<TR>	
									<TH></TH><TD></TD>
									<TH>Nbr élèves</TH><TD><?=$Batiment['OccupantNbr2']?></TD>
									<TH></TH><TD></TD>
								</TR>
								<?php endif; ?>
								<?php endif; ?>
							</THEAD>
						</TABLE>
						
					</div>
					<?php if ($Batiment['Statut'] > 1): ?>
					<!--<div class="tabbertab">
					<h3>Niveaux</h3><br />
						
						<br /><br />
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach(current($Batiment['Niveaux']) as $n=>$input):?>
									<TH>Niveau <?=$n?></TH>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Nom</TH>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
						</TABLE>
					</div>-->
					<div class="tabbertab">
					<h3>Froid/Air</h3>
						<TABLE>
							<TR>
								<TD>
									<TABLE class="fency" style="border:1px solid #CCC;padding:5px;">
										<THEAD class="vertical">
											<TR>
												<TH colspan="4">Groupes Froid</TH>
											</TR>
											<TR>
												<TH></TH><TH>Service</TH><TH>Secours</TH><TH>HS</TH>
											</TR>
											<TR>
												<TH>Nombre</TH><TD><?=$Batiment['FroidNombre']?></TD><TD><?=$Batiment['FroidNombreSecour']?></TD><TD><?=$Batiment['FroidNombreHS']?></TD>
											</TR>
											<TR>
												<TH>Marque</TH><TD><?=$Batiment['FroidMarque']?></TD><TD><?=$Batiment['FroidMarqueSecour']?></TD><TD><?=$Batiment['FroidMarqueHS']?></TD>
											</TR>
											<TR>	
												<TH>Modèle</TH><TD><?=$Batiment['FroidModele']?></TD><TD><?=$Batiment['FroidModeleSecour']?></TD><TD><?=$Batiment['FroidModeleHS']?></TD>
											</TR>
											<TR>	
												<TH>Puissance</TH><TD><?=$Batiment['FroidPuissance']?> kW</TD><TD><?=$Batiment['FroidPuissanceSecour']?> kW</TD><TD><?=$Batiment['FroidPuissanceHS']?> kW</TD>
											</TR>
										</THEAD>
									</TABLE>
								</TD>
								<TD style="vertical-align:top">
									<TABLE class="fency" style="border:1px solid #CCC;padding:5px;">
										<THEAD class="vertical">
											<TR>
												<TH colspan="2">Centrales Air</TH>
											</TR>
											<TR>
												<TH  style="height:21px"></TH>
											</TR>
											<TR>
												<TH>Nombre</TH><TD><?=$Batiment['TraitementNombre']?></TD>
											</TR>
											<TR>
												<TH>Marque</TH><TD><?=$Batiment['TraitementMarque']?></TD>
											</TR>
											<TR>	
												<TH>Modèle</TH><TD><?=$Batiment['TraitementModele']?></TD>
											</TR>
											<TR>	
												<TH>Puissance</TH><TD><?=$Batiment['TraitementPuissance']?> kW</TD>
											</TR>
										</THEAD>
									</TABLE>
								</TD>
								<TD style="vertical-align:top">
									<TABLE class="fency" style="border:1px solid #CCC;padding:5px;">
										<THEAD class="vertical">
											<TR>
												<TH colspan="2">Ventilo-Convecteurs</TH>
											</TR>
											<TR>
												<TH></TH>
											</TR>
											<TR>
												<TH>Nombre</TH><TD><?=$Batiment['VentiloConvecteursNombre']?></TD>
											</TR>
										</THEAD>
									</TABLE>
								</TD>
							</TR>
							<TR>
								<TD colspan="3">
									<TABLE class="fency" style="border:1px solid #CCC;padding:5px;">
										<THEAD class="vertical">
											<TR>
												<TH>Conduite des Installations</TH><TD><?=$Batiment['Installation']?></TD>
											</TR>
											<TR>
												<TH>Conduite de la Maintenance</TH><TD><?=$Batiment['Maintenance']?></TD>
												<TH>Periodicite Maintenance</TH><TD><?=$Batiment['MaintenancePeriodicite']?></TD>
											</TR>
										</THEAD>
									</TABLE>
								</TD>
							</TR>
						</TABLE>
						
					</div>
						
					<div class="tabbertab">
					<h3>Pieces</h3>
						Vous pouvez desormais donner un nom à chaque niveau pour facilité le reperage. Par exemple : "sous-sol", "RDC", ...<br><br>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php 
										if (count(current($Batiment['Niveaux'])) > 6) $jumpN = '<br />';
										else $jumpN = '&nbsp;';
										
										foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 1) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH> Bureaux</TH>
									<?php foreach($Batiment['Niveaux']['BureauNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Salles de Réunion</TH>
									<?php foreach($Batiment['Niveaux']['ReunionNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Logements</TH>
									<?php foreach($Batiment['Niveaux']['LogementNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Hall / Accueil</TH>
									<?php foreach($Batiment['Niveaux']['HallNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Cantine / Cafétéria</TH>
									<?php foreach($Batiment['Niveaux']['CantineNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Gymnase / Sport</TH>
									<?php foreach($Batiment['Niveaux']['SportNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Labo / Ateliers</TH>
									<?php foreach($Batiment['Niveaux']['LaboNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Salle de Classe</TH>
									<?php foreach($Batiment['Niveaux']['ClasseNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Stocks</TH>
									<?php foreach($Batiment['Niveaux']['StockNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Infirmerie</TH>
									<?php foreach($Batiment['Niveaux']['InfirmerieNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Chambres d'hôpital</TH>
									<?php foreach($Batiment['Niveaux']['ChambreHopitalNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH> Plateau technique</TH>
									<?php foreach($Batiment['Niveaux']['PlateauTechNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Autre 1</TH>
									<?php foreach($Batiment['Niveaux']['Autre1'] as $n=>$input):?>
										<TD><?=$input?><br /><?=$Batiment['Niveaux']['Autre1Nombre'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Autre 2</TH>
									<?php foreach($Batiment['Niveaux']['Autre2'] as $n=>$input):?>
										<TD><?=$input?><br /><?=$Batiment['Niveaux']['Autre2Nombre'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					
					<div class="tabbertab">
					<h3>Sanitaires</h3>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 2) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Sanitaires</TH>
									<?php foreach($Batiment['Niveaux']['SanitaireNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>WC</TH>
									<?php foreach($Batiment['Niveaux']['WcNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Urinoirs</TH>
									<?php foreach($Batiment['Niveaux']['UrinoirNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Lavabos</TH>
									<?php foreach($Batiment['Niveaux']['LavaboNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Douches</TH>
									<?php foreach($Batiment['Niveaux']['DoucheNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Autre </TH>
									<?php foreach($Batiment['Niveaux']['SAutre1'] as $n=>$input):?>
										<TD><?=$input?><br /><?=$Batiment['Niveaux']['SAutre1Nombre'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Etat</TH>
									<?php foreach($Batiment['Niveaux']['idEtatApparent'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					
					<div class="tabbertab">
					<h3>Electricité</h3>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 2) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Tableau div. Fusible</TH>
									<?php foreach($Batiment['Niveaux']['TableauFusible'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Tableau div. Disjoncteur</TH>
									<?php foreach($Batiment['Niveaux']['TableauDisjoncteur'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Autre Distribution</TH>
									<?php foreach($Batiment['Niveaux']['ElecAutre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Armoires</TH>
									<?php foreach($Batiment['Niveaux']['ArmoireNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Schéma elec</TH>
									<?php foreach($Batiment['Niveaux']['ElecSchema'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Départs séparés</TH>
									<?php foreach($Batiment['Niveaux']['SeparationElecClim'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Repérage possible</TH>
									<?php foreach($Batiment['Niveaux']['ReperageAppareil'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Etat armoires</TH>
									<?php foreach($Batiment['Niveaux']['idArmoireEtat'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>

								<TR>
								<TR>
									<TH>Conduite de la Maintenance</TH><TD colspan="12"><?=$Batiment['MaintenanceElec']?></TD>
								</TR>
								<TR>
									<TH>Periodicite Maintenance</TH><TD colspan="12"><?=$Batiment['MaintenanceElecPeriodicite']?></TD>
								</TR>
								</TD></TR>
								<TR><TD colspan="12"><hr /></TD></TR>
								<TR>
									<TH>Postes informatiques</TH>
									<?php foreach($Batiment['Niveaux']['OrdinateurNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Imprimante</TH>
									<?php foreach($Batiment['Niveaux']['ImprimanteNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Photocopieuse</TH>
									<?php foreach($Batiment['Niveaux']['PhotocopieuseNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Brasseur</TH>
									<?php foreach($Batiment['Niveaux']['BrasseurNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Clim SPLIT 1</TH>
									<?php foreach($Batiment['Niveaux']['SplitPuissance'] as $n=>$input):?>
										<TD><?=$input?>W<?=$jumpN?><?=$Batiment['Niveaux']['SplitNombre'][$n]?>(Nbr)</TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Clim SPLIT 2</TH>
									<?php foreach($Batiment['Niveaux']['Split2Puissance'] as $n=>$input):?>
										<TD><?=$input?>W<?=$jumpN?><?=$Batiment['Niveaux']['Split2Nombre'][$n]?>(Nbr)</TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Clim Bow Window 1</TH>
									<?php foreach($Batiment['Niveaux']['BowPuissance'] as $n=>$input):?>
										<TD><?=$input?>W<?=$jumpN?><?=$Batiment['Niveaux']['BowNombre'][$n]?>(Nbr)</TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Clim Bow Window 2</TH>
									<?php foreach($Batiment['Niveaux']['Bow2Puissance'] as $n=>$input):?>
										<TD><?=$input?>W<?=$jumpN?><?=$Batiment['Niveaux']['Bow2Nombre'][$n]?>(Nbr)</TD>
									<?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					
					<div class="tabbertab">
					<h3>Eclairage PIECES</h3>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 2) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR><TH>PIECES</TH></TR>
								<TR>
									<TH>Ampoule incandesc.</TH>
									<?php foreach($Batiment['Niveaux']['IncandescNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Ampoule Basse Conso</TH>
									<?php foreach($Batiment['Niveaux']['BasseConsoNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Lustre</TH>
									<?php foreach($Batiment['Niveaux']['LustrePuissance'] as $n=>$input):?>
										<TD>P:<?=$input?>W <?=$jumpN?>nbr:<?=$Batiment['Niveaux']['LustreNombre'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 1</TH>
									<?php foreach($Batiment['Niveaux']['Neon1Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['Neon1Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['Neon1PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['Neon1HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 2</TH>
									<?php foreach($Batiment['Niveaux']['Neon2Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['Neon2Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['Neon2PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['Neon2HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 3</TH>
									<?php foreach($Batiment['Niveaux']['Neon3Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['Neon3Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['Neon3PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['Neon3HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 4</TH>
									<?php foreach($Batiment['Niveaux']['Neon4Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['Neon4Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['Neon4PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['Neon4HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					
					<div class="tabbertab">
					<h3>Eclairage COULOIR</h3>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 2) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR><TH>COULOIRS</TH></TR>
								<TR>
									<TH>Ampoule incandesc.</TH>
									<?php foreach($Batiment['Niveaux']['CIncandescNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Ampoule Basse Conso</TH>
									<?php foreach($Batiment['Niveaux']['CBasseConsoNombre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Lustre</TH>
									<?php foreach($Batiment['Niveaux']['CLustrePuissance'] as $n=>$input):?>
										<TD>P:<?=$input?>W <?=$jumpN?>nbr:<?=$Batiment['Niveaux']['CLustreNombre'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 1</TH>
									<?php foreach($Batiment['Niveaux']['CNeon1Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['CNeon1Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['CNeon1PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['CNeon1HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 2</TH>
									<?php foreach($Batiment['Niveaux']['CNeon2Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['CNeon2Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['CNeon2PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['CNeon2HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 3</TH>
									<?php foreach($Batiment['Niveaux']['CNeon3Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['CNeon3Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['CNeon3PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['CNeon3HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Bloc néon 4</TH>
									<?php foreach($Batiment['Niveaux']['CNeon4Taille'] as $n=>$input):?>
										<TD>T:<?=$input?><?=$jumpN?>n:<?=$Batiment['Niveaux']['CNeon4Nombre'][$n]?><?=$jumpN?>
											PU<?=$Batiment['Niveaux']['CNeon4PU'][$n]?><?=$jumpN?>HS<?=$Batiment['Niveaux']['CNeon4HS'][$n]?></TD>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Detecteurs</TH>
									<?php foreach($Batiment['Niveaux']['Detecteur'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Interrupteur</TH>
									<?php foreach($Batiment['Niveaux']['Interrupteur'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>- avec Tempo</TH>
									<?php foreach($Batiment['Niveaux']['Tempo'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
								<TR>
									<TH>Autre Automatisme</TH>
									<?php foreach($Batiment['Niveaux']['AutoAutre'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					<div class="tabbertab">
					<h3>Observations</h3>
						<TABLE class="bigArray" style="border:1px solid #CCC;padding:5px;">
								<TR>
									<TD></TD>
									<?php foreach($Batiment['Niveaux']['Nom'] as $n=>$input):
										if($Batiment['Statut'] > 2) $label = $input; else $label = ''; 
									?>
									<TH>Niveau <?=$n?><br /><?=$label?></TH>
									<?php endforeach; ?>
								</TR>
								<TR>
									<TH>Observations</TH>
									<?php foreach($Batiment['Niveaux']['Commentaire'] as $n=>$input):?><TD><?=$input?></TD><?php endforeach; ?>
								</TR>
						</TABLE>
					</div>
					
					<?php endif; ?>	
					<?=form_close();?>
					<?php endif; ?>
				</div>
			</TD>
		</TR>
	</table>
</div>
<div class="tabbertab">
		<h3><?php if (isset($Alerte['RougeActive']) and ($Alerte['RougeActive'])) echo '<img src="'.base_url().'/images/icons/selection/flag_red.png" />'?> Alertes					 
		</h3>
		<div class="label">
		<!--<div style="float:right;margin-top:8px"><button onclick="switchview('PLtable');">Afficher/Cacher</button></div>-->
		<h1>ALERTES</h1>
		</div>
		<TABLE cellspacing="0" id="Alertetable">
			<TR onMouseOver="opacity(this,1)" onMouseOut="opacity(this,0.8)" onclick="this.onmouseout = '';" class="blur">
				<TD>
					<?php if (isset($Alerte['elec'])):?>
					<div class="label"><h4>Electricité</h4></div>
					<TR>
						<TD  style="height:200px;vertical-align:top" colspan="2">
						<br />
							<div class="tabber" id="tabAlerteElec">
								<?php foreach($Alerte['elec'] as $nom=>$Liste):?>	
											<div class="tabbertab">
												<h3><?=$nom?></h3>
													<table class="fency">
														<THEAD>
														<TH>Niveau</TH>
														<TH>Date</TH>
														<TH>Commentaire</TH>
														</THEAD>
												
											<?php //$first=true;											
											foreach($Liste as $id=>$AL):?>
											<?=form_open(uri_string());?>
												<TR>
													<TD style="text-align:center;"><?=$AL['Etat']?></TD>
													<TD><?php //if ($first==true) echo '<font color="#FF0000">';?><?=$AL['Date']?></TD>
													<TD><?=$AL['Commentaire']?></TD>
													<TD>
														<?=(isset($AL['SubmitBTN']))?$AL['SubmitBTN']:''?>
														<?=(isset($AL['OrangeBTN']))?$AL['OrangeBTN']:''?>
														<?=(isset($AL['GreenBTN']))?$AL['GreenBTN']:''?>
														<?=isset($AL['EditBTN'])?$AL['EditBTN']:''?>
														<?=$AL['idAlerte']?>
														<?=$AL['Flux']?>
														<?=$AL['CommentaireCache']?>
													</TD>
													<TH></TH>
												</TR>
											<?=form_close();?>
											<?php endforeach; ?>
													</table>
											</div>
									<?php endforeach; ?>
													
														
							</div>
						</TD>
					</TR>
					<?php endif;?>
				</TD>	
			</TR>		
			<TR onMouseOver="opacity(this,1)" onMouseOut="opacity(this,0.8)" onclick="this.onmouseout = '';" class="blur">
				<TD>
					<?php if (isset($Alerte['eau'])):?>
					<div class="label"><h4>Eau</h4></div>
					<TR>
						<TD  style="height:200px;vertical-align:top" colspan="2">
						<br />
							<div class="tabber" id="tabAlerteEau">
								<?php if (isset($Alerte['eau'])):
									    foreach($Alerte['eau'] as $nom=>$Liste):?>
										
											<div class="tabbertab">
												<h3><?=$nom?></h3>
													<table class="fency">
														<THEAD>
														<TH>Niveau</TH>
														<TH>Date</TH>
														<TH>Commentaire</TH>
														</THEAD>
										<?php endforeach;?>
													
										<?php foreach($Liste as $id=>$AL):?>
											<?=form_open(uri_string());?>
												<TR>
													<TD style="text-align:center;"><?=$AL['Etat']?></TD>
													<TD><?=$AL['Date']?></TD>
													<TD><?=$AL['Commentaire']?></TD>
													<TD>
														<?=(isset($AL['SubmitBTN']))?$AL['SubmitBTN']:''?>
														<?=(isset($AL['OrangeBTN']))?$AL['OrangeBTN']:''?>
														<?=(isset($AL['GreenBTN']))?$AL['GreenBTN']:''?>
														<?=isset($AL['EditBTN'])?$AL['EditBTN']:''?>
														<?=$AL['idAlerte']?>
														<?=$AL['Flux']?>
														<?=$AL['CommentaireCache']?>
													</TD>
													<TH></TH>
												</TR>
											<?=form_close();?>
										<?php endforeach; ?>
													</table>
												</div>
								<?php endif?>						
							</div>
						</TD>
					</TR>
					<?php endif;?>
				</TD>	
			</TR>
		</TABLE>
</div>
</div>

<script>


function switchdisplay(className,no_button){

	var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
	var allElements = document.getElementsByTagName("*");
	var results = [];
	var element;
	for (var i = 0; (element = allElements[i]) != null; i++) {
		var elementClass = element.className;
		if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
			if (element.style.display=='none'){
		   		element.style.display='';
		   	}
		   	else{
		   		element.style.display='none'
		   	}
	}
	if(document.getElementById('buttondetail'+no_button).value=='Masquer détails'){
		document.getElementById('buttondetail'+no_button).value='Afficher détails';
	}
	else{
		document.getElementById('buttondetail'+no_button).value='Masquer détails';
	}
}

</script>
