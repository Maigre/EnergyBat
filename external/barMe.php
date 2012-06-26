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

require_once "Artichow2/BarPlot.class.php";

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
//$xMax = 0; $xMin = 0;
//$yMax = 0; $yMin = 0;

if ($array['legend'])
{
	$group->legend->setPosition(0.87, 0.5);
	$group->legend->setAlign(Legend::LEFT);
	$group->legend->setTextFont(new Tuffy(17));
	$group->legend->shadow->setSize(2);
	$group->legend->shadow->setPosition(Shadow::RIGHT_BOTTOM);
}

foreach ($array['data'] as $name=>$datas)
{
	$y = array();
	$x = array();
	$l = '';
	foreach ($datas['liste'] as $cc) 
	{
		$x[] = $cc['x']; 
		$y[] = $cc['y'];
		if (isset($cc['label'])) $l = $cc['label'];
	}
	
	$plot = new BarPlot($y);
	
	$datas['couloured'] = new DarkOrange(20);
	
	if (isset($datas['color']))
	switch($datas['color'])
	{
		case 'red': $datas['couloured'] = new Red(40); break;
		case 'green': $datas['couloured'] = new Green(); break;
		case 'blue': $datas['couloured'] = new LightBlue(40); break;
		case 'yellow': $datas['couloured'] = new Yellow(); break;
		case 'orange': $datas['couloured'] = new Orange(); break;
	}

	$plot->setBarColor($datas['couloured']);
	$plot->setSize(1, 0.96);
	$plot->setCenter(0.5, 0.52);

	$group->add($plot);
	$group->axis->left->setTitlePosition(0.9);
	$group->axis->left->label->setFont(new Tuffy(13));
	$group->axis->left->title->setFont(new Tuffy(20));
	$group->axis->left->setColor($datas['couloured']);
	$group->axis->left->title->set($name);
	
	
	if ($array['legend']) $group->legend->add($plot, $l, Legend::BACKGROUND);
}
if ($array['abscisse']) $group->axis->bottom->title->set($array['abscisse']);
$group->axis->bottom->label->setFont(new Tuffy(13));
$group->axis->bottom->setLabelText($x);


$graph->add($group);
$graph->draw();
?>
