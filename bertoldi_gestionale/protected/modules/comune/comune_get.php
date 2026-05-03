<?php
ini_set("allow_url_fopen", "on");

$conn = mysqli_connect('217.72.102.155', 'dbadmin', 'AccediDb2011!', 'resolve_dev_odc') or die ('Error connecting to mysql');

$return_arr = array();

/* If connection to database, run sql statement. */
if ($conn)
{
	$fetch = mysqli_query($conn, "SELECT * FROM NazioneRegioneComune where NazioneId=1 and Comune like '%" . mysqli_real_escape_string($conn, $_REQUEST['term']) . "%'"); 

	/* Retrieve and store in array the results of the query.*/
	while ($row = mysqli_fetch_array($fetch, MYSQLI_ASSOC)) {
		$row_array['id'] = $row['ComuneId'];
		$row_array['value'] = $row['Comune'];
		$row_array['provincia'] = $row['Provincia'];

        array_push($return_arr,$row_array);
    }
}

/* Free connection resources. */
mysqli_close($conn);

/* Toss back results as json encoded array. */
echo json_encode($return_arr);
        
        

?>
