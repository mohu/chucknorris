<?php
require_once '../../includes/redbean/rb.php';
require_once '../../includes/common/dbconnector.php';

$id = (isset($_GET['id'])) ? $_GET['id'] : null;

if ($id) {
	$filename = R::getCell( 'SELECT file
        FROM backup
        WHERE id = '. $id
       );
}

$filesize = filesize('../../' . $filename);

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // Required for certain browsers
header('Content-type: application/sql');
header('Content-Disposition: attachment; filename= ' . basename($filename));
header("Content-Transfer-Encoding: binary");
header('Content-length:' . $filesize);
readfile('../../' . $filename);
exit;