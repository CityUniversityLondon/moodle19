<!--imoodel[en]: Delistavrou Constantinos' [MAI 09/07] UOM Dep/t of Applied Informatics MSc thesis (c) 2008.-->
<!--imoodel[el]: Διπλωματική εργασία του Δελησταύρου Κωνσταντίνου [MAI 09/07] για το ΠΜΣ του Τμ. Εφαρμοσμένης Πληροφορικής του Πανεπιστημίου Μακεδονίας (c) 2008.-->

<?php
  
    //imoodel[en]: System definitions inclusion.
	//imoodel[el]: Συμπερίληψη των ορισμών συστήματος.
	require_once("../../config.php");
    
	//imoodel[en]: Data Definition Language library inclusion.
	//imoodel[el]: Συμπερίληψη της βιβλιοθήκης της Γλώσσας Ορισμού Δεδομένων.
	require_once("$CFG->libdir/ddllib.php");

    //imoodel[en]: Default values assignment.
    //imoodel[el]: Ανάθεση αρχικών τιμών.
	$result = 1;
	$oldversion = 00000000;
	
    if ($result && $oldversion < 20080724) {

        //imoodel[en]: Definition of imoodeldata table object.
        //imoodel[el]: Ορισμός του αντικειμένου τύπου πίνακα, imoodeldata.        
	    $table = new XMLDBTable('imoodeldata');

        //imoodel[en]: Adding fields to table imoodeldata.
        //imoodel[el]: Προσθήκη πεδίων στο αντικείμενο πίνακα imoodeldata.
		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('usr', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
		$table->addFieldInfo('url', XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null);
		$table->addFieldInfo('itm', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
		$table->addFieldInfo('x', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
		$table->addFieldInfo('y', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

        //imoodel[en]: Adding primary key to table imoodeldata.
        //imoodel[el]: Προσθήκη πρωτεύοντος κλειδιού στο αντικείμενο πίνακα imoodeldata.
		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        //imoodel[en]: imoodeldata table creation.
        //imoodel[el]: Δημιουργία του πίνακα imoodeldata.
		$result = $result && create_table($table);
    }
?>