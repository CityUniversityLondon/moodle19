<?php

    //imoodel[en]: Delistavrou Constantinos' [MAI 09/07] UOM Dep/t of Applied Informatics MSc thesis (c) 2008.
    //imoodel[el]: Διπλωματική εργασία του Δελησταύρου Κωνσταντίνου [MAI 09/07] για το ΠΜΣ του Τμ. Εφαρμοσμένης Πληροφορικής του Πανεπιστημίου Μακεδονίας (c) 2008.

    //imoodel[en]: System definitions inclusion.
	//imoodel[el]: Συμπερίληψη των ορισμών συστήματος.
	require_once("../../config.php");
	
	//imoodel[en]: Data Manipulation Language library inclusion.
	//imoodel[el]: Συμπερίληψη της βιβλιοθήκης της Γλώσσας Διαχείρισης Δεδομένων.
	require_once("$CFG->libdir/dmllib.php");

	//imoodel[en]: Check if all needed values were passed to this module.
	//imoodel[el]: Έλεγχος για το αν πέρασαν στο παρόν άρθρωμα οι απαραίτητες τιμές.
	if(    isset($_GET['imdurl']) && isset($_GET['imdusr'])
        && isset($_GET['imditm']) && isset($_GET['imdX']) 
	    && isset($_GET['imdY'])                             ){

        //imoodel[en]: Assign the HTTP parameter data to local variables.
        //imoodel[el]: Ανάθεση των τιμών των HTTP παραμέτρων σε τοπικές μεταβλητές.
	    $url = $_GET['imdurl'];
	    $usr = $_GET['imdusr'];
	    $itm = $_GET['imditm'];
	    $X   = $_GET['imdX'];
	    $Y   = $_GET['imdY'];	

        //imoodel[en]: Check if a record for this user, URL, item combination exists.
        //imoodel[el]: Έλεγχος για το αν υπάρχει εγγραφή για το συγκεκριμένο συνδυασμό χρήστη, URL, αντικειμένου της ιστοσελίδας.
		if ( record_exists('imoodeldata', 'usr', $usr, 'url', $url, 'itm', $itm) ) {

            //imoodel[en]: If yes, then an update must be performed. So, the according record is first retrieved...
            //imoodel[el]: Αν ναι, τότε πρέπει να γίνει απλά ενημέρωση των τιμών. Έτσι, ανακτάται η αντίστοιχη εγγραφή...
			$rec = get_record('imoodeldata', 'usr', $usr, 'url', $url, 'itm', $itm, $fields='*');

            //imoodel[en]: ...values are changed to the record object...
            //imoodel[el]: ...αντικαθιστούνται οι τιμές στο αντικείμενο της εγγραφής...
			$rec->x = $X;
			$rec->y = $Y;

            //imoodel[en]: ...and the imoodeldata table record is updated.
            //imoodel[el]: ...και ενημερώνεται η εγγραφή του πίνακα imoodeldata.
			if (!update_record('imoodeldata', $rec)) {
            
                //imoodel[en]: On error and if debug messages are displayed, a message apears at the bottom of the webpage.
                //imoodel[el]: Αν κάτι δεν πάει καλά και έχει επιλεγεί η εμφάνιση μηνυμάτων αποσφαλμάτωσης, εμφανίζεται ένα μήνυμα στο κάτω μέρος της ιστοσελίδας.
                if (debugging()) {
                    echo "imoodel: Δεν είναι δυνατή η ενημέρωση των ιδιοτήτων του αντικειμένου στη βάση δεδομένων!";
                    echo "imoodel: Could not update item's properties to database!";
                }
			}
		}
		else {
		
            //imoodel[en]: If no, then an new record must be inserted. So, a new record object is defined...
            //imoodel[el]: Αν όχι, τότε πρέπει να δημιουργηθεί μια νέα εγγραφή. Έτσι, ορίζεται ένα νέο αντικείμενο τύπου εγγραφής...
			$rec = new stdClass();

            //imoodel[en]: ...values are set to the record object...
            //imoodel[el]: ...αποδίδονται τιμές στο αντικείμενο της εγγραφής...
			$rec->url = $url;
			$rec->usr = $usr;
			$rec->itm = $itm;
			$rec->x = $X;
			$rec->y = $Y;
            
			//imoodel[en]: ...and the new imoodeldata table record is inserted.
            //imoodel[el]: ...και εισάγεται μια νέα εγγραφή στον πίνακα imoodeldata.
			if (!insert_record('imoodeldata', $rec, $returnid=true, $primarykey='id')) {
                //imoodel[en]: On error and if debug messages are displayed, a message apears at the bottom of the webpage.
                //imoodel[el]: Αν κάτι δεν πάει καλά και έχει επιλεγεί η εμφάνιση μηνυμάτων αποσφαλμάτωσης, εμφανίζεται ένα μήνυμα στο κάτω μέρος της ιστοσελίδας.
                if (debugging()) {            
                    echo "imoodel: Δεν είναι δυνατή η αποθήκευση των ιδιοτήτων του αντικειμένου στη βάση δεδομένων!";
                    echo "imoodel: Could not save item's properties to database!";
                }
			}
		}
	}
	else {
        //imoodel[en]: If all needed data were not present in HTTP header, show message at bottom of webpage.
        //imoodel[el]: Αν όλα τα απαιτούμενα δεδομένα δεν υπήρχαν στην HTTP κεφαλίδα, εμφάνισε μήνυμα στο κάτω μέρος της ιστοσελίδας.
        if (debugging()) {
            echo "imoodel: Ανεπαρκή δεδομένα για την αποθήκευση της θέσης του αντικειμένου!";
            echo "imoodel: Inadaquate data for saving item's position!";
        }
	}
?>