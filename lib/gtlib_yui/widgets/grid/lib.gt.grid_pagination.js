
GTLib.Widget.GridPagination=function(div, ajaxurl, options){
    var me={

        // private properties
        _pages:0,        
        _totalrows:0,
        _griddiv:{},
        _pagdiv:{},
        _pagleft:{},
        _pagright:{},
        _pagviewing:{},
        _pagfld:{},
        _pagofpages:{},
        _loaddiv:{},
        _loadbox:{},
        _loadtxt:{},
        _loadimg:{},
    
        // public properties
        rowsperpage:8,
        pagenum:0,        
        el:{},
        ajaxurl:'',
        totalrowssendstr:'', // custom post vars on request for total rows
        pagesendstr:'', // custom post vars on request for grid data
        rowsendstr:'', // custom post vars on request for detailed row data
        openrowonclick:false, // if set to true, when a row is clicked it will request detailed row data from ajaxurl
        dg:{}, //if the container element is in a dialog, passing in the dialog via options will make 'detailed row data' child dialogs
        rdgopts:{buttons:false, w:400, h:300, lightBox:true}, // options for detailed row dialog
        rdgtitle:'Please wait...',
        gridopts:{}, // specific options for grid
               
        // call backs
        on_rowclick:{}, // fired when row clicked
        on_REC_totalrows:{}, // fired when total rows ajax received
        on_REC_page:{}, // fired when page ajax received        
        on_REC_rowDetail:{}, // fired when row detail is opened
        
        //
        // Purpose: Constructor
        //
        constructor:function(){
            me.el=div;
            me.ajaxurl=ajaxurl;
            
            me._loaddiv=GTLib.DOM.createEl('div', {'class':'gtlib_loader'});
            me.el.appendChild(me._loaddiv);
            
            me._loadbox=GTLib.DOM.createEl('div', {'class':'gtlib_loaderbox'});
            me._loaddiv.appendChild(me._loadbox);            
            
            me._loadtxt=GTLib.DOM.createEl('div', {'class':'gtlib_loadertxt'});
            me._loadbox.appendChild(me._loadtxt);
            me._loadtxt.innerHTML='Please wait...';            
            
            me._loadimg=GTLib.DOM.createEl('div', {'class':'imgLoaderBar'});
            me._loadbox.appendChild(me._loadimg);
                                    
            me._griddiv=GTLib.DOM.createEl('div', {'class':'gtlib_gridpage'});
            me.el.appendChild(me._griddiv);            
            
            me._pagdiv=GTLib.DOM.createEl('div', {'class':'gtlib_pagination'});
            me.el.appendChild(me._pagdiv);
            
            GTLib.Base.ObjectPropsFromOptions(me, options); // set optional properties of this class instance
            
            me.REQ_totalrows();
        },
        
        get_totalrows:function(){
            return (me._totalrows);
        },

        //
        // Purpose: AJAX Request: Get number of pages
        //
        REQ_totalrows:function(){
            me._loaddiv.style.display='';
            var sendstr='req=countrecords';
            sendstr+=me.totalrowssendstr!='' ? '&'+me.totalrowssendstr : '';
            GTLib.Ajax.Post(me.ajaxurl, sendstr, {success:function(o){me.REC_totalrows(o)}, failure:function(){}});
        },
        
        //
        // Purpose: AJAX Response: Number of pages
        //
        REC_totalrows:function(o){
             // Check for errors and Convert response to DomXML object            
            var respVal=GTLib.Ajax.ResponseToDomXMLValidate(o, 'gridpagination', true);
            if (respVal.error){
                return; // abort on any errors
            }
            var resp=respVal.resp;
            me._totalrows=XMLFirstTagValUnpack(resp,'record_count');
            me._pages=Math.ceil(me._totalrows/me.rowsperpage);            
            me.REQ_page(me.pagenum);
            
            // fire callback
            if (typeof(me.on_REC_totalrows)=='function'){
                me.on_REC_totalrows(resp);
            }
        },
        
        REQ_page:function(pagenum){            
            me._loaddiv.style.display='';
            var sendstr='req=getpage&page='+pagenum+'&rowsperpage='+me.rowsperpage;            
            sendstr+=me.pagesendstr!='' ? '&'+me.pagesendstr : '';
            GTLib.Ajax.Post(me.ajaxurl, sendstr, {success:function(o){me.REC_page(o, pagenum)}, failure:function(){}});
        },
        
        REC_page:function(o, pagenum){        
             // Check for errors and Convert response to DomXML object            
            var respVal=GTLib.Ajax.ResponseToDomXMLValidate(o, 'gridpagination', true);
            if (respVal.error){
                return; // abort on any errors
            } 
            var resp=respVal.resp;
 
            me.createTable(resp);
            me.createPagination();
            
            me._loaddiv.style.display='none';
            
            // fire callback
            if (typeof(me.on_REC_page)=='function'){
                me.on_REC_page(resp);
            }            
 
        },
        
        createTable:function(resp){
           // Set up table
            var tabarr=[];
            
            // Set up header row
            var cells=[];
            var atts=[{className:'header'}];
            
            var cols=resp.getElementsByTagName('col');
            for (c=0; c<cols.length; c++){
                var col=cols[c];
                var label=GTLib.XML.FirstTagValUnpack(col, 'label');
                cells.push({val:label});
            }            
            tabarr.push({cells:cells, atts:atts});
            
            // Set up table rows            
            var atts=[{className:'grid_selectable'}];
            
            var rows=resp.getElementsByTagName('row');
            for (r=0; r<rows.length; r++){
                // get row id
                var id=GTLib.XML.FirstTagValUnpack(rows[r], 'id');
                // if row id not set then set it to r
                id=id=='' ? r : id;

                var flds=rows[r].getElementsByTagName('fld');
                var cells=[];
                for (f=0; f<flds.length; f++){
                    var fld=flds[f];
                    cells.push({val:GTLib.XML.FirstTagValUnpack(fld, 'val')});                    
                }
                // push table body row
                tabarr.push({id:id, cells:cells, atts:atts});
            }
            
            if (me.openrowonclick){
                if (typeof(me.on_rowclick)=='function'){
                    var on_rowclick=function(id){me.on_rowclick(id); me.dgOpenRowOnClick(id)};
                } else {
                    var on_rowclick=function(id){me.dgOpenRowOnClick(id)};
                }
            } else {
                if (typeof(me.on_rowclick)=='function'){
                    var on_rowclick=function(id){me.on_rowclick(id);};
                } else {
                    var on_rowclick={};
                }
            }
            
            // merge setable grid options with grid options for this class
            var gridopts=YAHOO.lang.merge(me.gridopts, {tableclass:'grid_table', on_rowclick:on_rowclick});
            
            var grid=new GTLib.Widget.Grid(tabarr, gridopts);
            me._griddiv.innerHTML='';
            me._griddiv.appendChild(grid.getGrid());
        },
        
        dgOpenRowOnClick:function(id){
            var prompt='';
            var rdg={};
            var rdgopts=GTLib.Base.Clone(me.rdgopts);
            if (!rdgopts.buttons){
                var buttons=[
                    {name:'OK', functCode:function(){rdg.CloseDialog();}}
                ]
                rdgopts.buttons=buttons;
            }
            if (typeof(me.dg)=='function'){            
                rdg=me.dg.AddChildDialog(me.rdgtitle, prompt, rdgopts);                
            } else {
                rdg=new Dialog(me.rdgtitle, prompt, rdgopts);
                rdg.Init();
            }
            rdg.StatusLoading('Please wait...');
            me.REQ_rowDetail(id, rdg);
        },
        
        REQ_rowDetail:function(id, rdg){            
            var sendstr='req=getrow&rowid='+id;
            sendstr+=me.rowsendstr!='' ? '&'+me.rowsendstr : '';
            GTLib.Ajax.Post(me.ajaxurl, sendstr, {success:function(o){me.REC_rowDetail(o, id, rdg)}, failure:function(){}});
        },
        
        REC_rowDetail:function(o, id, rdg){
             // Check for errors and Convert response to DomXML object            
            var respVal=GTLib.Ajax.ResponseToDomXMLValidate(o, 'gridpagination', true);
            if (respVal.error){
                return; // abort on any errors
            }
            var resp=respVal.resp;
            
            // Get title for row and change dialog title to it
            var title=GTLib.XML.FirstTagValUnpack(resp, 'title');
            rdg.SetTitle(title);
            
            // Get fields for row and display
            var flds=resp.getElementsByTagName('fld');
            var prompt='<form class="gtlib">';
            for (var f=0; f<flds.length; f++){
                var fld=flds[f];
                var label=GTLib.XML.FirstTagValUnpack(fld, 'label');
                var val=GTLib.XML.FirstTagValUnpack(fld, 'val');
                //prompt+='<div><label>'+label+'</label><input type="text" readonly="readonly" value="'+val+'"/>';
                prompt+='<div class="formrow"><label>'+label+'</label><div class="txtreadonly">'+val+'</div>';
                
            }            
            prompt+='</form>';
            rdg.StatusLoadingRemove();
            rdg.SetPrompt(prompt);
                        
            // fire callback
            if (typeof(me.on_REC_rowDetail)=='function'){
                me.on_REC_rowDetail(resp, id, rdg);
            }
            
        },
        
        
        
        createPagination:function(){
        
            // Wipe pagination div
            var pagdiv=me._pagdiv;
            pagdiv.innerHTML='';        
        
            // Write pagination                         
           if (me._pages>1){
          
                // Add left pagination button
                me._pagleft=createEl('div', {'class':'itempaginationLeft'});
                var pagleft=me._pagleft;
                if (me.pagenum==0){
                    pagleft.className='itempaginationLeftGreyed';
                }
                pagdiv.appendChild(pagleft);

                // Add viewing text
                me._pagviewing=createEl('div', {'class':'itempaginationTextViewing'});
                var pagviewing=me._pagviewing;
                pagviewing.innerHTML='viewing page';
                pagdiv.appendChild(pagviewing);
                
                // Add page selector field
                me._pagfld=createEl('select', {'class':'itempaginationSelect'});
                var pagfld=me._pagfld;
                var opts='';
                for (o=1; o<=me._pages; o++){
                    var opt=createEl('option');
                    opt.value=o;
                    opt.innerHTML=o;
                    if (o==(me.pagenum+1)){
                        opt.selected='selected';
                    }
                    pagfld.appendChild(opt);
                }
                pagdiv.appendChild(pagfld);
                
                // Add pages text
                me._pagofpages=createEl('div', {'class':'itempaginationTextOfPages'});
                var pagofpages=me._pagofpages;
                pagofpages.innerHTML='of '+me._pages+' pages';
                pagdiv.appendChild(pagofpages);
                
                // Add right pagination button
                me._pagright=createEl('div', {'class':'itempaginationRight'});
                var pagright=me._pagright;
                pagdiv.appendChild(pagright);
                if (me.pagenum>=(me._pages-1)){
                    pagright.className='itempaginationRightGreyed';
                }                

                // Add page navigation events
                YAHOO.util.Event.addListener(pagleft, 'click', function(){me.pageLeft()});       
                YAHOO.util.Event.addListener(pagright, 'click', function(){me.pageRight()});
                YAHOO.util.Event.addListener(pagfld, 'change', function(){me.pageToSelected()});                
                                
            }
        },
        pageLeft:function(){
            var pagenum=me.pagenum;
            pagenum--;
            if (pagenum>-1){
                me.pagenum=pagenum;
                me.REQ_page(me.pagenum);      
            }              
        },
        pageRight:function(){
            var pagenum=me.pagenum;
            pagenum++;
            if (pagenum<me._pages){
                me.pagenum=pagenum;
                me.REQ_page(me.pagenum);      
            }        
        },
        pageToSelected:function(){
            me.pagenum=parseInt($GTF(me._pagfld))-1;
            me.REQ_page(me.pagenum); 
        }        
    }
    me.constructor();
    return (me);    
}