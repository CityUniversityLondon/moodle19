GTLib.Widget.Grid=function(arr,options){var me={_arr:arr,_grid:{},_selectedrow:null,tableclass:'',firstrowheader:true,applyaltrows:true,highlightrowsonselect:true,rowselectclass:'row_selected',on_rowclick:{},constructor:function(){GTLib.Base.ObjectPropsFromOptions(me,options);me.createGrid();},createGrid:function(){var table=GTLib.DOM.createEl('table');table.className=me.tableclass;var tbody=GTLib.DOM.createEl('tbody');var rowalt=1;for(var r=0;r<me._arr.length;r++){var row=me._arr[r];var id=row.id?row.id:r;rowalt=rowalt==1?0:1;var tr=GTLib.DOM.createEl('tr');me.applyJsonAttributesToEl(row.atts,tr);if(r>0||me.firstrowheader==false){me.add_on_rowclick(tr,id);}
if(me.applyaltrows){if(!me.firstrowheader||r>0){YAHOO.util.Dom.addClass(tr,'rowalt'+rowalt);}}
tbody.appendChild(tr);var tdel=r==0&&me.firstrowheader?'th':'td';for(var c=0;c<row.cells.length;c++){var cell=row.cells[c];var td=GTLib.DOM.createEl(tdel);td.innerHTML=cell.val;me.applyJsonAttributesToEl(cell.atts,td);tr.appendChild(td);}}
table.appendChild(tbody);me._grid=table;},add_on_rowclick:function(tr,id){YAHOO.util.Event.addListener(tr,'click',function(){me.rowSelected(tr)});if(typeof(me.on_rowclick)=='function'){YAHOO.util.Event.addListener(tr,'click',function(){me.on_rowclick(id)});}},rowSelected:function(tr){if(me.highlightrowsonselect){if(me._selectedrow){YAHOO.util.Dom.removeClass(me._selectedrow,me.rowselectclass);}}
me._selectedrow=tr;if(me.highlightrowsonselect){YAHOO.util.Dom.addClass(tr,me.rowselectclass);}},applyJsonAttributesToEl:function(json,el){for(var a in json){for(var i in json[a]){var atval=json[a][i];if(i.substr(0,2)=='on'&&typeof(atval)=='function'){var evtype=i.substr(2);YAHOO.util.Event.addListener(el,evtype,atval);}else{var isstring=true;switch(typeof(atval)){case'function':isstring=false;case'object':isstring=false;case'number':isstring=false;}
atvalesc=isstring?"'"+atval+"'":atval;var evalstr='el.'+i+'='+atvalesc;eval(evalstr);}}}},getGrid:function(){return(me._grid);}}
me.constructor();return(me);}