//
//Mochikit functions required for previous mochikit version of block
//

/*
MochiKit is dual-licensed software.  It is available under the terms of the
MIT License, or the Academic Free License version 2.1.  The full text of
each license is included below.

MIT License
===========

Copyright (c) 2005 Bob Ippolito.  All rights reserved.
See mochi-licence.txt
*/



var isUndefinedOrNull=function(){for(var i=0;i<arguments.length;i++){var o=arguments[i];if(!(typeof(o)=='undefined'||o===null)){return false}}return true};