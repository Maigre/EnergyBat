

<style>
  #dek
  {
  font-family: Lucida Grande, Verdana, Sans-serif;
  font-size: 10px; font-style: normal; 
  line-height:15px;
  font-weight: bold;
  padding: 5px;
  border:1px solid #5C443A;
  background-color: #ffffde; 
  color: #4F5155;
  text-align: justify;
  position: absolute;
  visibility: hidden;
  text-shadow: 1px 1px 0px white;
  border-radius: 3px;
  z-index: 3;
  }
</style>


<div id="dek"></div>


<table align="center">
	<TR>
		<TD>
			<table class="fency fencyLine">
				<TR>
					<TH>
						<h3>Consultation des Sites</h3>
					</TH>
					<TH>
						<!-- Print Form -->
						<?=form_open('consult/printList');?>
							<input type="image" src="<?=base_url()?>images/icons/selection/printer.png" style="margin-top:5px;margin-left:5px;" />
						<?=form_close();?>
					</TH>
				</TR>
				<TR>
					<TD colspan="2">
						<!-- Tri formulaire -->	
						<?=form_open('consult/');?>
						<table>
							<THEAD>
								<TR>
									<TD rowspan="3" style="width:17px;border:0;">&nbsp;</TD>
									<TH rowspan="3" style="width:225px"><input type="radio" name="order" value="Ministere" onClick="this.form.submit()" <?=($Order=='Ministere')?'checked':'';?> /> Ministère<br /><?=$MinistereListe?></TH>
									<TH rowspan="3" style="width:277px"><input type="radio" name="order" value="Site" onClick="this.form.submit()" <?=($Order=='Site')?'checked':'';?> /> Site<br /><?=$SiteTypeListe?></TH>
									<TH colspan="3">Electricité - kWh</TH>
									<TH colspan="1">Eau - L</TH>
									<TD rowspan="3" style="border:0;">&nbsp;</TD>
								</TR>
								<TR>								
										<!--<TH>PS</TH>-->
											<TH style="width:98px;text-align:right">/an<input type="radio" name="order" value="KWh/an" onClick="this.form.submit()" <?=($Order=='KWh/an')?'checked':'';?>></TH>
											<TH style="width:89px;text-align:right">/hab/an<input type="radio" name="order" value="KWh/hab/J" onClick="this.form.submit()" <?=($Order=='KWh/hab/J')?'checked':'';?>></TH>
											<TH style="width:69px;text-align:right">/m²/an<input type="radio" name="order" value="KWh/m²/J" onClick="this.form.submit()" <?=($Order=='KWh/m²/J')?'checked':'';?>></TH><!--<TH style="width:60px">TC</TH>-->
										<!--<TH>DS</TH>-->
											<TH style="width:78px;text-align:right">/hab/J <input type="radio" name="order" value="L/hab/J" onClick="this.form.submit()" <?=($Order=='L/hab/J')?'checked':'';?>></TH><!--<TH style="width:60px">TC</TH>-->
								</TR>
							</THEAD>
						</table>
						
						<div class="divList">
						<table>
							<TBODY class="ov">
							<?php $classement_site=1; 
							foreach($complete as $id=>$site): ?>
							<TR>
								<TD style="width:15px"><center><div style="font-size:10px;font-weight:bold;"><?=$classement_site?></div></center></TD>
								<?php $classement_site=$classement_site+1;?>
								<TD style="width:228px"><?=$site->Ministere?></TD>
								<TD style="width:281px" onclick="goto('consult/site/<?=$id?>')" class="button"><strong><?=$site->Nom?></strong></TD>
								<!--<TD style="width:55px;text-align:right"><?=$site->P_Elec?> kW</TD>-->
								<TD style="width:99px;text-align:right"><?=(($site->Ratio['elec']['12m']['KWh/J'])*365 > 0)?(round($site->Ratio['elec']['12m']['KWh/J']*365)).' KWh':''?></TD>
								<TD style="width:91px;text-align:right"><?=($site->Ratio['elec']['12m']['KWh/hab'] > 0)?(round($site->Ratio['elec']['12m']['KWh/hab']*365/30)).' KWh':''?></TD>
								<TD style="width:70px;text-align:right"><?=($site->Ratio['elec']['12m']['KWh/m²'] > 0)?(round($site->Ratio['elec']['12m']['KWh/m²']*365/30)).' KWh':''?></TD>
								<!--<TD style="width:60px;text-align:right"><?//=$site->Ratio['elec']['30j']['TC']?> %</TD>-->
								<!--<TD style="width:45px;text-align:right"><?=(isset($site->P_Eau))?$site->P_Eau:''?> m3</TD>-->
								<TD style="width:81px;text-align:right"><?=($site->Ratio['eau']['30j']['L/hab/J'] > 0)?(round($site->Ratio['eau']['30j']['L/hab/J'])).' L':'';?></TD>
								<!--<TD style="width:60px;text-align:right"><?//=$site->Ratio['eau']['30j']['TC']?> %</TD>-->
								<TD style="width:25px;text-align:center"><?php if ((isset($site->Alerte[0])) and ($site->Alerte[0])) echo '<img src="'.base_url().'/images/icons/selection/flag_red.png" onmouseover="popup(\''. str_replace("'","&#146;",$site->Alerte[0]) .'\');" onmouseout="kill();"/>'?>
																		 <?php if ((isset($site->Alerte[1])) and ($site->Alerte[1])) echo '<img src="'.base_url().'/images/icons/selection/flag_orange.png" onmouseover="popup(\''. str_replace("'","&#146;",$site->Alerte[1]) .'\');" onmouseout="kill();"/>'?></TD>
								<TD style="width:40px;text-align:center"><?php if ((isset($site->Groupe_elec)) and ($site->Groupe_elec)) echo '<img src="'.base_url().'/images/icons/selection/lightning.png" onmouseover="popup(\'Groupe Electrog&egrave;ne\');" onmouseout="kill();"/>'?>
																		 <?php if ((isset($site->Chaudiere)) and ($site->Chaudiere)) echo '<img src="'.base_url().'/images/icons/selection/flame.png" onmouseover="popup(\'Chaudi&egrave;re\');" onmouseout="kill();"/>'?>
																		 <?php if ((isset($site->Groupe_froid)) and ($site->Groupe_froid)) echo '<img src="'.base_url().'/images/icons/selection/snow_small.png" onmouseover="popup(\'Groupe Froid\');" onmouseout="kill();"/>'?></TD>
								<TD style="width:40px;text-align:center"><?php if ((isset($site->Niveau_sans_piece)) and ($site->Niveau_sans_piece)) echo '<img src="'.base_url().'/images/icons/selection/error.png"  onmouseover="popup(\''. str_replace("'","&#146;",$site->Niveau_sans_piece) .'\');" onmouseout="kill();"/>';?>
																		 <?php if ((isset($site->Niveau_sans_eclairage)) and ($site->Niveau_sans_eclairage)) echo '<img src="'.base_url().'/images/icons/selection/error.png"  onmouseover="popup(\''. str_replace("'","&#146;",$site->Niveau_sans_eclairage).'\');" onmouseout="kill();"/>';?>
							</TR>
							<?php endforeach; ?>
							</TBODY>
						</table>
						</div>
						
						<?=form_close();?>
					</TD>
				</TR>
			</table>
		</TD>
	</TR>
