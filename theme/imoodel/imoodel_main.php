<!--imoodel[en]: Delistavrou Constantinos' [MAI 09/07] UOM Dep/t of Applied Informatics MSc thesis (c) 2008.-->
<!--imoodel[el]: Διπλωματική εργασία του Δελησταύρου Κωνσταντίνου [MAI 09/07] για το ΠΜΣ του Τμ. Εφαρμοσμένης Πληροφορικής του Πανεπιστημίου Μακεδονίας (c) 2008.-->

<!--imoodel[en]: This is the main code of the imoodel theme AJAX functionality.
                       It is invoked by header.html and invokes asynchronously through AJAX other modules,
                       for reading and writting data to Moodle's database.-->
<!--imoodel[el]: Αυτός είναι ο κυρίως κώδικας της AJAX λειτουργικότητας του θέματος imoodel.
                      Καλείται από το header.html και καλεί ασύγχρονα μέσω AJAX άλλα αρθρώματα,
                      για την ανάγνωση και εγγραφή δεδομένων στη βάση δεδομένων του Moodle.-->
                      
<?php

    //imoodel[en]: System definitions inclusion.
	//imoodel[el]: Συμπερίληψη των ορισμών συστήματος.
    require_once("$CFG->dirroot/config.php");
?>

<!--imoodel[en]: Moodle installation's Yahoo! User Interface library inclusion.-->
<!--imoodel[el]: Συμπερίληψη της βιβλιοθήκης Yahoo! User Interface της Moodle εγκατάστασης.-->
<script type="text/javascript" src="<? echo $CFG->wwwroot; ?>/lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<? echo $CFG->wwwroot; ?>/lib/yui/dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="<? echo $CFG->wwwroot; ?>/lib/yui/yahoo/yahoo.js"></script>
<script type="text/javascript" src="<? echo $CFG->wwwroot; ?>/lib/yui/event/event.js"></script>
<script type="text/javascript" src="<? echo $CFG->wwwroot; ?>/lib/yui/connection/connection.js"></script>

