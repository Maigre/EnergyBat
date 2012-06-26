<title><?=$title;?></title>

<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">

<link href="<?=base_url()?>/application/css/main.css" rel="stylesheet" type="text/css" />
<link href="<?=base_url()?>/application/css/tabber.css" rel="stylesheet" type="text/css" MEDIA="screen">
<link href="<?=base_url()?>/application/css/tabber-print.css" rel="stylesheet" type="text/css" MEDIA="print">

<script type="text/javascript" src="<?=base_url()?>/application/js/cookMe.js"></script>
<script type="text/javascript" src="<?=base_url()?>/application/js/tabber-minimized.js"></script>
<script type="text/javascript" src="<?=base_url()?>/application/js/gwikCI.js"></script>

<!--  GRAPH FLOT-->
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="<?=base_url()?>external/Flot/JS/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?=base_url()?>external/Flot/JS/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?=base_url()?>external/Flot/JS/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?=base_url()?>external/Flot/JS/jquery.flot.navigate.min.js"></script>

<?=(isset($ToDo))?$ToDo:'';?>