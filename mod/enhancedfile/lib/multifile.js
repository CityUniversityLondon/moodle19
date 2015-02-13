var get_string=GTLib.Moodle.get_string;

var MODE_FLASH=1000;
var MODE_HTML5=1001;
var THUMB_ON_HOVER=2000;
var THUMB_ON_SELECT=2001;

$Y=YAHOO;
$YU=YAHOO.util;
$YUE=YAHOO.util.Event;
$YUD=YAHOO.util.Dom;

/**
 * HTML5 file uploader
 */
var HTML5FileUpload=function(file, cbprogress, cbcomplete, cbresponse){
    var me={
        file:'',
        cbprogress:null,
        cbcomplete:null,
        cbresponse:null,
        

        constructor:function(file, cbprogress, cbcomplete, cbresponse){

            me.cbprogress=cbprogress;
            me.cbcomplete=cbcomplete;
            me.cbresponse=cbresponse;

            var customname=file.customname ? file.customname : file.name;

            var boundary = "xxxxxxxxx";

            var xhr = new XMLHttpRequest();
            me.xhr = xhr;

            me.file=file;

            me.xhr.upload.addEventListener("progress", function(e) {
                  if (e.lengthComputable) {
                      me.updateProgress({bytesLoaded:e.loaded, bytesTotal:e.total, id:me.file.id});
                  }
                }, false);

            xhr.upload.addEventListener("load", function(e){
                    me.updateProgress({bytesLoaded:e.total, bytesTotal:e.total, id:me.file.id});
                 }, false);


            var url=file_wwwroot+'/mod/enhancedfile/upload.php?';
            url+='courseid='+$GTF('courseid','', 'mform1')+'&';
            url+='section='+$GTF('section','', 'mform1')+'&';
            url+='sesskey='+$GTF('sesskey','', 'mform1')+'&';
            url+='directory='+$GTF('directory','', 'mform1')+'&';
            url+='visible='+$GTF('visible','', 'mform1')+'&'; // enhancement by Amanda Doughty December 2010
            url+='resourcetype='+$GTF('resourcetype','','mform1')+'&';
            url+='add=file&';
            url+='customname='+encodeURIComponent(customname)+'&';
            url+='_submittype=ajax';

//            xhr.multipart=true; // do not enable this or it wont work!!!!!
            xhr.open("POST", url, true);

            

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if ((xhr.status >= 200 && xhr.status <= 200) || xhr.status == 304) {
                        if (xhr.status==200){
                            if (xhr.responseText != "") {
                                me.file.data=xhr.responseText;
                                me.fileResponse();
                            }
                        }
                    }
                }
            }

            if (GTLib.Browser.firefox && GTLib.Browser.details().versionnumber<4){
                // old way, Firefox 3.6 and lower
                xhr.setRequestHeader("Content-Type", 'multipart/form-data; charset=UTF-8; boundary="'+boundary+'"'); // simulate a file MIME POST request.
                var body = "--" + boundary + "\r\n";            
                body += "Content-Disposition: form-data; name='Filedata'; filename='" + encodeURIComponent(file.name) + "'\r\n";
                body += "Content-Type: application/octet-stream; charset=UTF-8\r\n\r\n";
                body += file.getAsBinary() + "\r\n";
                body += "--" + boundary + "--";
                xhr.setRequestHeader("Content-Length", body.length);
                xhr.sendAsBinary(body);
            } else {
                // new way, Firefox 4 and above

                // it took me ages to find this page that helped me a lot:
                // hacks.mozilla.org/2010/07/firefox-4-formdata-and-the-new-file-url-object/
              
                var fd = new FormData();
                fd.append("Filedata", file);
                //xhr.setRequestHeader('X-File-Name', encodeURIComponent(file.name));
                //xhr.setRequestHeader('X-File-Size', file.size);
                xhr.send(fd);
            }
            return true;

        },

        updateProgress:function(progobj){
            if (progobj.bytesLoaded==progobj.bytesTotal){
                me.updateComplete();
            } else {
                if (typeof(me.cbprogress)=='function'){
                    me.cbprogress(progobj);
                }
            }
        },

        updateComplete:function(){
            if (typeof(me.cbcomplete)=='function'){
                me.cbcomplete(me.file);
            }
        },

        fileResponse:function(){
            if (typeof(me.cbresponse)=='function'){
                me.cbresponse(me.file);
            }
        }


    };
    me.constructor(file, cbprogress, cbcomplete, cbresponse);
    return (me);
}

/**
 * Multiple file handler
 */