<!--imoodel[en]: In the following script event handling routines and their relevant imoodel theme logic is defined.-->
<!--imoodel[el]: Στο επόμενο τμήμα κώδικα (script) ορίζονται οι ρουτίνες χειρισμού γεγονότων και η σχετική με αυτούς λογική του θέματος imoodel.-->
<script type="text/javascript">
<!--
    //imoodel[en]: .
    //imoodel[el]: .
    
    //imoodel[en]: Objects of imoodelitem class will get by the imoodel theme the capability to be moved.
    //imoodel[el]: Τα αντικείμενα που θα ανήκουν στην κλάση imoodelitem θα αποκτήσουν τη δυνατότητα μετακίνησης από το θέμα imoodel.
	var imdItemClass = 'imoodelitem';
	
    //imoodel[en]: Page load event handling routine.
    //imoodel[el]: Ρουτίνα χειρισμού του γεγονότος φόρτωση σελίδας.
	function fnLoadHandler(oEvent) {
    
        //imoodel[en]: Items of class sideblock are being chosen as moovable, i.e. the menu panels. Their names are collected to an array...
        //imoodel[el]: Ως ικανά να μετακινηθούν επιλέγονται τα αντικείμενα της κλάσης sideblock, δηλαδή τα πλαίσια των μενού. Τα ονόματά τους συλλέγονται σε ένα διάνυσμα...
		var sideblockitemsarray = YAHOO.util.Dom.getElementsByClassName('sideblock');

        //imoodel[en]: ...and each item that has its name in this array, becomes a member of the imoodelitem class as well.
        //imoodel[el]: ...και κάθε αντικείμενο του οποίου το όνομα βρίσκεται σ' αυτό το διάνυσμα, γίνεται επιπλέον μέλος και της κλάσης imoodelitem.
		YAHOO.util.Dom.addClass (sideblockitemsarray, imdItemClass); 
	
        //imoodel[en]: Definition of arrays to hold the move (green bullet) and save (red bullet), visible interface object handles.
        //imoodel[el]: Δημιουργία διανυσμάτων για την αποθήκευση των λαβών των αντικειμένων της επιφάνειας εργασίας του φυλλομετρητή, για την μετακίνηση (πράσινη κουκίδα) και την αποθήκευση (κόκκινη κουκίδα).
		var mv = new Array();
		var sv = new Array();

        //imoodel[en]: For each item of class imoodelitem...
        //imoodel[el]: Για κάθε αντικείμενο της κλάσης imoodelitem...
		for (var i=0; i<sideblockitemsarray.length; i++){
			
            //imoodel[en]: ...(x is an alias for the current imoodelitem object)...
            //imoodel[el]: ...(το x είναι ένα ψευδόνυμο για το τρέχον αντικείμενο imoodelitem)...
			var x = sideblockitemsarray[i];
			
            //imoodel[en]: ...altering of its HTML code to give to each object two handling buttons.
            //imoodel[el]: ...τροποποίηση του HTML κώδικά του για να αποκτήσει κάθε αντικείμενο δύο κουμπιά χειρισμού.
			var str_m = "<span class='imoodel mv' id='mv"+i+"' style='position:relative; top:-5px; cursor:move;      font-size: 70%;'><img src='<?php echo $CFG->wwwroot.'/theme/'.current_theme().'/images/' ?>move.bmp' alt='μετακίνηση - move' /></span> ";
			var str_s = "<span class='imoodel sv' id='sv"+i+"' style='position:relative; top:-5px; cursor:crosshair; font-size: 70%;'><img src='<?php echo $CFG->wwwroot.'/theme/'.current_theme().'/images/' ?>save.bmp' alt='αποθήκευση - save' /></span> ";
			x.innerHTML = str_m + str_s + x.innerHTML;

            //imoodel[en]: Give drag'n'drop ability through YUI to the handling image (or alternate text) of the object.
            //imoodel[el]: Απόδοση δυνατότητας "σύρε κι άσε" στο αντικείμενο μέσω της YUI στην κουκίδα χειρισμού (ή το εναλλακτικό της κείμενο).
			var temp;
			temp = new YAHOO.util.DD(x.id);
			mv[i] = temp;
			mv[i].setOuterHandleElId("mv"+i);
			sv[i] = "sv"+i;
		}
		
        //imoodel[en]: Load saved item coordinates.
        //imoodel[el]: Φόρτωση αποθηκευμένων συντεταγμένων αντικειμένων.
		imdLoadItemProperties();

        //imoodel[en]: Activate event listener for click event on save buttons.
        //imoodel[el]: Ενεργοποίηση του "ακροατή" γεγονότων για το γεγονός "κλικ" σε κάποιο από τα κουμπιά αποθήκευσης.
		YAHOO.util.Event.on(sv, "click", fnSaveHandler, "User chose to save item properties.");
	}
			
	//imoodel[en]: Click on save button event handling routine.
    //imoodel[el]: Ρουτίνα χειρισμού του γεγονότος "κλικ" σε κουμπί αποθήκευσης.
	function fnSaveHandler(oEvent, message) {
	
        //imoodel[en]: Just call the saving item positions routine.
        //imoodel[el]: Απλά κλήση της ρουτίνας αποθήκευσης της θέσης αντικειμένων.
		imdSaveItemProperties(this.id);
	}

    //imoodel[en]: Definition of the saving item positions routine.
    //imoodel[el]: Ορισμός της ρουτίνας αποθήκευσης της θέσης αντικειμένων.
    function imdSaveItemProperties(itm) {

        //imoodel[en]: Using YUI to get item's position.
        //imoodel[el]: Χρήση της YUI για την απόκτηση της θέσης του αντικειμένου.
		var X = parseInt(YAHOO.util.Dom.getX(itm));
		var Y = parseInt(YAHOO.util.Dom.getY(itm));
        
        //imoodel[en]: Creation of the URL for calling asynchronously the PHP module for saving to database.
        //imoodel[el]: Δημιουργία του URL ασύγχρονης κλήσης του αρθρώματος PHP για την αποθήκευση στη βάση δεδομένων.
        
        //imoodel[en]: Concatenating the module's absolute web server path with the URL of the current page and user name.
        //imoodel[el]: Παράθεση της απόλυτης διαδρομής του αρθρώματος στο διακομιστή με το URL της τρέχουσας ιστοσελίδας και το όνομα χρήστη.
        var sUrl1 = "<? echo $CFG->wwwroot; ?>/theme/imoodel/imoodel_save_item_properties.php";
		var sUrl2 = "?imdurl=<?php echo $_SERVER['PHP_SELF']; ?>";
		var sUrl3 = "&imdusr=<?php echo $USER->username; ?>";

        //imoodel[en]: Finding the parent of the clicked item to get its id. Append it with X and Y to the calling URL.
        //imoodel[el]: Εύρεση του γονέα του αντικειμένου που έγινε "κλικ" για να ληφθεί η τιμή id του. Επέκταση του URL κλήσης με το id και τα Χ και Υ.
		var node = document.getElementById(itm);
		node = node.parentNode;
		itm = node.id;		
		var sUrl4 = "&imditm="+itm;
		var sUrl5 = "&imdX="+X;
		var sUrl6 = "&imdY="+Y;

		sUrl = sUrl1 + sUrl2 + sUrl3 + sUrl4 + sUrl5 + sUrl6;
        
        //imoodel[en]: Alias for the division into which debug messages will be written.
        //imoodel[el]: Ψευδόνυμο για το τμήμα της ιστοσελίδας στο οποίο θα γραφούν μηνύματα αποσφαλμάτωσης.
        var div = document.getElementById('imddebug');

        //imoodel[en]: Successful asynchronous call handling routine (showing debug messages).
        //imoodel[el]: Ρουτίνα χειρισμού επιτυχούς ασύγχρονης κλήσης (εμφάνισης μηνυμάτων αποσφαλμάτωσης).
        var handleSuccess = function(o){
        
            //imoodel[en]: If the return object has text.
            //imoodel[el]: Αν το αντικείμενο επιστροφής έχει κείμενο.
            if(o.responseText !== undefined){
                div.innerHTML = "<li>Transaction id: " + o.tId + "</li>";
                div.innerHTML += "<li>HTTP status: " + o.status + "</li>";
                div.innerHTML += "<li>Status code message: " + o.statusText + "</li>";
                div.innerHTML += "<li>HTTP headers: <ul>" + o.getAllResponseHeaders + "</ul></li>";
                div.innerHTML += "<li>Server response: " + o.responseText + "</li>";
                div.innerHTML += "<li>Argument object: Object ( [foo] => " + o.argument.foo +
                                 " [bar] => " + o.argument.bar +" )</li>";
            }
        }

        //imoodel[en]: Unsuccessful asynchronous call handling routine (showing debug messages)
        //imoodel[el]: Ρουτίνα χειρισμού μη επιτυχούς ασύγχρονης κλήσης (εμφάνισης μηνυμάτων αποσφαλμάτωσης).
        var handleFailure = function(o){
        
            //imoodel[en]: If the return object has text.
            //imoodel[el]: Αν το αντικείμενο επιστροφής έχει κείμενο.
            if(o.responseText !== undefined){
                div.innerHTML = "<li>Transaction id: " + o.tId + "</li>";
                div.innerHTML += "<li>HTTP status: " + o.status + "</li>";
                div.innerHTML += "<li>Status code message: " + o.statusText + "</li>";
            }
        }

        //imoodel[en]: Asynchronous call return object definition.
        //imoodel[el]: Ορισμός του αντικειμένου επιστροφής της ασύγχρονης κλήσης.
        var callback =
        {
          success: handleSuccess,
          failure: handleFailure,
          argument: { foo:"foo", bar:"bar" }
        };
        
        //imoodel[en]: Asynchronous call of saving module using YUI.
        //imoodel[el]: Ασύγχρονη κλήση του αρθρώματος αποθήκευσης με χρήση της YUI.
        var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);   
    }	
	
    //imoodel[en]: Current webpage event listening handling routine.
    //imoodel[el]: Ρουτίνα χειρισμού της ακρόασης γεγονότων για την τρέχουσα ιστοσελίδα.
	function imdEventListenInit() {

        //imoodel[en]: Activate event listener for current webpage's load event on save buttons.
        //imoodel[el]: Ενεργοποίηση του "ακροατή" γεγονότων για το γεγονός "φόρτωση" της τρέχουσας ιστοσελίδας.
		YAHOO.util.Event.on(window, "load", fnLoadHandler);
	}	
	
    //imoodel[en]: Load saved item coordinates handling routine.
    //imoodel[el]: Ρουτίνα χειρισμού της φόρτωση αποθηκευμένων συντεταγμένων αντικειμένων.
	function imdLoadItemProperties() {
		
        //imoodel[en]: Alias for the division into which debug messages will be written.
        //imoodel[el]: Ψευδόνυμο για το τμήμα της ιστοσελίδας στο οποίο θα γραφούν μηνύματα αποσφαλμάτωσης.        
		var div = document.getElementById('imddebug');

        //imoodel[en]: Successful asynchronous call handling routine.
        //imoodel[el]: Ρουτίνα χειρισμού επιτυχούς ασύγχρονης κλήσης.
		var handleSuccess = function(o){

            //imoodel[en]: If the return object has text.
            //imoodel[el]: Αν το αντικείμενο επιστροφής έχει κείμενο.
			if(o.responseText !== undefined){
			
                //imoodel[en]: Splitting of text to an array of items.
                //imoodel[el]: Χωρισμός του κειμένου σε διάνυσμα αντικειμένων.
				var items = o.responseText.split('.');
                
                //imoodel[en]: For each array's item...
                //imoodel[el]: Για κάθε αντικείμενο του διανύσματος...
				for (var i=0; i<items.length-1; i++) {

                    //imoodel[en]: ...splitting each item's to three values...
                    //imoodel[el]: ...χωρισμός του κειμένου κάθε αντικειμένου σε τρεις τιμές...
					var item = items[i];
					var pmt = item.split(',');

                    //imoodel[en]: ...assign each value to a temporary variable...
                    //imoodel[el]: ...ανάθεση κάθε τιμής σε μια προσωρινή μεταβλητή...
                    var itm = pmt[0];
                    var x = pmt[1];
                    var y = pmt[2];
                    
                    //imoodel[en]: ...and use these variables to set item's position on webtop.
                    //imoodel[el]: ...και χρήση των μεταβλητών αυτών για καθορισμό της θέσης του αντικειμένου στην επιφάνεια της σελίδας.
                    YAHOO.util.Dom.setX(itm, x);
                    YAHOO.util.Dom.setY(itm, y);
				}
			}
		}

        //imoodel[en]: Unsuccessful asynchronous call handling routine (showing debug messages)
        //imoodel[el]: Ρουτίνα χειρισμού μη επιτυχούς ασύγχρονης κλήσης (εμφάνισης μηνυμάτων αποσφαλμάτωσης).        
		var handleFailure = function(o){
            
            //imoodel[en]: If the return object has text.
            //imoodel[el]: Αν το αντικείμενο επιστροφής έχει κείμενο.
			if(o.responseText !== undefined){
				div.innerHTML = "<li>Transaction id: " + o.tId + "</li>";
				div.innerHTML += "<li>HTTP status: " + o.status + "</li>";
				div.innerHTML += "<li>Status code message: " + o.statusText + "</li>";
			}
		}

        //imoodel[en]: Asynchronous call return object definition.
        //imoodel[el]: Ορισμός του αντικειμένου επιστροφής της ασύγχρονης κλήσης.
		var callback =
		{
		  success:handleSuccess,
		  failure: handleFailure,
		  argument: { foo:"foo", bar:"bar" }
		};
		
        //imoodel[en]: Use YUI to get all items of class imoodelitem into an array.
        //imoodel[el]: Χρήση της YUI για τη λήψη όλων των αντικειμένων της κλάσης imoodelitem σε ένα διάνυσμα.
		var imdItemsArray = YAHOO.util.Dom.getElementsByClassName(imdItemClass);
		
        //imoodel[en]: Creation of the URL for calling asynchronously the PHP module for reading from database.
        //imoodel[el]: Δημιουργία του URL ασύγχρονης κλήσης του αρθρώματος PHP για την ανάγνωση από τη βάση δεδομένων.        

        //imoodel[en]: Concatenating the reading module's absolute web server path with the URL of the current page and user name.
        //imoodel[el]: Παράθεση της απόλυτης διαδρομής του αρθρώματος ανάγνωσης στο διακομιστή με το URL της τρέχουσας ιστοσελίδας και το όνομα χρήστη.        
		var sUrl1 = "<? echo $CFG->wwwroot; ?>/theme/imoodel/imoodel_load_item_properties.php";
		var sUrl2 = "?imdurl=<?php echo $_SERVER['PHP_SELF']; ?>";
		var sUrl3 = "&imdusr=<?php echo $USER->username; ?>";
		
		sUrl = sUrl1 + sUrl2 + sUrl3;
        
        //imoodel[en]: Asynchronous call of reading module using YUI.
        //imoodel[el]: Ασύγχρονη κλήση του αρθρώματος ανάγνωσης με χρήση της YUI.        
        var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);  		
	}	
	
    //imoodel[en]: Set event listening for current webpage to on.
    //imoodel[el]: Ενεργοποίηση της ακρόασης γεγονότων για την τρέχουσα ιστοσελίδα.
    imdEventListenInit();
    
//-->
</script>
