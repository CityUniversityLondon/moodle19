// If old Yahoo library is being used then force it to be more compatible with latest yahoo library
if(typeof(YAHOO.lang)=='undefined'){YAHOO.lang={extend:YAHOO.extend,merge:function(){var o={},a=arguments,i;for(i=0;i<a.length;i=i+1){for(var j in a[i]){o[j]=a[i][j];}}
return o;}}}
if(typeof(YAHOO.util.Dom)=='undefined'){YAHOO.util.Dom=YAHOO.Dom;}
if(typeof(YAHOO.util.Dom.getDocumentScrollLeft)=='undefined'){YAHOO.util.Dom.getDocumentScrollLeft=function(doc){doc=doc||document;return Math.max(doc.documentElement.scrollLeft,doc.body.scrollLeft);}}
if(typeof(YAHOO.util.Dom.getDocumentScrollTop)=='undefined'){YAHOO.util.Dom.getDocumentScrollTop=function(doc){doc=doc||document;return Math.max(doc.documentElement.scrollTop,doc.body.scrollTop);}}
if(typeof(YAHOO.register)=='undefined'){YAHOO.register=function(name,mainClass,data){};}
