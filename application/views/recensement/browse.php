<table align="center">
	<TR>
		<TD style="vertical-align:top">
			<table class='fency' align="center">
				<THEAD>
					<TR><TD onclick="goto('recensement/site/new')" class="button"><strong>Nouveau Site</strong></TD></TR>
				</THEAD>
			</table>
		</TD>
		<TD style="vertical-align:top" colspan=1>
			<table class="fency">
				<!--
				<THEAD>
					<TH colspan="2">PROGRESSION</TH>
				</THEAD>
				-->
				<TR>
					<TD>Nombre Total de Sites : </TD><TD><?=$count['total']?></TD>
				</TR>
				<!--
				<TR>
					<TD><strong>Progression du Recensement : </strong></TD>
					<TD><strong><?=($count['total']>0)?round($count['complete']*100/$count['total']):'0'?> %</strong></TD>
				</TR>
				-->
			</table>
		</TD>
	</TR>
	<TR>
		<TD colspan=2>
			<table class="fency">
				<THEAD>
					<TH>Site à valider (<?=count($inprogress)?>)</TH>
				</THEAD>
				<TR>
					<TD>
						<div class="divList" style="width:700px">
						<table align="center">
							<THEAD>
								<TR><TD></TD><TD></TD><TD><u>Saisie</u></TD><TD><u>Equipe</u></TD></TR>
								<?php foreach($inprogress as $id=>$site): ?>
								<TR>
									<TD><?=$site->Ministere?></TD>
									<TD onclick="goto('recensement/site/<?=$id?>')" class="button"><strong><?=$site->Nom?></strong></TD>
									<TD>
										<?php
											$j = ceil((strtotime($site->DateSaisie)-time())/86400);
											if ($j == 0) echo '<em>aujourd\'hui</em>';
											else if ($j < 0) 
											{
												if ($j < -2) echo '<div style="color:red">';
												if ($j == -1) echo 'hier';
												else echo 'il y a '.abs($j).' jours'; 
												if ($j < -3) echo '</div>';
											}
										?>
									</TD>
									<TD><?=$site->Equipe?></TD>
								</TR>
								<?php endforeach; ?>
							</THEAD>
						</table>
						</div>
					</TD>
				</TR>
			</table>
		</TD>
		<TD>
			<table class="fency" style="display:none;">
				<THEAD>
					<TH>Sites Terminés (<?=$count['complete']?>)</TH>
				</THEAD>
				<TR>
					<TD>
						<div class="divList">
						<table align="center">
							<THEAD>
								<TR><TD></TD><TD><u>Site</u></TD></TR>
								<?php foreach($complete as $id=>$site): ?>
								<TR>
									<TD><?=$site->Ministere?></TD><TD onclick="goto('consult/site/<?=$id?>')" class="button"><strong><?=$site->Nom?></strong></TD>								
								</TR>
								<?php endforeach; ?>
							</THEAD>
						</table>
						</div>
					</TD>
				</TR>
			</table>
		</TD>
	</TR>
	<TR>
		
	</TR>
	<TR>
		<?php if(!($byPass1)): ?>
		<TD rowspan="3">
			<table class="fency">
				<THEAD>
					<TH>Sites à recenser (<?=count($virgins)?>)</TH>
				</THEAD>
				<TR>
					<TD>
						<div class="divList" style="height:278px">
						<table align="center">
							<THEAD>
								<TR><TD></TD><TD></TD><TD><u>Visite prévue</u></TD><TD><u>Equipe</u></TD></TR>
								<?php foreach($virgins as $id=>$site): ?>
								<TR>
									<TD><?=$site->Ministere?></TD>
									<TD onclick="goto('recensement/site/<?=$id?>')" class="button"><strong><?=$site->Nom?></strong></TD>
									<TD>
										<?php
											$j = ceil((strtotime($site->DatePrevue)-time())/86400);
											if ($j == 0) echo '<em>aujourd\'hui</em>';
											else if ($j < 0) 
											{
												echo '<div style="color:red">';
												if ($j == -1) echo 'hier';
												else echo '<strong>il y a '.abs($j).' jours</strong>'; 
												echo '</div>';
											}
											else 
											{
												echo '<div style="color:green">';
												if ($j == 1) echo 'demain';
												else echo 'dans '.$j.' jours';
												echo '</div>';
											}
										?>
									</TD>
									<TD><?=$site->Equipe?></TD>
								</TR>
								<?php endforeach; ?>
							</THEAD>
						</table>
						</div>
					</TD>
				</TR>
			</table>
		</TD>
		<TD rowspan="3"></TD>
		<?php endif; ?>
	</TR>
</table>