var multifile=function(){


    // class object
    var me={

        fileList:[],
        fileIdHash:[],
        filesById:[],
        thumbsById:[], // html 5 image thumbs
        thumbwidth:160,
        thumbheight:120,
        upFileIds:[],
        completedIds:[], // completed file uploads
        failedIds:[], // failed file uploads
        failedMsgs:[], // messages for failed file uploads
        tooBig:[], // files that are too big to upload
        uploader:null,
        simUploads:1,
        numUploading:0,
        progbarheight:"16px",
        progbarwidth:"180px",
        allUploaded:false,
        uploading:false,
        reqflashver:'9.0.45', // minimum required flash version
        mode:MODE_FLASH, // default mode
        flashupkey:'', // prove flash user agent is ok to upload files
        thumbimages:true, // thumbnail images
        thumbimagemaxsize:1024, // max size of image in kbytes to be thumbed
        thumbmode:THUMB_ON_HOVER,
        thumbhoverid:null,
        mouse_xy:null,

        /**
         * constructor
         */
        constructor:function(){
            if (!this.checkGTLibVersion()){
                return;
            }
            // Apply click events
            $YUE.addListener(window, 'load', function(){me.preinit()});
            $YUE.addListener(window, 'mousemove', function(e){me.setmouse_xy(e)});
        },

        setmouse_xy:function(e){
            me.mouse_xy=$YUE.getXY(e);
        },

        /**
         * pre initialisation - check that user has flash and that version is
         * greater than or equal to me.reqflashver
         */
        preinit:function(e){
		
			if (file_html5uploads){ // only try html5 upload method if enabled in settings
				try {
					var fr=new FileReader(); // this is just here to test HTML file API
					me.mode=MODE_HTML5;
					me.init();
				} catch(e){
					me.mode=MODE_FLASH;
				}
			}
			if (me.mode==MODE_FLASH){
                var FlashDetect=YAHOO.util.FlashDetect;
                var warnstr='';
                var warntitle='';
                var flashverarr=me.reqflashver.split('.');
                if (!FlashDetect.installed){
                    warntitle='Non-HTML5 browser / Adobe Flash Not Installed!'; // @todo language string
                    warnstr='<h2 class="warning alert">'+warntitle+'</h2>';
                } else if (!FlashDetect.versionAtLeast(flashverarr[0], flashverarr[1], flashverarr[2])){
                    warntitle='Adobe Flash Requires Upgrade!'; // @todo language string
                    warnstr='<h2 class="warning alert">'+warntitle+'</h2>';
                    warnstr+='<p>You are using '+FlashDetect.raw+'</p>'; // @todo language string
                    warnstr+='<p>Required version is '+me.reqflashver+' or higher.</p>'; // @todo language string
                }
                // if no warnings then initialise multiple file upload capability
                if (warnstr==''){
                    me.init();
                } else {
                    warnstr+='<p>For easy uploading of multiple files, it is recommended that you use a HTML5 ready browser (e.g. Firefox 3.6+)</p>'; // @todo language string
                    warnstr+='<p>You can download Firefox from <a href="http://www.mozilla.com">http://www.mozilla.com</a></p>'; // @todo language string
                    warnstr+='<p>Alternatively Flash '+me.reqflashver+' is recommended for a better uploading experience (enables easy multiple file uploads)</p>'; // @todo language string
                    warnstr+='<p>Please download and install flash from:</p><p><a href="http://get.adobe.com/flashplayer/">http://get.adobe.com/flashplayer/</a></p>'; // @todo language string
                    // do not initialise multiple file upload capability
                    // warn that user does not have flash installed.
                    var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.dg.CloseDialog();}}];
                    me.dg=new Dialog(warntitle,warnstr, {buttons:buttons, w:400,h:280, lightBox:true});
                    me.dg.Init();
                }
            }


        },

        /**
         * initialise
         */
        init:function(){
            me.fileList=[];
            me.completedIds=[];
            me.uploader=null;
            me.fileIdHash=[];
            me.filesById=[];
            me.upFileIds=[];
            me.applyClickEvents();
            var file=$YUD.get('id_file');

            if (me.mode==MODE_HTML5){
                // add hidden image preview container
                // this is used to store all previews of images
                // this means that thumbs can be added and re-added much faster
                if (!$YUD.get('filepreviewcontainer')){
                    // note that the preview container is hidden off screen - I don't trust how some browser deal with non displayed areas of the screen (i.e. images wont actually be loaded)
                    var prevcont=createEl('div',{id:'filepreviewcontainer', style:'display:block; height:1px; width:1px; overflow:hidden; position:absolute; top:-1px; left:-1px'});
                    //var prevcont=createEl('div',{id:'filepreviewcontainer', style:'display:block; height:320px; width:512px; overflow:scroll; position:absolute; top:32px; left:32px'});
                    document.body.appendChild(prevcont);
                }
            }

            // create file queue container
            if (!$YUD.get('filequeuecontainer')){
                // weird - if you don't set display to block and position to relative then the swf fails to work as expected!
                var fqcont=createEl('div',{id:'filequeuecontainer', style:'display:block; position:relative', 'class':'yui-skin-sam mimetypes'});
                $YUD.get('fileelements').appendChild(fqcont);
            }

            // add uploader container
            var upcont=createEl('div',{id:'upload_container', style:'position:relative; display:block'});
            file.parentNode.appendChild(upcont);
            upcont.appendChild(file);

            // remove unnecessary html file upload fields
            me.removeUnusedUploads();

            // add yui skin class to body
            $YUD.addClass  (document.body , 'yui-skin-sam' );

            // move file management button next to directory selector
            var manfanc=$YUD.get('managefiles');
            manfanc.parentNode.parentNode.style.display="none";
            var mfbut=createEl('input', {id:'managefilesbutton'});
            mfbut.type='button';
            mfbut.value=manfanc.firstChild.data;
            mfbut.title=manfanc.firstChild.data;
            $YUE.addListener(mfbut, 'click', me.dboxFileManager);
            $YUD.get('id_directory').parentNode.appendChild(mfbut);

            if (me.mode==MODE_FLASH){
                // remove file browser input and replace with button
                var nfb=createEl('button', {'id':'id_file'});
                nfb.innerHTML=get_string('browse', 'enhancedfile');
                file.parentNode.appendChild(nfb);
                file.style.display='none';
                file=nfb;
                // ad uploader overlay (for swf)
                var overlay=createEl('div', {id:'uploader_overlay', style:'position:absolute; top:0; left:0; z-index:2; opacity:0;filter:alpha(opacity=0);'});
                upcont.appendChild(overlay);

                var region=$YUD.getRegion(file);
                var width=region.right-region.left;
                var height=region.bottom-region.top;

                $YUD.setStyle(overlay, 'width', width + "px");
                $YUD.setStyle(overlay, 'height', height + "px");

                //YAHOO.widget.Uploader.SWFURL = file_wwwroot+"/lib/yui/uploader/assets/uploader.swf";
                $Y.widget.Uploader.SWFURL = file_wwwroot+"/mod/enhancedfile/extlib/uploader/assets/uploader.swf";

                me.uploader = new $Y.widget.Uploader( "uploader_overlay" );
                me.uploader.addListener('contentReady', me.handleContentReady);
                me.uploader.addListener('fileSelect', me.onFileSelectFlash);
                me.uploader.addListener('uploadStart', me.onUploadStart);
                me.uploader.addListener('uploadProgress', me.onUploadProgress);
                me.uploader.addListener('uploadCancel', me.onUploadCancel);
                me.uploader.addListener('uploadComplete', me.onUploadComplete);
                me.uploader.addListener('uploadCompleteData', me.onUploadResponse);
                me.uploader.addListener('uploadError', me.onUploadError);
                me.uploader.addListener('click', me.handleClick);
                me.uploader.addListener('mouseDown', me.handleMouseDown);
                me.uploader.addListener('mouseUp', me.handleMouseUp);

            } else if (me.mode==MODE_HTML5){
                // ad html5 upload input
                var filesel=createEl('input', {'type':'file', id:'id_fileselector', multiple:true});
                filesel.type='file';
                file.parentNode.appendChild(filesel);
                file.style.display='none';
                $YUE.addListener(filesel, 'change', function(){me.onFileSelectHTML5(filesel)});
            }

            // set correct add file label
            $YUD.get('lableaddfile').innerHTML=get_string('modulenameadd', 'enhancedfile');

        },

        /**
         * remove unused html upload fields
         */
        removeUnusedUploads:function(){
          for (var f=2; f<=5; f++){
              var upel=$YUD.get('id_file'+f);
              upel.parentNode.parentNode.parentNode.removeChild(upel.parentNode.parentNode);
          }
        },


        handleContentReady:function() {
            // Allows the uploader to send log messages to trace, as well as to YAHOO.log
            me.uploader.setAllowLogging(true);

            // Allows multiple file selection in "Browse" dialog.
            me.uploader.setAllowMultipleFiles(true);

            // New set of file filters.
            /*
            var ff = new Array({description:"Images", extensions:"*.jpg;*.png;*.gif"},
                               {description:"Videos", extensions:"*.avi;*.mov;*.mpg"});
            */

            var ff = new Array({description:"Files", extensions:"*.*"});

            // Apply new set of file filters to the uploader.
            me.uploader.setFileFilters(ff);
        },

        handleMouseDown:function () {
        },

        handleMouseUp:function () {
        },

        handleClick:function () {
        },


        onFileSelectFlash:function(event) {
            var file={};
            var f=0;
            if('fileList' in event && event.fileList != null) {
                // remove duplicates in the fileList (have same name)
                var uniqueList=[];
                for (f in event.fileList){
                    file = event.fileList[f];
                    file.customname=file.name; // add custom name property so that file can be renamed
                    var alreadyinlist=false;
                    for (var cf in me.fileList){
                        var checkfile = me.fileList[cf];
                        if (checkfile.name==file.name && checkfile.id!=file.id){
                            alreadyinlist=true;
                            break;
                        }
                    }
                    if (alreadyinlist){
                        //alert (file.name+' already in list '+file.id);
                        me.uploader.removeFile(file.id);
                    } else {
                        uniqueList.push(file);
                    }
                }
                // set file list to unqique list
                me.fileList = uniqueList;
                // add files to filesById
                for (f in me.fileList){
                    file = me.fileList[f];
                    me.filesById[file.id]=file;
                }
                // create data table
                me.createFilesDataTable(me.fileList, true);
            }
        },

        onFileSelectHTML5:function(filesel){
            var files = filesel.files;
            for (var f=0; f<files.length; f++){
                var file=files[f];
                
                var alreadyinlist=false;
                for (var cf in me.fileList){
                    var checkfile = me.fileList[cf];
                    file.id=me.makeFileId(file);
                    if (checkfile.name==file.name && checkfile.id==file.id){ // html5 does not have an id  - so trust filename and size as id
                        alreadyinlist=true;
                        break;
                    }
                }
                if (!alreadyinlist){
                    file.customname=file.name; // add custom name property so that file can be renamed
                    file.id=me.makeFileId(file);
                    me.fileList.push(file);
                    me.filesById[file.id]=file;
                }
            }
            // create data table
            me.createFilesDataTable(me.fileList, true);
        },

        /**
         * makes a file id from a file entry (needed for html5)
         **/
        makeFileId:function(file){
            var fileurl=file.url || 'nourl';
            var fileid=escape(file.name+'_'+fileurl+'_'+file.size);
            fileid=fileid.replace(/%/g, '*p*');
            return (fileid);
        },

        createFilesDataTable:function(entries, includetoobig) {
            me.fileIdHash = {};
            var dataArr = [];
            var entry = {};
            var i=0;
            var imageType = /image.*/;
            for(i in entries) {
               entry = entries[i];
               if (entry.size>file_maxsize){
                   entry["progress"] = "<div style='text-align:center'><div id='progbar_"+entry.id+"' style='margin:0 auto; width:"+me.progbarwidth+"'; class='error'>Exceeds max size of "+file_maxsizetxt+"</div></div>";
                   me.tooBig[entry.id]=entry;
               } else {
                   //entry["actions"] = "<a class='action_cancel' id='upload_cancel_"+entry.id+"' href='#' title='cancel upload' onclick='return(false;)'>&nbsp</a>";
                   entry["progress"] = "<div style='text-align:center'><div id='progbar_"+entry.id+"' class='progbar' style='margin:0 auto; height:"+me.progbarheight+";width:"+me.progbarwidth+";'></div></div>";
               }
               if (entry.size<=file_maxsize || includetoobig){
                   dataArr.unshift(entry);
               }
            }

            // don't create data table if there aren't any files to upload
            if (dataArr.length==0){
                $YUD.get('filequeuecontainer').innerHTML='';
                return;
            }

            for (var j = 0; j < dataArr.length; j++) {
                me.fileIdHash[dataArr[j].id] = j;
            }
           
            var myColumnDefs = [
                {key:"customname", label: get_string('filename', 'enhancedfile'), sortable:true, resizeable:true, width:'150px', formatter:me.formatFileName, editor: new YAHOO.widget.TextboxCellEditor({validator: me.editCustomName})},
                {key:"size", label: get_string('size', 'enhancedfile'), sortable:true, resizeable:true, width:'35px', formatter:me.formatNiceSize},
                {key:"progress", label: get_string('progress', 'enhancedfile'), sortable:false, width:me.progbarwidth}
            ];

            me.myDataSource = new YAHOO.util.DataSource(dataArr);
            me.myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
            me.myDataSource.responseSchema = {
                  fields: ["id","customname","name","created","modified","type", "size", "progress"]
            };

            me.singleSelectDataTable = new YAHOO.widget.DataTable("filequeuecontainer",
                   myColumnDefs, me.myDataSource, {
                       //caption:"Files To Upload",
                        //sortedBy:{key:"name", dir:"asc"},
                        selectionMode:"single"
                   });


            if (me.thumbimages && me.thumbmode==THUMB_ON_SELECT){
                // register thumb nails for images
                me.registerThumbs(entries, imageType);
            }

            // sorting does not work unless it is done via the sortColumn method - don't know why!
            me.singleSelectDataTable.subscribe("initEvent", function(){
                //me.singleSelectDataTable.set("sortedBy", {key:"name", dir:"desc"});
                me.singleSelectDataTable.set("sortedBy", null);

                // sort table in ascending order
                var col=me.singleSelectDataTable.getColumn ("name");
                me.singleSelectDataTable.sortColumn  (col , YAHOO.widget.DataTable.CLASS_ASC  );
            });

            // need to re-add thumbs every time table is rendered or re-rendered (e.g. column is sorted)
            me.singleSelectDataTable.subscribe("renderEvent", function(){me.addThumbs()});

            // make table editable
            me.singleSelectDataTable.subscribe("cellClickEvent", me.singleSelectDataTable.onEventShowCellEditor);

        },

        registerThumbs:function(entries, imageType, refresh){
             var entry=null;
             var i=null;
             refresh=typeof(refresh)!='undefined' ? refresh : false;
             if (me.mode!=MODE_HTML5){
                 return;
             }

             // initialise thumbs array
             for(i in entries) {
                entry = entries[i];
                if (entry.type.match(imageType)) {
                    me.thumbsById[entry.id]=null;
                }
             }

             for(i in entries) {
                entry = entries[i];
                // only include image if it is of a supported type and its size in kilobytes is < me.thumbimagemaxsize
                if (entry.type.match(imageType) && (entry.size/1024)<=me.thumbimagemaxsize) {
                    // only load up image thumb if its not already loaded or a refresh has been forced.
                    if (refresh || (!me.thumbsById[entry.id] || me.thumbsById[entry.id]==null)){
                        var reader = new FileReader();
                        reader.onload = (function(entryid) {  return function(e) {me.registerThumbLoaded(entryid, e)}})(entry.id);
                        reader.readAsDataURL(entry);
                    }
                }
             }
        },

        registerThumbLoaded:function(entryid, e){
            var img=$YUD.get('previewimg_'+entryid);
            if (img){
                me.thumbsById[entryid]=img;
            } else {
                img=createEl('img');
                var prevcont=$YUD.get('filepreviewcontainer'); // this is used to store hidden preview versions of images
                img.classList.add("obj");
                img.file = me.filesById[entryid];
                img.id='previewimg_'+entryid;
                img.src=e.target.result;
                prevcont.appendChild(img);
                me.thumbsById[entryid]=img;
            }
            if (me.thumbmode==THUMB_ON_SELECT && me.allThumbsLoaded()){
                me.singleSelectDataTable.render();
            } else if (me.thumbmode==THUMB_ON_HOVER){
                var thumbimg=$YUD.get('hoverthumbimg');
                   if (thumbimg){
                    var parentNode=thumbimg.parentNode;
                    parentNode.removeChild(thumbimg);
                    thumbimg=me.thumbsById[entryid].cloneNode(true);
                    thumbimg.id='hoverthumbimg';
                    me.thumbImage(thumbimg);
                    parentNode.appendChild(thumbimg);
                }
            }
        },

        /**
         * return true if all thumbs are loaded
         */
        allThumbsLoaded:function(){
            //var ids='';
            for (var f in me.thumbsById){
                //ids+=' - '+f;
                if (me.thumbsById[f]==null){
                   return (false); // thumb does not exist
                }
            }
            //alert (ids);
            return (true);
        },


        addThumbs:function(){
            if (me.mode!=MODE_HTML5){
                return;
            }
            for (var id in me.thumbsById){
                me.addThumb(id);

            }
        },

        addThumb:function(id){
            var preview=$YUD.get('filethumb_'+id);

            if (!preview){
                return; // can't add thumb because no preview available
            }

            if (me.thumbsById[id]==null){
                return;
            }

            preview.innerHTML='';
            preview.style.textAlign='center';
            preview.style.width=me.thumbwidth+'px';
            preview.style.height=me.thumbheight+'px';

            /*
            var img=$YUD.get('filethumbimg_'+id);
            if(!img){
                img = document.createElement("img");
            }
            img.style.visibility='hidden';
            img.id='filethumbimg_'+id;
            img.src=me.thumbsById[id].src;
            preview.appendChild(img);
            me.thumbimgOnload(img);
            */

            var img=me.thumbsById[id].cloneNode(true);
            img.style.visibility='hidden';
            img.id='filethumbimg_'+id;
            preview.appendChild(img);
            me.thumbimgOnload(img);
        },


        thumbimgOnload:function(img){
            img.onload=function(){me.thumbImage(img)};
        },

        thumbImage:function(img){
            // Resize thumb without stretching
            var theight=0;
            var twidth=0;
            var imgwidth=parseInt(img.width);
            var imgheight=parseInt(img.height);
            if (imgwidth>imgheight){
                // set height first, then width
                theight=me.thumbheight;
                twidth=me.thumbwidth;
            } else {
                // set width first, then height
                theight=me.thumbheight;
                twidth=imgwidth*(me.thumbheight/imgheight);
            }
            img.style.width=twidth+'px';
            img.style.height=theight+'px';
            img.className='filethumb';
            if (me.thumbwidth!=twidth){
                img.style.marginLeft=((me.thumbwidth-twidth)/2)+'px';
                img.style.marginRight=((me.thumbwidth-twidth)/2)+'px';
            }
            img.style.visibility='visible';
        },

        /**
         * get nice size from bytes - (human readable)
         */
        niceSize:function(bytes){
               var unit='B';
               var nsize=bytes;

            if (nsize>=1024){
                nsize=nsize/1024;
                unit='KB';
            }

            if (nsize>=1024){
                nsize=nsize/1024;
                unit='MB';
            }

            if (nsize>=1024){
                nsize=nsize/1024;
                unit='GB';
            }

            if (nsize>=1024){
                nsize=nsize/1024;
                unit='TB';
            }

            return((Math.round(nsize*10)/10)+unit);
        },

        /**
         * data grid custom name cell validator
         */
        editCustomName:function(inputValue, currentValue, editorInstance){            
            var oRecord=editorInstance.getRecord( );
            var id=oRecord.getData('id');
            var fileel=$YUD.get('file_'+id);
            fileel.parentNode.removeChild(fileel);
            if (inputValue==''){
                return;
            }
            for (var f in me.fileList){
                if (me.fileList[f].id==id){
                    me.fileList[f].customname=inputValue;
                }
            }
            me.filesById[id].customname=inputValue;
            return (inputValue);
        },

        /**
         * data grid formatter - make cell bytes data human readable
         */
        formatNiceSize:function(elCell, oRecord, oColumn, oData){
            var nsize=me.niceSize(oData);
            elCell.innerHTML=nsize;
        },

        /**
         * get file extension from file
         */
        fileExt:function(filename){
            var dotpos = filename.lastIndexOf('.');
            if( dotpos == -1 ) {return '';}
            return (filename.substr((dotpos+1),filename.length).toLowerCase());
        },

        /**
         * data grid formatter - apply css for file extension
         */
        formatFileName:function(elCell, oRecord, oColumn, oData){
            //var ext=me.fileExt(oData);
            var ext=me.fileExt(oRecord.getData('name'));
            ext=ext.toLowerCase();
            var id=oRecord.getData('id');            
            var fileHasThumb=false;
            for (var thumbid in me.thumbsById){
                if (thumbid==id){
                    fileHasThumb=true;
                    break;
                }
            }


            /*
            if (fileHasThumb && me.thumbmode==THUMB_ON_SELECT){
                imgThumb='<div class="filethumbcont" id="filethumb_'+id+'" style="text-align:center; width:'+me.thumbwidth+'px; height:'+me.thumbheight+'px;">'+'<div style="background-color:#fff; width:100%; height:100%"><img style="margin-top:'+((me.thumbheight/2)-16)+'px; width:32px; height:32px" src="'+file_wwwroot+'/mod/enhancedfile/pix/loader_big.gif" alt="loading thumb image"></img></div></div>';
            }
            var filehtml='<div id="file_'+id+'" class="file ext_'+ext+'">'+imgThumb+'<div id="filetxt_'+id+'">'+oData+'</div></div>';
            */

            var filecont=createEl('div');
            filecont.id='file_'+id;
            filecont.className='file ext_'+ext;
            if (fileHasThumb && me.thumbmode==THUMB_ON_SELECT){
                var imgThumb=createEl('div', {style:'text-align:center; width:'+me.thumbwidth+'px; height:'+me.thumbheight+'px;'});
                imgThumb.id='filethumb_'+id;                
                imgThumb.innerHTML='<div style="background-color:#fff; width:100%; height:100%"><img style="margin-top:'+((me.thumbheight/2)-16)+'px; width:32px; height:32px" src="'+file_wwwroot+'/mod/enhancedfile/pix/loader_big.gif" alt="loading thumb image"></img></div>';
                filecont.appendChild(imgThumb);
            }
            var filetext=createEl('div');
            filetext.id='filetxt_'+id;
            filetext.innerHTML=oData;
            filecont.appendChild(filetext);
            elCell.appendChild(filecont);
            if (me.thumbmode==THUMB_ON_HOVER){                
                $YUE.addListener(elCell, 'mouseover', function(){me.thumbOnHover(id, ext)});
                $YUE.addListener(elCell, 'mouseout', function(){me.thumbOnMouseOut(id)});
            }
        },

        thumbOnHover:function(id, ext){
          if (me.mode!=MODE_HTML5){
             return;
          }
          ext=ext.toLowerCase();
          if (ext=='jpg' || ext=='png' || ext=='gif'){
              me.thumbhoverid=id;
              window.setTimeout(function(){me.showThumbHover(id)}, 1000);
          } else {
              window.setTimeout(function(){me.hideThumbMouseOut()}, 1000);
          }
        },

        showThumbHover:function(id){
            if (!me.mode==MODE_HTML5){
                return;
            }
            if (id==null || me.thumbhoverid!=id){
                return; // no point showing now
            }
            var filecont=$GT('file_'+id);
            var imgThumb=$GT('hoverthumb');            
            if (!imgThumb){
                imgThumb=createEl('div', {style:'text-align:center; width:'+me.thumbwidth+'px; height:'+me.thumbheight+'px;'});
                imgThumb.id='hoverthumb';
            }            
            imgThumb.innerHTML='<div style="background-color:#fff; width:100%; height:100%"><img id="hoverthumbimg" style="margin-top:'+((me.thumbheight/2)-16)+'px; width:32px; height:32px" src="'+file_wwwroot+'/mod/enhancedfile/pix/loader_big.gif" alt="loading thumb image"></img></div>';
            imgThumb.style.display='block';
            if (me.thumbmode==THUMB_ON_HOVER){
                imgThumb.style.position='absolute';
                imgThumb.style.left=(me.mouse_xy[0]+20)+'px';
                imgThumb.style.top=(me.mouse_xy[1]+20)+'px';
            }
            //filecont.appendChild(imgThumb);
            document.body.appendChild(imgThumb);
            
            $YUE.addListener(window, 'mousemove', function(e){
                var xy=$YUE.getXY(e);
                imgThumb.style.left=(xy[0]+20)+"px";
                imgThumb.style.top=(xy[1]+20)+"px"});

            var reader = new FileReader();
            reader.onload = (function(entryid) {  return function(e) {me.registerThumbLoaded(entryid, e)}})(id);
            reader.readAsDataURL(me.filesById[id]);
        },

        thumbOnMouseOut:function(id){
            me.thumbhoverid=null;
            window.setTimeout(function(){me.hideThumbMouseOut(id)}, 500);
        },

        hideThumbMouseOut:function(id){
            /*
            if (id!=null && me.thumbhoverid==id){
                // cancel, now over same row
                return;
            }
            */
            var imgThumb=$GT('hoverthumb');
            if (!imgThumb){
                return;
            }
            imgThumb.innerHTML='';
            imgThumb.style.display='none';
        },
