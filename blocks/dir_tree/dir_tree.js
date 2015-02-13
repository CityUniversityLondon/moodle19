/**
 * NAME:   JSON to YUI::TreeView library
 * AUTHOR: Dean Stringer (University of Waikato, NZ)
 * VERSION: 20081106
 * 
 * A library used to take data from a back-end script that contains
 * JSON encoded info about folders and their content, and process that
 * data for display in a YUI TreeView widget.
 *   see: http://developer.yahoo.com/yui/
 *
 * The tree can be rendered in one of two modes:
 *   narrow : suitable for blocks/portlets, file/folder-name only 
 *   wide : suitable for page sized areas, name + size, date details
 *
 * The mode is set by declaring a JS variable 'dirTreeWidth' in your
 * HTML page with one of the above values. 'narrow' is the default 
 *
 * This library needs to be loaded with a 'defer' switch and boot-straps 
 * itself when the YAHOO.util.Event.onDOMReady event fires which then builds 
 * the tree in the browser. The client using this library also needs to have
 * loaded the 'yui_yahoo', 'yui_dom', 'yui_event' and 'yui_treeview' libs 
 */
 
(function() {
    var tree; //will hold our TreeView instance
    var maxLabelLengthWide = 40;
    var maxLabelLengthNarrow = 17;
    
    /**
     * Initialise our TreeView and call the functions to populate it
     */
    function treeInit() {
        // empty the static render of the tree, dont need it now
        var staticDiv = document.getElementById(dirTreeStaticName);
        staticDiv.innerHTML = '';	// not part of the W3C DOM I know, but is well supported
        tree = new YAHOO.widget.TreeView(dirTreeYUIName);
        var root = tree.getRoot();
		if ((typeof(dirTreeWidth) != "undefined") && (dirTreeWidth == 'wide') ) {
			buildWideNodeTree(treeJSONData, root, 0);
        } else { 
			buildNarrowNodeTree(treeJSONData, root, 0);
		}
		tree.draw();
    }

	/**
	 * Step through the nodes in our data and render each item and any children
	 * also associate a css class for each to display a relevant icon
	 */
	function buildWideNodeTree(treeData, root, depth) {
		// update a placeholder header table to resemble the standard non JS resource display
		var headerDiv = document.getElementById(dirTreeYUIName + 'header');
	    headerDiv.innerHTML = '<table class="files" summary=""><th class="cellIcon"></th>' +
	        '<th class="cellName" align="left">Name</th>' +
        	'<th class="cellItemSize" align="left">Size</th>' +
        	'<th class="cellDate" align="left">Modified</th>' +
			'</tr></table>';

		// get the item names from treeData and save their index number
	 	var keyNames = new Array();
	 	for (var i = 0, numItems = treeData.length; i < numItems; ++i) {
	 		keyNames[treeData[i].name] = i;
	 	}
	 	// sort the names we found in treedata
	 	var keyList = new Array();
	 	for (key in keyNames) {	keyList.push(key); }
	 	keyList.sort();

		// now step thru the original treeData in order of the sorted names
		for (var i = 0, numItems = treeData.length; i < numItems; ++i) {
			var dirItem = treeData[keyNames[keyList[i]]];
			var oData = new Object;
			itemLabel = fit_string(dirItem.name, maxLabelLengthWide - (depth * 2));
			if (dirItem.type == 'folder') {
				//oData.html = '<a href="javascript:YAHOO.widget.TreeView.getNode(\'' + dirTreeYUIName + '\',' + 1 + ').toggle()" ' +
				oData.html = itemLabel +
					"</td><td class='cellFolderSize'>";
				oData.href='';
			} else {
				oData.html = "<a target='_new' href='" + dirItem.path + "' " +
					'onclick="this.target=\'resourcedirectory1\'; ' +
					'return openpopup(\'' + dirItem.path + '\', \'resourcedirectory1\', ' +
						'\'menubar=0,location=0,scrollbars,resizable,width=600,height=450\', 0);"' +
					">" + itemLabel + "</a>" +
					"</td><td class='cellItemSize'>";
			}
			oData.html += dirItem.size + "</td>" +
				"<td class='cellDate'>" + dirItem.modified + "";
			var tmpNode = new YAHOO.widget.HTMLNode(oData, root);
			tmpNode.contentStyle = "icon-" + dirItem.extension;
			tmpNode.hasIcon = true;
			if (dirItem.type == 'folder') {	// recursive call to display children too
				buildWideNodeTree(dirItem.children, tmpNode, depth+1);
			}
		}
	}

	/**
	 * Step through the nodes in our data and render each item and any children
	 * also associate a css class for each to display a relevant icon
	 */
	function buildNarrowNodeTree(treeData, root, depth) {
		// get the item names from treeData and save their index number
	 	var keyNames = new Array();
	 	for (var i = 0, numItems = treeData.length; i < numItems; ++i) {
	 		keyNames[treeData[i].name] = i;
	 	}
	 	// sort the names we found in treedata
	 	var keyList = new Array();
	 	for (key in keyNames) {	keyList.push(key); }
	 	keyList.sort();
	 	
		for (var i = 0, numItems = treeData.length; i < numItems; ++i) {
			var dirItem = treeData[keyNames[keyList[i]]];
			var oData = new Object;
			oData.expanded = false;
			oData.label = fit_string(dirItem.name, maxLabelLengthNarrow - (depth * 2));
			oData.title = dirItem.name;	// so user can see entire filename in tooltip
			if (dirItem.type == 'folder') {
				oData.expanded = true;
			} else {
				oData.href = dirTreeBaseURL + dirItem.path;
			}
			var tmpNode = new YAHOO.widget.TextNode(oData, root);
			tmpNode.labelStyle = "icon-" + dirItem.extension;
			if (dirItem.type == 'folder') {	// recursive call to display children too
				buildNarrowNodeTree(dirItem.children, tmpNode, depth+1);
			}
		}
	}

    /**
     * Trim the middle out of filenames that are too long replacing with '...'
     */
    function fit_string(theString, maxLength) {
        if(theString.length <= maxLength) {
            return theString;
        }
        str1 = theString.substr(0, maxLength-8);
        str2 = theString.substr(theString.length-5);
        return str1 + "..." + str2;
    }
    
    // once DOM has loaded we can initialize our TreeView instance:
	try {
	    YAHOO.util.Event.onDOMReady(treeInit);
	} catch(err) {
		// must be an older YUI version without onDOM, do a window.load instead 
	    YAHOO.util.Event.addListener(window, 'load', treeInit);
	}    
	
})();//anonymous function wrapper closed; () notation executes function