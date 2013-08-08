<?php
header('Content-Type: text/html; charset=utf-8');
$dir = dirname(__FILE__) . '/classes/';
require_once('config/globales.php');
require_once('config/config.php');
define('DEBUG', TRUE);
define('LOG_DIR', dirname(__FILE__) . "/log");
$log = new KLogger ( LOG_DIR , KLogger::DEBUG );
if (defined('DEBUG')) {
  ini_set('display_errors', '1');
  ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
  ini_set('html_errors', false);
  ini_set('implicit_flush', true);
  ini_set('max_execution_time', 0);
}else{
  error_reporting( 0 );
}

$TIME_START = getmicrotime();
$options = array();
if(defined('DEBUG')){
	$options = array_merge($options, array('printDataset' => TRUE, 'country_name' => 'Colombia'));
}
$facade = new PosicionLogica($log, $options);




if($_GET
	&& !empty($_GET['lon'])
	&& !empty($_GET['lat'])) {
	$facade->setWktPoint($_GET['lon'],$_GET['lat']);
}
$output = $facade->execute();
?><html>
<head>
	<title></title>
	<style type="text/css">
	#container{
		margin: 0 auto;

	}
	.message{
		border: 1px solid #red;
		background-color: #ffcc00;
		color: black;

	}
	th.data {
    	background-color: #E6E6CC;
    	color: #000000;
    	font-family: arial,tahoma,verdana,helvetica,sans-serif,serif;
    	font-size: smaller;
	}
	td.data1 {
	    background-color: #F3F3E9;
	    color: #000000;
	    font-family: arial,tahoma,verdana,helvetica,sans-serif,serif;
	    font-size: smaller;
	    text-align: left;
	}
	th{
		font-weight: bold;
	}
	td.data2, th {
	    background-color: #E6E6CC;
	    color: #000000;
	    font-family: arial,tahoma,verdana,helvetica,sans-serif,serif;
	    font-size: smaller;
	    text-align: left;
	}
	footer .footertext{
		font-family: arial,tahoma,verdana,helvetica,sans-serif,serif;
    	font-size: smaller;
	}
	</style>
</head>
<body>
	<form action="#" method="GET">
		<input type="text" name="lat" placeholder="latitude" />
		<input type="text" name="lon" placeholder="longitude" />
		<input type="submit" value="enviar" />
	</form>
	<div id="container">
		<span class="message"><?php if(isset($output)) { echo $output; } ?></span>
	<span class="message"><?php echo "Tiempo de ejecucion: " . round(getmicrotime() - $TIME_START, 2) . " segundos"; ?></span>
	</div>
	<?php $i=0; if(isset($facade) && count($facade->recordset) > 0):?>
	<h2>Query results</h2>
	<table>
		<tbody>
			<tr>
				<th>place_id</th>
				<th>osm_id</th>
				<th>class</th>
				<th>type</th>
				<th>name</th>
				<th>parent_place_id</th>
				<th>distance</th>
			</tr>
		</tbody>
		<?php foreach ($facade->recordset as $row): ?>
			<tr>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['place_id'] ?></div></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['osm_id'] ?></div></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['class'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['type'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['name'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['parent_place_id'] ?></div></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['distancia'] ?></div></td>
			</tr>
		<?php $i++; endforeach; ?>
	</table>
	<?php endif; ?>
	<?php $i=0; if(isset($facade) && count($facade->resultsetLocations) > 0):?>
	<table>
		<tbody>
			<tr>
				<th>place_id</th>
				<th>osm_id</th>
				<th>nombrevia</th>
				<th>tipoparent</th>
				<th>nombreparent</th>
				<th>tipoparent2</th>
				<th>nombreparent2</th>
				<th>isin</th>
			</tr>
		</tbody>
		<tr>
		<?php foreach ($facade->resultsetLocations as $row): ?>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row ?></div></td>
		<?php $i++; endforeach; ?>
		</tr>
	</table>
	<?php endif; ?>
	<?php $i=0; if(isset($facade) && count($facade->optional_recordsets) > 0):?>
	<h2>Optional data</h2>
	<?php foreach ($facade->optional_recordsets as $data): ?>
		<table width="600">
		<tbody>
			<tr>
				<th>place_id</th>
				<th>osm_id</th>
				<th>class</th>
				<th>type</th>
				<th>name</th>
				<th>parent_place_id</th>
				<th>distance</th>
			</tr>
		</tbody>
		<?php foreach ($data as $row): ?>
		<tr>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['place_id'] ?></div></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['osm_id'] ?></div></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['class'] ?></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['type'] ?></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['name'] ?></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['parent_place_id'] ?></div></td>
			<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['distancia'] ?></div></td>
		</tr>
		<?php $i++; endforeach; ?>
		</table>
	<?php $i++; endforeach; ?>
	<?php endif; ?>
	<?php $i=0; if(isset($facade) && count($facade->city_recordset) > 0):?>
	<h2>Query results</h2>
	<table>
		<tbody>
			<tr>
				<th>place_id</th>
				<th>osm_id</th>
				<th>nombrevia</th>
				<th>tipoparent</th>
				<th>nombreparent</th>
				<th>tipoparent2</th>
				<th>isin</th>
			</tr>
		</tbody>
		<?php foreach ($facade->city_recordset as $row): ?>
			<tr>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['place_id'] ?></div></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['osm_id'] ?></div></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['nombrevia'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['tipoparent'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['nombreparent'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><?php echo $row['tipoparent2'] ?></td>
				<td class="data<?php echo ($i%2 == 0)? "1" : "2"; ?>" style="white-space:nowrap;"><div style="text-align: right"><?php echo $row['isin'] ?></div></td>
			</tr>
		<?php $i++; endforeach; ?>

	</table>
	<?php endif; ?>
</body>
</html>
<footer>
	<div class="footertext">Version: <?php echo $facade::$version; ?></div>
</footer>