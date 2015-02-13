/**************************************************************************
MIT License

Copyright (c) 2007-2011 Guy Thomas

GTLib

***************************************************************************/

/**
 * =======================================================================
 * Guy Thomas lib
 *
 * Setup GTLib object with stubs
 * =======================================================================
 **/
if (typeof(GTLib)=='undefined'){
    GTLib={
        version:2011101700,
        ExportFuncs:typeof(GTLib_ExportFuncs)!='undefined' ? GTLib_ExportFuncs : true,     // export functions is the default setting. Set to true if you want to use shorthand - e.g. Dialog() instead of GTLib.Widgets.Dialog() (shorthand is less compatible with other libs) - use global var GTLib_ExportFuncs=false before loading this lib if you DO NOT want to export the functions
        Widget:{}
    };
}

/**
 * Put all js language specific functionality here
 */
GTLib.Base={

    //
    // Purpose - binds arguements to a function
    // Taken from http://www.ejball.com/EdAtWork/PermaLink.aspx?guid=14c020c7-20a6-47d7-b445-9c3ecda153b2
    //
    BindArguments:function(fn){
      var args = [];
      for (var n = 1; n < arguments.length; n++)
        args.push(arguments[n]);
      return function () { return fn.apply(this, args); };
    },

    //
    // Purpose - Compares 2 objects
    // Reason: Javascript comparitors seem to be broken - e.g. I found the following code to not work correctly with javascript
    // var obj1={id:1, name:'guy'};
    // var obj2={id:1, name:'guy'};
    // if (obj1==obj2){
    //   alert ('both objects are the same');
    // }
    // The above code should bring up the alert ('both objects are the same') but it doesn't
    // Replacing if(obj1==obj2) with GTLib.Base.ObjectCompare(obj1, obj2) will bring up the alert
    //
    ObjectCompare:function (object1, object2){
        if (object1==object2){
            return (true);
        } else {
        	// fail comparison on following types (== comparison has already failed!)
        	var atypes=['number', 'string', 'null', 'undefined'];
        	for (var at=0; at<atypes.length; at++){
        		var atype=atypes[at];
        		if (typeof(object1)==atype || typeof(object2)==atype){
        			return (false);
        		}
        	}
        }
        // count number of properties for 1st object
        var obj1nprops=0;
        for (var x in object1){
            obj1nprops++;
        }
        // count number of properties for 2nd object
        var obj2nprops=0;
        for (var x in object2){
            obj2nprops++;
        }

        // if number of properties does not match then return false
        if (obj1nprops!=obj2nprops || obj1nprops==0){
            return (false);
        }

        for (var n in object1){
            if (typeof(object1[n])!='undefined' && typeof(object2[n])!='undefined'){
                // recursive check
                if (!GTLib.Base.ObjectCompare(object1[n], object2[n])){
                    // object properties don't match - return false
                    return (false);
                }
            } else {
                // if both object properties are undefined then assume as being the same
                if (typeof(object1[n])=='undefined' && typeof(object2[n])=='undefined'){
                    return (true);
                } else {
                    return (false);
                }
            }
        }


        // all object properties match so return true
        return (true);
    },

    //
    // Set optional properties of an object(will only set non private and pre-declared properties)
    //
    ObjectPropsFromOptions:function(obj, options){
        var objprops=new Array();
        for (var p in obj){
            objprops[p]=true;
        }
        for (var o in options) {
            if (objprops[p] && o.substr(0,1)!='_' && o!="options" && typeof(options[o])!='undefined' && options[o]!= null){
                obj[o]=options[o];
            }
        }
    },

    //
    // http://keithdevens.com/weblog/archive/2007/Jun/07/javascript.clone
    //
    Clone:function(obj){
        if(obj == null || typeof(obj) != 'object') {return obj;}

        var temp = new obj.constructor(); // changed (twice)

        for(var key in obj){temp[key] = GTLib.Base.Clone(obj[key]);}

        return temp;
    }

}

if (GTLib.ExportFuncs){
    BindArguments=GTLib.Base.BindArguments;
    ObjectCompare=GTLib.Base.ObjectCompare;
}