/*CMDL*/
        initUpload:function(submitbutton){
        //initUpload:function(){
/**/

            var removefiles=[];
            me.submitbutton = submitbutton;

            if (me.fileList==null) {
                return (false);
            }

            // remove files that are too big
            for (var f in me.fileList){
                var entry=me.fileList[f];
                if (entry.size>file_maxsize){
                    if (me.mode==MODE_FLASH){
                        me.uploader.removeFile(entry["id"]);
                    }
                    // add file to removal list
                    removefiles.push(me.fileList[f]);
                } else {
                    me.upFileIds.push(entry["id"]);
                }
            }

            // remove files in removal list
            me.remove_files(removefiles);

            var anytoobig=me.warnTooBig();

            if (anytoobig){
                // reset tooBig array and exit
                me.tooBig=[];
                return (false);
            } else {
                me.REQ_check_resources();
            }

        },

        REQ_check_resources:function(){
            var uri=file_wwwroot+'/mod/enhancedfile/ajax/checkresources.php';
            var postData='filelist=';
            var fileListStr='';

            for (var f in me.fileList){
                var entry=me.fileList[f];
                if (fileListStr!=''){
                    fileListStr+='~FDEL~'; // file delemeter
                }
                fileListStr+=encodeURIComponent(entry.name)+'~FPDEL~'+entry.id; // ~FPDEL~ file info delemeter
            }
            postData+=fileListStr;
            var updir=$GTF('id_directory');
            if (updir=='~ROOT~'){
                postData+='&updir='+updir;
            } else {
                postData+='&updir='+encodeURIComponent(updir);
            }
            postData+='&course='+$GTF('courseid','','mform1');
            postData+='&section='+$GTF('section','','mform1');
            postData+='&visible='+$GTF('visible','','mform1');  // enhancement by Amanda Doughty December 2010
            postData+='&method=';
            if (me.mode==MODE_HTML5){
                postData+='html5';
            } else {
                postData+='flash';
            }

            AJAXPost(uri, postData, {success:function(o){me.REC_check_resources(o)}, failure:function(o){me.FAIL_check_resources(o)}});
        },

        REC_check_resources:function(o){
            try {
                // get object from json
                var response = $Y.lang.JSON.parse(o.responseText);
            }
            catch (e) {
                // just upload if nothing came back
                me.selectUploadMethod();
                return;
            }

            if (typeof(response.warnoverwrites)!='undefined'){
                // populate array of warning results
                var warnres=[];
                for (var w in response.warnoverwrites){
                    var file=me.filesById[w];
                    if (typeof(file)=='undefined'){
                        file=me.filesById[escape(w)];
                    }
                    var acthtml='<input type="radio" value="skip" name="conflict_'+w+'" checked="checked">'+get_string('skip','enhancedfile')+'</input>';
                    acthtml+='<input type="radio" value="replace" name="conflict_'+w+'">'+get_string('replace','enhancedfile')+'</input>';
                    warnres.push({file:file.name, action:acthtml});
                }
                if (warnres.length==0){
                    return;
                }

                // intialise dialog to resolve conflicts
                var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.removeSkipFiles(response.warnoverwrites);}}];
                me.dg=new Dialog(get_string('resolveconflicts','enhancedfile'),'<div class="warning alert">'+get_string('resolveconflictsact', 'enhancedfile')+'</div><br /><form name="file_overwrites"><div id="file_overwrites_dtab"></div></form>', {buttons:buttons, w:500,h:450, lightBox:true});
                me.dg.Init();

                var dsource = new $YU.LocalDataSource({
                    found: warnres.length,
                    total: warnres.length,
                    results: warnres
                });
                dsource.responseType = $YU.LocalDataSource.TYPE_JSON;
                dsource.responseSchema = {
                    resultsList:'results',
                    fields:["file", "action"],
                    metaFields : {
                        totalRecords : "total"
                   }
                };

                var coldefs = [
                    {key:"file", label:get_string('file', 'enhancedfile'), abbr:get_string('filename', 'enhancedfile'), minWidth:400},
                    {key:"action", label:get_string('action', 'enhancedfile'), abbr:get_string('action', 'enhancedfile'), maxAutoWidth:200, minWidth:180}
                ];

                var dtable = new YAHOO.widget.DataTable("file_overwrites_dtab", coldefs, dsource);
                return;
            }
            // upload or request flash upload keys
            me.selectUploadMethod();
        },

        selectUploadMethod:function(){
            if (me.mode!=MODE_FLASH){
                me.doUpload();
            } else {
                me.REQ_flash_up_keys();
            }
        },

        FAIL_check_resources:function(o){
            alert ('failed to check resources!'); // @todo - use language string
        },

        REQ_flash_up_keys:function(o){
            var uri=file_wwwroot+'/mod/enhancedfile/ajax/makeflashuploadkeys.php';
            var postData='filelist=';
            var fileListStr='';
            for (var f in me.fileList){
                var entry=me.fileList[f];
                if (fileListStr!=''){
                    fileListStr+='~FDEL~';
                }
                fileListStr+=encodeURIComponent(entry.name)+'~FPDEL~'+entry.id;
            }
            
            if (fileListStr==''){
                return; // don't request flash upload keys if there are no valid files!
            }
            
            postData+=fileListStr;
            updir=$GTF('id_directory');
            if (updir=='~ROOT~'){
                postData+='&updir='+updir;
            } else {
                postData+='&updir='+escape(updir);
            }
            postData+='&course='+$GTF('courseid','','mform1');
            postData+='&section='+$GTF('section','','mform1');
            postData+='&sesskey='+file_sesskey;
            postData+='&userid='+$GTF('userid','','mform1');
            postData+='&visible='+$GTF('visible','','mform1');  // enhancement by Amanda Doughty December 2010
            AJAXPost(uri, postData, {success:function(o){me.REC_flash_up_keys(o)}, failure:function(o){me.FAIL_flash_up_keys(o)}});
        },

        REC_flash_up_keys:function(o){
            var dg={};
            var buttons=[];
            try {
                // get object from json
                var response = YAHOO.lang.JSON.parse(o.responseText);

                // warn security check failure
                if (!response.security_pass){
                    // get list of failed security checks
                    var sctxt='<ul>';
                    for (var sc in response.security_checks){
                        if (response.security_checks[sc].success==false){
                            var chktype=response.security_checks[sc].checktype;
                            var chktext=get_string(chktype, 'enhancedfile');
                            if (chktext==''){
                                chktext=chktype;
                            }
                            sctxt+='<li>'+chktext+'</li>';
                        }
                    }
                    sctxt+='</ul>';
                    dg={};
                    buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){dg.CloseDialog();}}];
                    dg=new Dialog(get_string('notauthorised','enhancedfile'),'<div class="warning alert">'+get_string('securitychecksfailed','enhancedfile')+'</div>'+sctxt, {buttons:buttons, w:350,h:250, lightBox:true});
                    dg.Init();
                    return (false);
                }

                // warn critical errors
                if (typeof(response.criticalerrors)!=='undefined' && response.criticalerrors.length>0){
                    var cetxt='<ul>';
                    for (var ce in response.criticalerrors){
                        cetxt+='<li>'+response.criticalerrors[ce]+'</li>';
                    }
                    cetxt+='</ul>';
                    dg={};
                    buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){dg.CloseDialog();}}];
                    dg=new Dialog(get_string('criticalerrors','enhancedfile'),'<div class="warning alert">'+get_string('criticalerrorslist','enhancedfile')+'</div>'+cetxt, {buttons:buttons, w:350,h:250, lightBox:true});
                    dg.Init();
                    return (false);
                }

                // set flash upload key
                me.flashupkey=response.data.key;

                // do upload
                me.doUpload();
                return (true);

            }
            catch (e) {
                me.FAIL_flash_up_keys(o);
                return (false);
            }
        },

        FAIL_flash_up_keys:function(o){
            alert ('Failed to set flash upload keys '+o.responseText); // @todo - use language string
        },

        removeSkipFiles:function(conflictfiles){

            var fpos=-1;

            var actions=[];
            for (var f in conflictfiles){
                var action=$GTF("conflict_"+f, '~', 'file_overwrites');
                actions[f]=action;
            }

            me.dg.CloseDialog();

            var removefiles=[];

            for (var f in actions){
                action=actions[f];
                if (action=='skip'){
                    if (me.mode==MODE_FLASH){
                        me.uploader.removeFile(f);
                    }
                    removefiles.push(me.filesById[f]); // files to be removed
                    var pbar=$YUD.get('progbar_'+f);
                    pbar.innerHTML="<div class='skip' style='height:"+me.progbarheight+";width:auto'>"+get_string('skipped','enhancedfile')+"</div>";
                }
            }

            me.remove_files(removefiles);

            me.selectUploadMethod();

        },


        // remove files
        remove_files:function(removefiles){
            var fpos=0;
            for (var r in removefiles){
                var f=removefiles[r]; // file
                // remove files from file list
                fpos=arraySearch(f, me.fileList);
                if (fpos>-1){
                    me.fileList.splice(fpos,1);
                }
                // remove files from filesById
                fpos=arraySearch(f, me.filesById);
                if (fpos>-1){
                    me.filesById.splice(fpos,1);
                }
                // remove files from upload file ids list
                fpos=arraySearch(f.id, me.upFileIds);
                if (fpos>-1){
                    me.upFileIds.splice(fpos,1);
                }
                // remove files from thumbsById
                if (me.thumbsById[f.id]!=null){
                    var tpos=GTLib.Array.intPosKey(f.id, me.thumbsById);
                    me.thumbsById.splice(tpos,1);
                    // remove actual image from preview container
                    var previmg=$YUD.get('previewimg_'+f.id);
                    if (previmg){
                        // remove img
                        previmg.parentNode.removeChild(previmg);
                    }
                }
            }
        },

        doUpload:function(){
            if (me.mode==MODE_FLASH && me.flashupkey==''){
                alert ('Upload key is empty!'); // @todo -translate and use dialog
                return;
            }

            if (me.uploading){
                return; // do nothing - we are uploading already!
            }

            // update file data table
            me.createFilesDataTable(me.fileList);

            //$YUD.get('filequeuecontainer').style.border='1px solid #aaa';

            // warn no files to upload
            if (me.upFileIds.length==0){
                var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.dg.CloseDialog();}}];
                me.dg=new Dialog(get_string('nofilestoupload','enhancedfile'),'<div class="warning alert">'+get_string('nofilesinuploadqueue','enhancedfile')+'</div>', {buttons:buttons, w:300,h:175, lightBox:true});
                me.dg.Init();
                me.uploading=false; // reset uploading mode
                return(false);
            }


            // lightbox the upload queue
            me.lboxUpQueue();

            me.uploading=true; // set uploading to true, because we have started uploading.

            me.doSimUploads();
            
        },

        doSimUploads:function(){
            var numberOfFiles = 0;
            for (var i in me.fileList){numberOfFiles++};

            var simUploads=me.simUploads;
            simUploads = numberOfFiles < simUploads ? numberOfFiles : simUploads;

            var lcheck=0;
            while (me.numUploading<=simUploads && me.allUploaded==false && lcheck<100){
                lcheck++; // loop check
                me.uploadNextFile();
            }
        },

        uploadNextFile:function(){

            if (me.allUploaded){
                return;
            }

            var numberOfFiles = 0;
            for (var i in me.fileList){numberOfFiles++};

            var simUploads=me.simUploads;
            simUploads = numberOfFiles < simUploads ? numberOfFiles : simUploads;

            // CMDL-1606 enhancement by Amanda Doughty December 2011
            //if (me.numUploading>=me.simUploads){
                //return; // already uploading maximum number of files simultaneously
            //}
            // end CMDL-1606

            if (me.mode==MODE_FLASH){
                window.setTimeout(function(){
                    me.uploader.setSimUploadLimit(simUploads); // set upload limit

                    /*
                    me.uploader.uploadAll(file_wwwroot+'/mod/enhancedfile/upload.php', "POST",upvars
                            );

                    */

                    for (var f in me.fileList) {

                        // CMDL-1606 enhancement by Amanda Doughty December 2011
                        if (me.numUploading>=me.simUploads){
                            return; // already uploading maximum number of files simultaneously
                        }
                        // end CMDL-1606

                        var file=me.fileList[f];
                        //me.log("checking upload status for "+file.customname);
                        if (!file.uploading && !file.completed){
                            var customname=file.customname ? file.customname : file.name;
                            /*
                            me.log('courseid = '+$GTF('courseid','', 'mform1'));
                            me.log('section = '+$GTF('section','', 'mform1'));
                            me.log('sesskey = '+$GTF('sesskey','', 'mform1'));
                            me.log('directory = '+$GTF('directory','', 'mform1'));
                            me.log('userid = '+$GTF('userid','', 'mform1'));
                            me.log('resourcetype = '+$GTF('resourcetype','', 'mform1'));
                            me.log('visible = '+$GTF('visible','', 'mform1'));
                            */
                            var upvars={courseid:$GTF('courseid','', 'mform1'),
                                    course:$GTF('courseid','', 'mform1'),
                                    section:$GTF('section','', 'mform1'),
                                    sesskey:$GTF('sesskey','', 'mform1'),
                                    directory:$GTF('directory','', 'mform1'),
                                    userid:$GTF('userid','', 'mform1'),
                                    resourcetype:$GTF('resourcetype','','mform1'),
                                    visible:$GTF('visible','','mform1'),  // enhancement by Amanda Doughty December 2010
                                    flashupkey:me.flashupkey,
                                    customname:encodeURIComponent(customname),
                                    add:'file',
                                    _submittype:'ajax'
                                    };

                            me.fileList[f].uploading=true;
                            me.fileList[f].completed=false;
                            me.numUploading++;
                            me.log('current uploading '+me.numUploading+' '+(me.numUploading>1 ?'files':'file')+' with max '+me.simUploads+' concurrent allowed.');
                            return (me.uploader.upload (file.id, file_wwwroot+'/mod/enhancedfile/upload.php', 'POST', upvars));
                        }
                    }
                 },1000);                
            } else {
                for (var f in me.fileList) {

                    // CMDL-1606 enhancement by Amanda Doughty December 2011
                    if (me.numUploading>=me.simUploads){
                        return; // already uploading maximum number of files simultaneously
                    }
                    // end CMDL-1606

                    var file=me.fileList[f];
                    if (!file.uploading && !file.completed){
                        me.fileList[f].uploading=true;
                        me.fileList[f].completed=false;
                        me.numUploading++;
                        me.log('current uploading '+me.numUploading+' '+(me.numUploading>1 ?'files':'file')+' with max '+me.simUploads+' concurrent allowed.');
                        return (new HTML5FileUpload(me.fileList[f], me.onUploadProgress, me.onUploadComplete, me.onUploadResponse));
                    }
                }
            }
            return (false);
        },

        /**
         * warn of any files which are too big before uploading
         */
        warnTooBig:function(){
            var tbtxt='';
            for (var f in me.tooBig){
                if (tbtxt==''){
                    tbtxt+='<ul class="mimetypes">';
                }
                var file=me.tooBig[f];
                var extclass='file ext_'+me.fileExt(file.name);
                tbtxt+='<li><div class="'+extclass+'">'+file.name+' ('+me.niceSize(file.size)+') </div></li>';
            }
            if (tbtxt==''){
                return (false); // no warning
            }
            tbtxt+='</ul>';

            tbtxt='<div class="warning alert">'+get_string('fileexceedsmaxsizeskipped', 'enhancedfile')+'</div><br />'+tbtxt+'<br /><div class="optiontxt">'+get_string('continueuploading', 'enhancedfile')+'</div>';
            var buttons=[
                         {name:get_string('ok', 'enhancedfile'), functCode:function(){me.dg.CloseDialog();me.REQ_check_resources()}},
                         {name:get_string('cancel', 'enhancedfile'), functCode:function(){me.dg.CloseDialog();}}];
            me.dg=new Dialog(get_string('fileexceedsmaxsize', 'enhancedfile'),tbtxt, {buttons:buttons, w:300,h:300, lightBox:true});
            me.dg.Init();
            return (true); // there are files which are too big
        },

        lboxUpQueue:function(){
            // hide file queue container
            var fqcont=$YUD.get('filequeuecontainer');
            fqcont.style.display='none';
            // set Dialog height
            var w=$YUD.getViewportWidth();
            var h=$YUD.getViewportHeight();


            // instantiate the main Dialog
            me.dg=new Dialog(get_string('uploadingfiles','enhancedfile'),get_string('uploadingfiles','enhancedfile'), {w:w,h:h, lightBox:true, tbButtons:{close:false, minmax:true}});
            me.dg.Init();
            // add queue container
            me.dg.SetPrompt(fqcont);
            // show queue container
            fqcont.style.display='block';
        },

        log:function(msg){
            if (typeof(console)!='undefined' && typeof(console.log)!='undefined'){
                console.log(msg);
            }
        },

        onUploadProgress:function(event) {
            var prog = Math.round(100*(event["bytesLoaded"]/event["bytesTotal"]));
            var progpx = parseInt((parseInt(me.progbarwidth) / 100)*prog);
            var pbar=$YUD.get('progbar_'+event.id);
            pbar.innerHTML="<div class='progress_active' style='height:"+me.progbarheight+";width:" + progpx + "px;'></div>";
        },

        onUploadComplete:function(event) {
            var pbar=$YUD.get('progbar_'+event.id);
            pbar.innerHTML="<div class='progress_uploaded' style='height:"+me.progbarheight+";width:"+me.progbarwidth+";'></div>";
            // set uploading to false and completed to true for this file and decrement concurrent number of uploading files (numUploading)
            for (var f in me.fileList){
                var testfile=me.fileList[f];
                if (testfile.id==event.id){
                    me.fileList[f].uploading=false;
                    me.fileList[f].completed=true;
                    me.numUploading--;
                }
            }
        },

        onUploadStart:function(event) {

        },

        onUploadError:function(event) {
            // log failure of upload
            me.failedIds.push(event.id);
            // log error message
            me.failedMsgs[event.id]=event.status;
            me.log('Error uploading file :'+event.id+' '+event.status);
            // set uploading to false and completed to true for this file and decrement concurrent number of uploading files (numUploading)
            for (var f in me.fileList){
                var testfile=me.fileList[f];
                if (testfile.id==event.id){
                    me.fileList[f].uploading=false;
                    me.fileList[f].completed=true;
                    me.numUploading--;
                }
            }
            // check all uploaded
            me.allUploadedCheck();
            var pbar=$YUD.get('progbar_'+event.id);
            pbar.innerHTML="<div class='progress_error' style='height:"+me.progbarheight+";width:"+me.progbarwidth+";'></div>";
        },

        onUploadCancel:function(event) {
        },

        onUploadResponse:function(event) {
            var pbar=$YUD.get('progbar_'+event.id);
            if (event.data.indexOf('<submitted>true</submitted>')>-1){
                // add to completed uploads
                me.completedIds.push(event.id);
                pbar.innerHTML="<div class='progress_complete' style='height:"+me.progbarheight+";width:"+me.progbarwidth+";'></div>";
            } else {
                // add to failed uploads
                me.failedIds.push(event.id);
                // log error message
                me.failedMsgs[event.id]=get_string('serverrejectedorfailedupload', 'enhancedfile')+':'+event.data;
                // set progress bar to error
                pbar.innerHTML="<div class='progress_error' style='height:"+me.progbarheight+";width:"+me.progbarwidth+";'></div>";
            }
            // check all uploaded
            me.allUploadedCheck();
            // upload more files from queue
            me.doSimUploads();
        },

        /**
         * Check that all files have been uploaded (even if they have failed)
         * @return boolean -  true if all uploads completed
         */
        allUploadedCheck:function(){
            // check if all files uploaded
            if (me.allUploaded==false){
                var allup=true; // until proven otherwise
                for (var f=0; f<me.upFileIds.length; f++){
                    var fileid=me.upFileIds[f];
                    var inarr=arraySearch(fileid, me.completedIds);
                    if (inarr==-1){
                        // check for file in failed uploads
                        inarr=arraySearch(fileid, me.failedIds);
                    }
                    if (inarr==-1){
                        allup=false;
                        break;
                    }
                }
                if (allup){
                    me.allUploaded=true;
                    // now trigger onAllUploaded function
                    me.onAllUploaded();
                    return (true);
                } else {
                    return (false);
                }
            } else {
                return (true);
            }
        },

        onAllUploaded:function(){
            var upmsg='';
            var uptitle='';
            var completed=me.completedIds.length+1;
            var failed=me.failedIds.length+1;
            var uploadedn=(completed-failed)+1;
            var filelist='<ul class="uploadedfiles mimetypes">';
            if (me.failedIds.length>0){
                uptitle=get_string('uploadedfiles', 'enhancedfile', uploadedn)+' '+get_string('outoffiles', 'enhancedfile', completed);
                upmsg='<h2>'+uptitle+'</h2>';
                // add failed files to file list
                for (var f=0; f<me.failedIds.length; f++){
                    var evid=me.failedIds[f];
                    var msg=me.failedMsgs[evid];
                    var failedfile=me.filesById[evid];
                    var extclass='file ext_'+me.fileExt(failedfile.name);
                    filelist+='<li><div class="file_upfailed"> </div><div class="'+extclass+'">'+failedfile.name+'</div><ul><li><div class="file_warning">'+msg+'</div></li></ul></li>';
                }
            } else {
                uptitle=get_string('uploadscompleted', 'enhancedfile');
                upmsg='<h2>'+get_string('alluploadscompleted','enhancedfile')+'</h2>';
            }

            // create list of succesfully uploaded files
            for (var f=0; f<me.completedIds.length; f++){
                var evid=me.completedIds[f];
                var file=me.filesById[evid];
                var extclass='file ext_'+me.fileExt(file.name);
                filelist+='<li><div class="file_upok"> </div><div class="'+extclass+'">'+file.name+'</div></li>';
            }

            filelist+='</ul>';
            upmsg+=filelist;

            // close existing dialog
            me.dg.CloseDialog();
            // show completion dialog
            if (me.submitbutton == 'id_submitbutton2'){
                var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.returnToCourse();}}];
            } else {
                var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.returnToAddModule();}}];
            }
            me.dg=new Dialog(uptitle,upmsg, {buttons:buttons, w:550,h:300, lightBox:true, tbButtons:{close:false, minmax:true}});
            me.dg.Init();
        },

        returnToCourse:function(){
            me.dg.CloseDialog();
            var title=get_string('pleasewait', 'enhancedfile');
            var msg=get_string('returningtocourse', 'enhancedfile');
            var dgbody='<div style="text-align:center"><div><h2>'+msg+'</h2></div><img style="width:32px; height:32px" src="'+file_wwwroot+'/mod/enhancedfile/pix/loader_big.gif" alt="'+title+'"></img></div>';
            me.dg=new Dialog(title, dgbody , {w:300,h:180, lightBox:true, tbButtons:{close:false, minmax:true}});
            me.dg.Init();
            window.setTimeout(function(){
                    window.location=file_wwwroot+'/course/view.php?id='+file_courseid;
                }, 1000
            );
        },
        returnToAddModule:function(){
            me.dg.CloseDialog();
            var title=get_string('pleasewait', 'enhancedfile');
            var msg=get_string('returningtofileupload', 'enhancedfile');
            var dgbody='<div style="text-align:center"><div><h2>'+msg+'</h2></div><img style="width:32px; height:32px" src="'+file_wwwroot+'/mod/enhancedfile/pix/loader_big.gif" alt="'+title+'"></img></div>';
            me.dg=new Dialog(title, dgbody , {w:300,h:180, lightBox:true, tbButtons:{close:false, minmax:true}});
            me.dg.Init();
            window.setTimeout(function(){
                    window.location=file_wwwroot+'/course/modedit.php?add=enhancedfile&type=&course='+file_courseid+ '&section='+file_section+'&return=0';

                }, 1000
            );

        },
        /**
         * Make sure GTLib is compatible with this block
         */
        checkGTLibVersion:function(){
            var verchk=2011020800;
            if (GTLib.version<verchk){
                alert (get_string('warn_gtlibversion', 'enhancedfile', verchk));
                return (false);
            }
            return (true);
        },

        /**
         * Apply click events
         */
        applyClickEvents:function(){
            // disable click action for file browser
            $YUD.get('id_file').onclick=function(){return (false);};
            // disable form submit for submit button
            $YUD.get('id_submitbutton').onclick=function(){return (false);};
            $YUD.get('id_submitbutton2').onclick=function(){return (false);};
            // add upload function to submit button onclick
            YAHOO.util.Event.addListener($YUD.get('id_submitbutton'), 'click', function(){me.initUpload('id_submitbutton')});
            YAHOO.util.Event.addListener($YUD.get('id_submitbutton2'), 'click', function(){me.initUpload('id_submitbutton2')});
        },

        dboxFileManager:function(){
            var dgbody='<div id="filemanager"></div>';
            var buttons=[{name:get_string('ok', 'enhancedfile'), functCode:function(){me.dg.CloseDialog()}}];
            me.dg=new Dialog(get_string('filemanager', 'enhancedfile'), dgbody , {
                buttons:buttons,
                w:parseInt($YUD.getViewportWidth()/1.5),
                h:parseInt($YUD.getViewportHeight()/1.5),
                lightBox:true,
                tbButtons:{close:true, minmax:true},
                preCloseFunct:function(){me.REQ_directoryrefresh()}
            });
            me.dg.Init();
            var iframe=createEl('iframe', {
                'style':'width:100%; visibility:hidden; height:'+(parseInt($YUD.getViewportHeight()/1.5)-100)+'px'
            });
            $YUD.get('filemanager').appendChild(iframe);
            me.showFileManLoader(iframe);
            $YUE.addListener(iframe, 'load', function(){me.onLoadFileMan(iframe)});
            //$YUE.addListener(iframe, 'unload', function(){me.showfilemanloader(iframe)});
            iframe.src=file_wwwroot+'/files/?id='+file_courseid;
        },

        showFileManLoader:function(iframe){
            var fml=$YUD.get('filemanloader');
            if (fml){
                fml.style.display='block';
            } else {
                var loader=createEl('div', {'class':'loader', 'id':'filemanloader'});
                loader.innerHTML=get_string('pleasewait', 'enhancedfile');
                iframe.parentNode.insertBefore(loader, iframe);
            }
        },

        onLoadFileMan:function(iframe){
            $YUD.get('filemanloader').style.display='none';
            me.iframeContentOnly(iframe);
        },


        iframeContentOnly:function(iframe){
            var idoc=null, content=null, nc=null;
            if (iframe.contentDocument){
                // w3c compliant browsers
                idoc=iframe.contentDocument;
            } else {
                // internet explorer lt8
                idoc=iframe.contentWindow.document;
           }

            content=idoc.getElementById('content');
            nc=content.cloneNode(true);

            idoc.body.innerHTML='';
            idoc.body.appendChild(nc);
            idoc.body.style.backgroundColor='#fff';
            idoc.body.style.backgroundImage='none';

            // make iframe content visible
            iframe.style.visibility='visible';

            /* old way of removing header and footer
            for (var cn in idoc.body.childNodes){
                var cnode=idoc[cn];
                if (cnode.id!='content'){
                    cnode.parentNode.removeChild(cnode);
                }
            }
            */
        },

        REQ_directoryrefresh:function(){
            var dirsel=$YUD.get('id_directory');
            dirsel.style.display='none';
            var loader=createEl('div', {'class':'loader', 'id':'dirloader'});
            loader.innerHTML=get_string('pleasewait', 'enhancedfile');
            dirsel.parentNode.appendChild(loader);
            var uri=file_wwwroot+'/mod/enhancedfile/ajax/directorystructure.php';
            postData='course='+$GTF('courseid');
            AJAXPost(uri, postData, {success:function(o){me.REC_directoryrefresh(o)}});
        },

        REC_directoryrefresh:function(o){
            var dirsel=$YUD.get('id_directory');
            dirsel.style.display='';
            var dirloader=$YUD.get('dirloader');
            dirloader.parentNode.removeChild(dirloader);
            try {
                // get object from json
                var response = YAHOO.lang.JSON.parse(o.responseText);
            }
            catch (e) {
                // @todo
                return;
            }
            // refresh directory selector
            var dirs=response.data.directories;
            var selected=$GTF('id_directory');
            
            do {
                dirsel.remove(0);
            } while (dirsel.length>0);

            for (var key in dirs){
                opt=createEl('option');
                opt.innerHTML=dirs[key];
                opt.value=key;
                opt.text=dirs[key];
                if (selected==key){
                    opt.selected='selected';
                }
                if(document.all && !window.opera){
                    try {
                        dirsel.add(opt);
                    } catch(e){
                        // for rubbish browsers
                        dirsel.appendChild(opt);
                    }
                }
                 else {
                     try{
                        dirsel.add(opt, null);
                     } catch (e){
                        // for rubbish browsers
                        dirsel.appendChild(opt);
                     }
                }

            }
        }


      };
    me.constructor();
    return (me);
}
// Auto instantiate
var multifile_inst=new multifile();