</table>
	
<div class="tabber" id="tabResidenceA">
	<div class="tabbertab">
		<h3>Sur 1 an</h3>
		<table>
			<TR>
				<TD><?=$GraphElec1A?></TD>
			</TR>
			<TR>
				<TD><?=$GraphElec2A?></TD>
			</TR>
			<TR>
				<TD><?=$GraphEauA?></TD>
			</TR>
		</table>
	</div>
	<div class="tabbertab">
		<h3>Sur 1 mois</h3>
		<table>
			<TR>
				<TD><?=$GraphElec1M?></TD>
			</TR>
			<TR>
				<TD><?=$GraphElec2M?></TD>
			</TR>
			<TR>
				<TD><?=$GraphEauM?></TD>
			</TR>
		</table>
	</div>
</div>
<br />
<br />



<?=form_open('consult/ForceCache');?>
<input type="submit" value="Forcer la mise à jour des données !" />
<?=form_close();?>


<script type='text/javascript'>
//Script popup text
var offsetxpoint=7;var offsetypoint=20;var ie=document.all;var ns6=document.getElementById && !document.all;var enabletip=false;

var tipobj= document.getElementById("dek");

function ietruebody(){
  return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
}

function popup(thetext){
  tipobj.innerHTML=thetext;enabletip=true;return false;
}

function positiontip(e){
  if (enabletip){var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20;var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20;var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000;if (rightedge<tipobj.offsetWidth)tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px";else if (curX<leftedge)tipobj.style.left="5px";else tipobj.style.left=curX+offsetxpoint+"px";if (bottomedge<tipobj.offsetHeight)tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px";else tipobj.style.top=curY+offsetypoint+"px";tipobj.style.visibility="visible";}
}

function kill(){
  enabletip=false;tipobj.style.visibility="hidden";tipobj.style.left="-1000px";tipobj.style.backgroundColor='';tipobj.style.width='';
}

document.onmousemove=positiontip;
var ff = null;
//Fin du script popup text
</script>