/**
/* Browser object
**/
GTLib.Browser={
 ie:navigator.appVersion.indexOf('MSIE')>-1,
 ie5:navigator.appVersion.indexOf('MSIE 5')>-1,
 ie6:navigator.appVersion.indexOf('MSIE 6')>-1,
 ie7:navigator.appVersion.indexOf('MSIE 7')>-1,
 ie8:navigator.appVersion.indexOf('MSIE 8')>-1,
 opera:navigator.userAgent.indexOf("Opera")>-1,
 safari:navigator.userAgent.indexOf("Safari")>-1 && navigator.userAgent.indexOf("Chrome")==-1,
 chrome:navigator.userAgent.indexOf("Chrome")>-1,
 firefox:navigator.userAgent.indexOf("Firefox")>-1,
 
 // return more useful info
 details:function(){
    var browserdets={browser:null, version:null, versionnumber:null};    
    var agents={
        'Opera':{'name':'Opera'},
        'Firefox':{'name':'Firefox'},
        'Chrome':{'name':'Chrome'},
        'Safari':{'name':'Safari'},
        'Netscape':{'name':'Netscape'},
        'MSIE':{'name':'IE', 'versiondel':'MSIE ', 'versionenddel':';'}
    };

    var uagent=navigator.userAgent;
    var versiondel='';
    for (var check in agents){

        var barr=agents[check];

        var bname=barr.name;            
        if (uagent.indexOf(check)>-1){
            browserdets.browser=bname;
            if (typeof(barr.versiondel)!='undefined'){
                versiondel=barr.versiondel;
            } else {                
                versiondel=barr.name+'/';
            }
            if (uagent.indexOf(versiondel)>-1){
                tmparr=uagent.split(versiondel);
                if (tmparr.length==2){
                    if (typeof(barr.versionenddel)!='undefined'){
                        versionenddel=barr.versionenddel;
                    } else {
                        versionenddel=' ';
                    }
                    tmparr2=tmparr[1].split(versionenddel);
                    browserdets.version=tmparr2[0];
                    tmparr3=browserdets.version.split('.');
                    browserdets.versionnumber=tmparr3[0];
                    if (tmparr3.length>1){
                        browserdets.versionnumber+='.';
                        for (var t=1; t<tmparr3.length; t++){
                            browserdets.versionnumber+=tmparr3[t];                            
                        }
                    }
                    browserdets.versionnumber=parseFloat(browserdets.versionnumber);
                }
            }
            break;
        }     
    }
    return (browserdets);
 }
};

/**
 * Array Library
 */
//requires GTLib.Base
GTLib.Array={

    //
    // Purpose: scan array for needle
    //
    search:function(needle, haystack) {
        for (n=0; n<haystack.length; n++){
            if (needle===haystack[n]){
                return (n);
            }
            // javascript comparitors seem to be broken so use this comparitor
            if (GTLib.Base.ObjectCompare(needle, haystack[n])){
                return (n);
            }
        }
        // needle not found
        return (-1);
    },

    //
    // Purpose: get integer position for text key
    //
    intPosKey:function(searchkey, haystack) {
        var n=-1;
        for (var key in haystack){
            n++;
            if (searchkey===key){
                return (n);
            }
        }
        // needle not found
        return (-1);
    },

    //
    // Purpose: return true if testvar is array
    // @param required testvar - variable to test type of
    // @returns boolean
    //
    isArray:function(testvar) {
        return ( testvar instanceof Array );
    }
}

//
// Export Functions
//
if (GTLib.ExportFuncs){
    arraySearch=GTLib.Array.search;
    isArray=GTLib.Array.isArray;
}

/**
 * GTLib String Library
 */
GTLib.String={
    trim:function(s) { var s=String(s); return s.replace(/^\s+|\s+$/, ''); }
}

//
// Export functions
//
if (GTLib.ExportFuncs){
    trim=GTLib.String.trim;
}

/**
 * GTLib XML Library
 */
