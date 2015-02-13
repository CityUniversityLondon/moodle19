<?php

    //imoodel[en]: Delistavrou Constantinos' [MAI 09/07] UOM Dep/t of Applied Informatics MSc thesis (c) 2008.
    //imoodel[el]: Διπλωματική εργασία του Δελησταύρου Κωνσταντίνου [MAI 09/07] για το ΠΜΣ του Τμ. Εφαρμοσμένης Πληροφορικής του Πανεπιστημίου Μακεδονίας (c) 2008.

    //imoodel[en]: System definitions inclusion.
	//imoodel[el]: Συμπερίληψη των ορισμών συστήματος.
	require_once("../../config.php");

	//imoodel[en]: Data Manipulation Language library inclusion.
	//imoodel[el]: Συμπερίληψη της βιβλιοθήκης της Γλώσσας Διαχείρισης Δεδομένων.
	require_once("$CFG->libdir/dmllib.php");
    
    //imoodel[en]: Field and imoodel table names to variables.
	//imoodel[el]: Τα ονόματα των πεδίων και του πίνακα του imoodel σε μεταβλητές.
	$fields = "usr, url, itm, x, y";
	$table  = "imoodeldata";
    
	//imoodel[en]: Check if needed values were passed to this module.
	//imoodel[el]: Έλεγχος για το αν πέρασαν στο παρόν άρθρωμα οι απαραίτητες τιμές.
	if ( isset($_GET['imdurl']) && isset($_GET['imdusr']) ){

        //imoodel[en]: Assign the HTTP parameter data to local variables.
        //imoodel[el]: Ανάθεση των τιμών των HTTP παραμέτρων σε τοπικές μεταβλητές.
	    $url = $_GET['imdurl'];
	    $usr = $_GET['imdusr'];
		
        //imoodel[en]: SQL clause creation for selecting an item's coordinates from imoodeldata table.
        //imoodel[el]: Δημιουργία της SQL πρότασης για την επιλογή των συντεταγμένων ενός αντικειμένου από τον πίνακα imoodeldata.
		$sql = "SELECT itm, x, y FROM mdl_imoodeldata WHERE usr='$usr' AND url='$url'";

        //imoodel[en]: Getting a recordset. If it is not empty...
        //imoodel[el]: Λήψη συνόλου εγγραφών. Αν δεν είναι κενό...
		if ( $rs = get_recordset_sql($sql, $limitfrom=null, $limitnum=null) ) {

            //imoodel[en]: ...read each reocord in it and print its field values. These values will be used by this module's invoker (AJAX).
            //imoodel[el]: ...διάβασε κάθε του εγγραφή και τύπωσε τις τιμές των πεδίων του. Οι τιμές αυτές θα χρησιμοποιηθούν από το άρθρωμα που καλεί το παρόν (AJAX).
			while(!$rs->EOF){
				echo $rs->fields['itm'].',';
				echo $rs->fields['x'].',';
				echo $rs->fields['y'].'.';
				$rs->MoveNext();
			}
            
            //imoodel[en]: Closing recordset.
            //imoodel[el]: Κλείσιμο του συνόλου εγγραφών.
			$rs->close();
		}
	}
	else {
        //imoodel[en]: If all needed data were not present in HTTP header, show message at bottom of webpage.
        //imoodel[el]: Αν όλα τα απαιτούμενα δεδομένα δεν υπήρχαν στην HTTP κεφαλίδα, εμφάνισε μήνυμα στο κάτω μέρος της ιστοσελίδας.
        if (debugging()) {
            echo "imoodel: Ανεπαρκή δεδομένα για την ανάγνωση της θέσης του αντικειμένου!";
            echo "imoodel: Inadaquate data for reading item's position!";
        }
	}
?>