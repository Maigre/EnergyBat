

<style>
  #dek
  {
  font-family: Lucida Grande, Verdana, Sans-serif;
  font-size: 10px; font-style: normal;
  line-height:100%;
  font-weight: normal;
  padding: 5px;
  border:1px solid #5C443A;
  background-color: white; 
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
				<THEAD class="vertical">
					<TR>
						<TH>
							<h3><br />Importation des données de consommation</h3>
						</TH>
					</TR>
					<TR>
						<TH>
							<h3 style="text-align:center"><br />Facture Moyenne Tension (MT)</h3>
						</TH>
						<TD>
							<!-- Print Form -->
							<?php echo form_open_multipart('upload/do_upload/Conso_MT');?>
							Sélectionnez le fichier à importer  (au format xls):
							<input type="file" name="userfile" size="20" />

							<br /><br />

							<input type="submit" value="O.K" />

							</form>
						</TD>
					</TR>
					<TR>
						<TH>
							<h3 style="text-align:center"><br />Facture Basse Tension (BT)</h3>
						</TH>
						<TD>
							<!-- Print Form -->
							<?php echo form_open_multipart('upload/do_upload/Conso_BT');?>
							Sélectionnez le fichier à importer (au format xls):
							<input type="file" name="userfile" size="20" />

							<br /><br />

							<input type="submit" value="O.K" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							L'importation dure quelques minutes...

							</form>
						</TD>
					</TR>
					<TR>
						<TH style="vertical-align:top;">
							<h3 style="text-align:center" ><br />Historique des importations</h3>
						</TH>
						<TD>
							<center>
							<table class="fency fencyLine">
								<tr>
									<th>Nom du fichier</th><th>Date d'importation</th>
								</tr>
								<?php foreach($uploads as $upload): ?>
								<tr>
									<td><?=$upload['nom_fichier']?></td><td><?=$upload['date_creation']?></td>
								</tr>
								<?php endforeach; ?>
							</table> 
							</center>
						</TD>
					</TR>
				</THEAD>
			</table>
		</TD>
	</TR>
</table>
	

<br />
<br />


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