GTLib.XML={
    //
    // Purpose, converts xml as text to xml object
    // GThomas
    //
    TextToXMLObj:function(txt){
    	try {
    		var xmlObj=new ActiveXObject('Microsoft.XMLDOM');
    		xmlObj.loadXML(txt);
    	} catch (e){
    		var xmlObj=new DOMParser().parseFromString(txt, 'text/xml');
    	}
    	return (xmlObj);
    },
    //
    // Purpose, gets immediate children of a specific node (node) by a specific tagname (chkTagName)
    // GThomas
    //
    ChildrenByTag:function(node, chkTagName){
        if (node!==null && typeof(node)!='undefined' && node.childNodes){
            var nodes=node.childNodes;
            var retNodes=new Array();
            for (var c=0; c<=nodes.length; c++){
                var chkNd=nodes[c];
                if (chkNd!=null && typeof(chkNd)!='undefined'){
                    var thsTagName=chkNd.tagName;
                    if (thsTagName==chkTagName){
                        retNodes.push(chkNd);
                    }
                }
            }
            if (retNodes.length==0){
                return (null);
            } else {
                return (retNodes);
            }
        } else {
            return (null);
        }
    },
    //
    // Gets the first tag of tagName and returns its value - or an empty string if it doesn't have a value
    //
    FirstTagVal:function(nd, tagName){
    	try {
    		var nodeVal=nd.getElementsByTagName(tagName)[0].childNodes[0].nodeValue;
    	} catch (e){
    		return (""); // return empty string
    	}
    	return (nodeVal);
    },

    //
    // Gets first tag of Tagname and unpacks XML safed string
    //
    FirstTagValUnpack:function(nd, tagName){
        var raw=GTLib.XML.FirstTagVal(nd, tagName);
        return (GTLib.XML.SafedUnpack(raw));
    },

    //
    // Unpacks XML safed string (reserved chars were converted)
    //
    SafedUnpack:function(str){
        str=str.replace(/~~AMP~~/gi,'&');
        str=str.replace(/~~LT~~/gi,'<');
        str=str.replace(/~~GT~~/gi,'>');
        str=str.replace(/~~QT~~/gi,'"');
        str=str.replace(/~~AP~~/gi,'\'');
        return(str);
    },

    //
    // Packs XML unsafe string (reserved chars were converted)
    //
    UnsafedPack:function(str){
        str=str.replace(/&/gi,'~~AMP~~');
        str=str.replace(/</gi,'~~LT~~');
        str=str.replace(/>/gi,'~~GT~~');
        str=str.replace(/"/gi,'~~QT~~');
        str=str.replace(/'/gi,'~~AP~~');
        return(str);
    }

}

//
// Export functions
//
if (GTLib.ExportFuncs){
    TextToXMLObj=GTLib.XML.TextToXMLObj;
    XMLChildrenByTag=GTLib.XML.ChildrenByTag;
    XMLFirstTagVal=GTLib.XML.FirstTagVal;
    XMLFirstTagValUnpack=GTLib.XML.FirstTagValUnpack;
    XMLSafedUnpack=GTLib.XML.SafedUnpack;
    XMLUnsafedPack=GTLib.XML.UnsafedPack;
}


/**
 * DOM traversing and manipulation library
 */
GTLib.DOM={
    //
    //Gets DOM element by Id
    //
    getEl:function (id) {
        if (typeof(id) == "string"){
            return (document.getElementById(id));
        } else {
            return (id);
        }
    },
    //
    // Get Form Field value by id
    // Safari Bug fix by Nick Thompson UCLA 2010091600
    //
    getFieldVal:function(id, seperator, fname){
        var sep=seperator ? seperator : "|"; // default seperator for multi vals is pipe symbol
        var el=false;
        if (fname && typeof(fname!=='undefined')){
            if (typeof(document.forms[fname])!='undefined'){                
                el=document.forms[fname].elements[id];
            } else {                
                el=document.forms[0].elements[id];
            }            
        } else {
            el=GTLib.DOM.getEl(id);
        }
        
        var opts=null;
        var val="";
        var cbs=null;  
        
        // GT Mod 2011020800 added check for el.innerHTML=='' (IE  fix)
        if (!el || typeof(el)=='undefined' || (GTLib.Browser.ie && GTLib.Browser.details().versionnumber<=7 && typeof(fname)=='undefined' && el.id=='') || (GTLib.Browser.ie && el.innerHTML=='')){ // note, el.id=='' is a check for <=ie7 which gets a dom item by name if it can't by id - weird!            // couldn't get field by id, try to get by name using first form in doc
        	if (typeof(fname)!=='undefined' && typeof(document.forms[fname])!='undefined'){
        		el=document.forms[fname].elements[id];                        
        	} else {
        		el=document.forms[0].elements[id];
        	}
                if (!el){
                    // GT Mod 2011042100
                    // couldn't get element by form so just hope that by id is better than nothing
                    // this is here in case el.innerHTML='' but the field cannot be recovered from the form
                    el=GTLib.DOM.getEl(id);
                }
        }
        
        var eltype='';
        
        
        

        if(el!=null && (typeof(el) == 'object' || typeof(el)=='function')){ // GT Mod 2011-10-17 - for Safari, check type of el == 'function'
            if (typeof(el.type)=='undefined' && typeof(el.nodeName)=='undefined'){
                    var elmembers=el;
                    el=el[0];
            }
            if (el.type=='text' || el.type=='textarea' || el.type=='hidden'){
                return (el.value);
            } else if (el.type=='checkbox' || el.type=='radio'){
                eltype='cboxradio';
            } else if (typeof(el.type)!='undefined' && el.nodeName.toLowerCase()=='select'){
                eltype='select';
            }
        } else if (el!=null && el.nodeName.toLowerCase()=='select'){
            eltype='select';
        }
        
        if (eltype=='select'){
            opts=el.getElementsByTagName('option');
            val="";
            for (o=0; o<opts.length; o++){
                if (opts[o].selected){
                    val+=val!="" ? sep : "";
                    val+=opts[o].value;
                }
            }
            return (val);            
        } else if (eltype=='cboxradio'){
            if (elmembers && elmembers.length){
                cbs=elmembers;
            } else {
                cbs=el;
            }
            val="";            
            for (c=0; c<cbs.length; c++){                
                if (cbs[c].checked){
                    val+=val!="" ? sep : "";
                    val+=cbs[c].value;
                }
            }
            return (val);            
        }
        
        return ('');
    },

    setFieldVal:function(id, val, fname){
        var el=GTLib.DOM.getEl(id);
        if (!el || el.id==''){ // note, el.id=='' is a check for <=ie7 which gets a dom item by name if it can't by id - weird!
            // couldn't get field by id, try to get by name using first form in doc
        	if (typeof(fname)!=='undefined' && typeof(document.forms[fname])!='undefined'){
        		el=document.forms[fname].elements[id];
        	} else {                    
        		el=document.forms[0].elements[id];
        	}
        }
        if (!el || !'type' in el){
            return;
        }
        if (el.type=='select-one' || el.type=='select-multiple' || el.type=='select'){
            for (var o=0; o<el.options.length; o++){
                if (typeof(el.options[o])!=='undefined' && el.options[o].value==val){
                    el.options[o].selected='selected';
                } else if (el.type!='select-multiple'){
                    el.options[o].selected=''; // wipe out other selections if not multi select
                }
            }
        } else if (el.type=='textarea'){
        	el.innerHTML=val;
        	el.value=val;
        } else {
        	el.value=val;
        }
    },
    //
    // Gets a label by for attribute
    //
    labelByFor:function(id, parentEl){
        var parentEl=typeof(parentEl)=='undefined' ? document : parentEl;
        var labels=parentEl.getElementsByTagName('label');
        for (var l=0; l<labels.length; l++){
            var fr=labels[l].getAttribute('for');
            if (id==fr){
                return (labels[l]);
            }
        }
    },
    
    applyAttributes:function(el, atts){
        
        // @todo - make applicable based on element type
        var applicable=['id', 'className', 'class', 'style', 'innerHTML', 'name', 'multiple', 'type', 'value', 'checked'];        
        
        for (var a=0; a<applicable.length; a++){
            if (atts[applicable[a]]){
                var attname=applicable[a];
                // attname is used to accomodate IE - e.g. converts style to cssText
                attname=attname=='className' ? 'class' : attname;

                // if ie5, ie6 or ie7 used then change 'class' attname to 'className'
                if (attname=='class' && (GTLib.Browser.ie5 || GTLib.Browser.ie6 || GTLib.Browser.ie7)){
                	attname='className';
                }

                if (attname=='style' && GTLib.Browser.ie){
                    el.style.setAttribute('cssText', atts['style'], 0);
                } else {
                    el.setAttribute(attname, atts[applicable[a]]);
                }
            }
        }        
    },

    //
    //Creates DOM element with options (id, className, style, innerHTML)
    //
    createEl:function(elType, opts){
        var elType=elType.toLowerCase();
        
        if (opts && 'name' in opts && GTLib.Browser.ie && GTLib.Browser.details().versionnumber<=7){
            // LTE ie7 - you have to put the name attribute inside the element as HTML
            var el=document.createElement('<'+elType+' name="'+opts.name+'">');
        } else {
            // every other browser
            var el=document.createElement(elType);
        }

        var opts=!opts ? {} : opts;

        GTLib.DOM.applyAttributes(el, opts);


        if (opts.apply){
            if (opts.parent){
                parent.appendChild(el);
            } else {
                document.body.appendChild(el);
            }
        }

        return (el);
    },

    //
    // Create an anchor but stop it from linking when clicked
    // Useful for anchors where you simply want a javascript click event - no link
    // e.g. <a href="#" onclick="alert('hello'); return (false);">say hello</a>
    //
    unlinkedAnchor:function(opts){
        var a=GTLib.DOM.createEl('a', opts);
        a.href='#';
        a.onclick=function(){return(false)};
        return (a);
    },

    //
    // Return a DOM elements dimensions
    //
    getElDims:function(el){
        return ({h:el.offsetHeight, w:el.offsetWidth});
    },

    //
    // Gets DOM element(s) by tag and full textstring of classname
    //
    getElByTagAndClass:function(parent, tag, exactclasses){
        var els=new Array();
        var kids=parent.getElementsByTagName(tag);
        for (k=0; k<kids.length; k++){
            if (kids[k].className==exactclasses){
                els.push(kids[k]);
            }
        }
        return (els);
    }
}

// Export functions
if (GTLib.ExportFuncs){
    $GT=GTLib.DOM.getEl;
    $GTF=GTLib.DOM.getFieldVal;
    setFieldVal=GTLib.DOM.setFieldVal;
    createEl=GTLib.DOM.createEl;
    unlinkedAnchor=GTLib.DOM.unlinkedAnchor;
    labelByFor=GTLib.DOM.labelByFor;
}



/**
 * Ajax Library
 */
GTLib.Ajax={

    respTextInErrors:false, // if true, shows response text in any errors


    //
    // Purpose: Post AJAX / AJAJ request
    // @param {string} uri - uri to post to
    // @param {string} postData - data to post (can be an empty string)
    // @param {object} callbacks - 2 functions wrapped in object, {success, failure}
    // @param {object*} options -
    //                  failOnUnload - if true, will trigger failure even if window location has changed
    //                  noAJAXSupport - message to display if browser does not provide AJAX support
    //
    Post:function(uri, postData, callbacks, options){

        // Based on http://developer.mozilla.org/en/docs/AJAX:Getting_Started

        if (typeof(options)=='undefined'){
            options={};
        }

        if (typeof(options.failOnUnload)=='undefined'){
            // default option for callback 'failure' is NOT to trigger if window location has changed
            options.failOnUnload=false;
        }
        if (typeof(options.noAJAXSupport)=='undefined'){
            // default option for message if browser does not support AJAX
            options.noAJAXSupport='Your browser does not appear to support AJAX. You could download a modern standards compliant web browser - e.g. Firefox, Opera or Safari';
        }
        if (typeof(options.retryMax)=='undefined'){
            // how many times should we try the AJAX call?
            options.retryMax=2;
        }
        if (typeof(options.retryPass)=='undefined'){
            // is this the 1st,2nd,3rd attempt ,etc ??
            options.retryPass=0;
        }

        var http;
        if (window.XMLHttpRequest) { // Mozilla, Safari, ...
            http = new XMLHttpRequest();
            if (http.overrideMimeType) {
                http.overrideMimeType('text/xml');
            }
        }
        else if (window.ActiveXObject) { // IE
            try {
                http = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch (e) {
                        try {
                            http = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                    catch (e) {}
                }
        }
        if (!http) {
            alert(options.noAJAXSupport);
            return false;
        }

        // Custom request object (this is cos M$ IE wouldn't let me mutate the XMLHTTP object)
        var req={
            http                    :   http,
            unloaded                :   false,
            wuri                    :   window.location,
            uri                     :   uri,
            postData                :   postData,
            callbacks               :   callbacks,
            options                 :   options
        };

        req.http.onreadystatechange = function() { GTLib.Ajax._ResponseTriggerCallBack(req);};
        YAHOO.util.Event.addListener(window, 'unload', function(){req.unloaded=true;});
        req.http.open('POST', uri, true);
        req.http.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");
        req.http.send(postData);
    },

    //
    // Scope: Private
    // Purpose: Called automatically on http ready state change
    //
    _ResponseTriggerCallBack:function(req){

        var http=req.http;

        // Add to custom request object (this is because M$ IE wouldn't let me mutate the XMLHTTP object)
        req.readyState              =   http.readyState;

        if (http.readyState == 4) {
            if (http.status == 200) {

                // Add to custom request object (this is because M$ IE wouldn't let me mutate the XMLHTTP object)
                req.responseText            =   http.responseText;
                req.responseXML             =   http.responseXML;

                // Call success function
                req.callbacks.success(req);
            } else {
                // Delay callback to check for unload status
                window.setTimeout(
                    function(){
                        GTLib.Ajax._TriggerFailureCallBack(req);
                    },
                    1000
                );
            }
        }
    },

    //
    // This callback is delayed by 1 second to allow for unload event to take place
    //
    _TriggerFailureCallBack:function(req){

        req.options.retryPass++;

        var retryPass=req.options.retryPass;
        var retryMax=req.options.retryMax;
        var failOnUnload=req.options.failOnUnload;

        var unload=false;
        if (req.unloaded==true){
            unload=true;
        } else if (req.wuri!=window.location){
            unload=true;
        }

        // Retry ajax post if not passed number of retry attempts and window has not been unloaded
        if (retryPass<retryMax && unload==false){
            // retry ajax post
            GTLib.Ajax.Post(req.uri, req.postData, req.callbacks, req.options);
        } else {
            if (unload==false || failOnUnload){
                // Call failure function
                req.callbacks.failure(req);
            }
        }
    },

    //
    // Purpose: Check xml for and report critical errors
    // In: xml dom object
    //
    CheckXMLCriticalErrors:function(xmlObj, report){
        var report=typeof(report)=='undefined' ? true : report // default to report critical errors
        var criticalError=XMLFirstTagValUnpack(xmlObj, 'critical_error');
        if (report){
            if (criticalError){
                var dg=new Dialog("<span class='title_error'>Error - Critical Error</span>","<div class='body_error'></div>"+criticalError, {buttons:[{name:'Close', functCode:function(){dg.CloseDialog();}}], lightBox:true, w:280, h:220, zIndex:2000});
                dg.Init();
                return (criticalError);
            }
        }
        return (criticalError);
    },

    //
    // Purpose: Converts AJAX response to dom xml - better parser
    //
    ResponseToDomXML:function(http){
        var response=http.responseText;
        try {
            var xmlDomObj=new ActiveXObject('Microsoft.XMLDOM');
            xmlDomObj.loadXML(response);
        } catch (e){
            var xmlDomObj=new DOMParser().parseFromString(response, 'text/xml');
        }
        return (xmlDomObj.documentElement);
    },

    //
    // Purpose: One stop shop for checking critical errors, checking correct response and returning response
    //
    ResponseToDomXMLValidate:function(http, resptype, reporterrors){
        var resp=GTLib.Ajax.ResponseToDomXML(http);
        // Return error for critical errors
        var criticalError=GTLib.Ajax.CheckXMLCriticalErrors(resp, reporterrors);
        if (criticalError){
            return ({resp:resp, error:criticalError}); // abort on critical errors
        }
        var respTypeCheck=XMLFirstTagVal(resp,'response_type');
        // Return error for unrecognised response (if it does not match resptype)
        if (respTypeCheck!=resptype){
            var resptxterr=GTLib.Ajax.respTextInErrors ? '  '+http.responseText : '';
            var respError='Error: unrecognised response received - expected '+resptype+' but received '+respTypeCheck+resptxterr;
            if (reporterrors){
                alert(respError);
            }
            return ({resp:resp, error:respError}); // abort on critical errors
        }
        return ({resp:resp, error:false});
    }
};

//
// Export functions
//
if (GTLib.ExportFuncs){
    AJAXResponseToDomXML=GTLib.Ajax.ResponseToDomXML;

    // This used to use Yahoo's crappy Connect lib.
    // It is unnecessarilly bloated and doesn't work well with a reverse proxy set up.

    //AJAXPost=function (uri, postData, callbacks){YAHOO.util.Connect.asyncRequest('POST', uri, callbacks, postData)}; // wrapper for yui async request

    AJAXPost=function (uri, postData, callbacks, options){GTLib.Ajax.Post(uri, postData, callbacks, options)};
    AJAXCheckXMLCriticalErrors=GTLib.Ajax.CheckXMLCriticalErrors;
    AJAXResponseToDomXMLValidate=GTLib.Ajax.ResponseToDomXMLValidate;

}

/**
/* ZIndex drag and drop implementation, extending YAHOO.util.DD
/**/
GTLib.DD={
    ZIndex:function(id, sGroup, config) {
        GTLib.DD.ZIndex.superclass.constructor.apply(this, arguments);
    }
};

YAHOO.lang.extend(GTLib.DD.ZIndex, YAHOO.util.DD, {
    origZ: 0,
    startDrag: function(x, y) {
        YAHOO.log(this.id + " startDrag", "info", "example");
        var style = this.getEl().style;
        // store the original z-index
        this.origZ = style.zIndex;
        // Set custom zindex
        style.zIndex = this.config.dragZ;
    },
    endDrag: function(e) {
        // restore the original z-index
        this.getEl().style.zIndex = this.origZ;
    }
});


/**
 * Form library
 * requires GTLib.DOM, GTLib.Array
 */
GTLib.Form={
    //
    // Purpose: Applies event which stops certain keys from being pressed
    // @param fieldId - id of field to filter keys
    // @param lists - {whitelist:array, blacklist:array}
    //
    applyFilterKeys:function(fieldId, lists){
        var field=GTLib.DOM.getEl(fieldId);
        field.onkeypress=function(e){return(GTLib.Form._filterKeys(e, lists))};
    },

    //
    // Purpose : stops certain keys from being pressed
    // @param required e - event
    // @param lists - {whitelist:array, blacklist:array}
    // @returns keyCode or false
    //
    _filterKeys:function(e,lists){
        ev=(window.event) ? event : e;
        if (window.event){
            var keyCode=ev.keyCode;
        } else {
            var keyCode=ev.charCode;
        }

        var ecd=String.fromCharCode(keyCode);

        // return true for non char keys - e.g. delete key
        if (!keyCode){
            return true;
        }

        var keyValid=keyCode; // until proven false
        if (typeof(lists.blacklist)!='undefined'){
            if (GTLib.Array.isArray((lists.blacklist))){
                if (GTLib.Array.search(ecd, lists.blacklist)>-1){
                  keyValid=false;
                }
            }
        }
        if (typeof(lists.whitelist)!='undefined'){
            if (GTLib.Array.isArray((lists.whitelist))){
                if (GTLib.Array.search(ecd, lists.whitelist)==-1){
                    keyValid=false;
                }
            }
        }
        return (keyValid);
    },

    //
    // Purpose: Gets all fields in a form and converts them to an ajax send string
    // * NOTE: Does not work with files, etc...
    //
    makeAjaxSendStr:function(form){
        var inps=form.getElementsByTagName('input');
        var sels=form.getElementsByTagName('select');
        var tas=form.getElementsByTagName('textarea');
        var sendstr='';        
        sendstr=GTLib.Form._buildSendStr(sendstr, inps);
        sendstr=GTLib.Form._buildSendStr(sendstr, sels);
        sendstr=GTLib.Form._buildSendStr(sendstr, tas);
        return (sendstr);
    },

    //
    // Purpose: Private function used to build send string for array of form elements
    //
    _buildSendStr:function(sendstr, elarray){
        for (var i=0; i<elarray.length; i++){            
            var el=elarray[i];            
            if (el.id || el.name){
                sendstr+=sendstr=='' ? '' : '&';
                var name=el.id ? el.id : el.name;                
                var fldval=GTLib.DOM.getFieldVal(el);  // safari didn't like getting some fields by name so just pass in element              
                if (fldval){
                	sendstr+=name+'='+fldval.replace(/&/g,'~~AMP~~');
                }
            }
        }
        return (sendstr);
    }
}

//
// Export Functions
//
if (GTLib.ExportFuncs){
    formMakeAjaxSendStr=GTLib.Form.makeAjaxSendStr;
    applyFilterKeys=GTLib.Form.applyFilterKeys;
}

/**
 * GTLib Moodle library
 */
GTLib.Moodle={
    //
    // Purpose: Return a language string item (if it exists)
    // This function enables you to share language files with javascript
    // Your block must provide the language file to moodle_lang['blockname']
    //
    get_string:function(identifier, module, a){
        if (typeof(moodle_lang[module])!='undefined' && moodle_lang[module]!=null){
            if (typeof(moodle_lang[module][identifier])!='undefined' && moodle_lang[module][identifier]!=null){
                var output=moodle_lang[module][identifier];
                output=output.replace(/\$a/g, a);
            }
            return (output);
        } else {
            return ('['+identifier+']');
        }
    }
}

//
// Export functions
//
if (GTLib.ExportFuncs){
    moodle_get_string=GTLib.Moodle.get_string;
}