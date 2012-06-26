<?php
if(isset($_GET['values']) === FALSE) {
echo 'no data';
      exit;
}

// On récupère les valeurs à afficher sur le graphique dans $_GET['values'] et on les désérialize
$array = json_decode(urldecode($_GET['values']),true);

// On vérifie que les données passées en GET sont correctes
if(is_array($array) === FALSE) {
echo 'no data array';
exit;
}

require_once "Artichow2/ScatterPlot.class.php";

if (!isset($array['width'])) $array['width'] = 800;
if (!isset($array['height'])) $array['height'] = 800;

$graph = new Graph($array['width'], $array['height']);
$graph->shadow->setSize(4);

$graph->title->setFont(new Tuffy(30));
$graph->title->set($array['title']);

$group = new PlotGroup;
//$group->setSpace(3, 3, 0, 0);
$group->setPadding(80, 160,80,60);
$group->grid->setType(Line::DASHED);
$xMax = 0; $xMin = 0;
$yMax = 0; $yMin = 0;


foreach ($array['data'] as $name=>$datas)
{
	foreach ($datas['liste'] as $cc) 
	{
		$x[] = $cc['x']; 
		$y[] = $cc['y'];
		if (isset($cc['label'])) $l[] = $cc['label'];
		else $l[] = '';
		
		$xMax = max($xMax,$cc['x']);
		$yMax = max($yMax,$cc['y']);
		$xMin= min($xMin,$cc['x']);
		$yMin = min($yMin,$cc['y']);
	}
	
	$plot = new ScatterPlot($y, $x);
	
	if (!isset($datas['size'])) $datas['size'] = 40;
	$plot->mark->setSize($datas['size']*2);

	$plot->label->setFont(new Tuffy(15));
	$plot->label->set($l);
	$plot->label->setColor(new White);
	
	$datas['couloured'] = new DarkOrange(40);
	
	if (isset($datas['color']))
	switch($datas['color'])
	{
		case 'red': $datas['couloured'] = new Red(40); break;
		case 'green': $datas['couloured'] = new Green(40); break;
		case 'blue': $datas['couloured'] = new Blue(40); break;
		case 'yellow': $datas['couloured'] = new Yellow(40); break;
		case 'orange': $datas['couloured'] = new Orange(40); break;
	}
	$plot->mark->setFill($datas['couloured']);

	$group->add($plot);
	$group->axis->left->setColor($datas['couloured']);
	$group->axis->left->label->setFont(new Tuffy(13));
	$group->axis->left->title->setFont(new Tuffy(20));
	$group->axis->left->title->set($name);
}
$group->axis->bottom->title->setFont(new Tuffy(13));
$group->axis->bottom->title->set($array['abscisse']);
$group->axis->bottom->setLabelInterval(($xMax-$xMin)/20);
$group->axis->bottom->setLabelPrecision(0);
$group->axis->bottom->label->setFont(new Tuffy(13));
$group->axis->bottom->hideTicks();
$group->grid->setInterval(1,($xMax-$xMin)/20);

$graph->add($group);
$graph->draw();
   
?>
