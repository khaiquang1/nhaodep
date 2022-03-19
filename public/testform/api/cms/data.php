<?php
if(isset($_POST['clearall'])){
	if(is_file('../data.txt')){
		@unlink('../data.txt');
	}
	echo json_encode(array(
		'success' => TRUE,
	));
	die;
}
$filename = '../data.txt';
ini_set ('memory_limit', filesize ($filename) + 4000000);
$rows = @file_get_contents ($filename);
$rows = json_decode($rows, TRUE);
$data['rows'] = array();
//$count=0;
foreach($rows as $row){
	$data['rows'][] = $row;
}
$data['totalrecords'] = count($data['rows']);
echo json_encode($data);