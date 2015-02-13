// GTLib (c) Guy Thomas 2007-2008  MIT Licence

/*
Constructor Parameters
    arr - array of rows with array of cell items e.g.    
    [
        {
            cells:[
                {val:'headercol1', atts:[{id:'c1'}, {class:'test'}, {style:'background-color:red'}]},
                {val:'headercol2'},
                {val:'headercol3'}
            ],
            atts:[{class:'headerrow'}]
        },
        {
            cells:[
                {val:'row1cel1'},
                {val:'row1cel2'},
                {val:'row1cel3'}        
            ]
        },
        {
            cells:[
                {val:'row2cel1'},
                {val:'row2cel2'},
                {val:'row2cel3'}        
            ]
        }
    ]
    
    options - sets public properties
*/

GTLib.Widget.Grid=function(arr, options){
    var me={  
        
        //
        // private properties
        //
        _arr:arr, // array data
        _grid:{}, // grid object
        _selectedrow:null, // selected row element
        
        //
        // optional properties
        //
        tableclass:'',
        firstrowheader:true,
        applyaltrows:true, // apply alternate row classes to each row
        highlightrowsonselect:true,  // apply highlight class when a row is selected
        rowselectclass:'row_selected',
        
        // callbacks
        on_rowclick:{},        
    
        //
        // Purpose: Constructor
        //
        constructor:function(){
            GTLib.Base.ObjectPropsFromOptions(me, options); // set optional properties of this class instance
            me.createGrid();
        },
        
        //
        // Purpose: Creates the grid element
        //
        createGrid:function(){
            var table=GTLib.DOM.createEl('table');     
            table.className=me.tableclass;
            var tbody=GTLib.DOM.createEl('tbody');
            var rowalt=1;
            for (var r=0; r<me._arr.length; r++){
                var row=me._arr[r];
                // get row id or set to r
                var id=row.id ? row.id : r; // note id is not necessarilly the dom id - it could be a db record id, a dom id should be set in atts.id
                rowalt=rowalt==1 ? 0 : 1;
                var tr=GTLib.DOM.createEl('tr');
                
                // apply json attributes to row element
                me.applyJsonAttributesToEl(row.atts, tr);
                
                // appply select highlight event
                if (r>0 || me.firstrowheader==false){
                    me.add_on_rowclick(tr, id);                    
                }
                
                if (me.applyaltrows){
                    if (!me.firstrowheader || r>0){
                        YAHOO.util.Dom.addClass(tr, 'rowalt'+rowalt);
                    }
                }
                tbody.appendChild(tr);
                // get element type for this row
                var tdel=r==0 && me.firstrowheader ? 'th' : 'td';
                for (var c=0; c<row.cells.length; c++){
                    var cell=row.cells[c];
                    var td=GTLib.DOM.createEl(tdel);
                    td.innerHTML=cell.val;
                    
                    // apply json attributes to cell element
                    me.applyJsonAttributesToEl(cell.atts, td);
                    tr.appendChild(td);
                }
            }
            table.appendChild(tbody);
            me._grid=table;
        },
        
        //
        // Purpose: Add on click for row item
        //
        add_on_rowclick:function(tr, id){
            // add row selected function click listener
            YAHOO.util.Event.addListener(tr, 'click', function(){me.rowSelected(tr)});

            // if on_rowclick is a function then add click listener
            if (typeof(me.on_rowclick)=='function'){
                YAHOO.util.Event.addListener(tr, 'click', function(){me.on_rowclick(id)});
            }
        },
        
        //
        // Purpose: Apply selected class, etc
        //
        rowSelected:function(tr){
            // remove selected class from previously selected row
            if (me.highlightrowsonselect){
                if (me._selectedrow){
                    YAHOO.util.Dom.removeClass(me._selectedrow, me.rowselectclass);
                }
            }
            
            // set selected row to current row
            me._selectedrow=tr;
            
            if (me.highlightrowsonselect){
                // add selected class to newly selected row
                YAHOO.util.Dom.addClass(tr, me.rowselectclass);
            }
        },
        
        //
        // Purpose: Apply array of json attributes to dom element
        //
        applyJsonAttributesToEl:function(json, el){
            // apply attributes from json to dom obj
            for (var a in json){
                for (var i in json[a]){
                    var atval=json[a][i]; // attribute value (i is attribute key)
                    // if i is an event trigger (onclick, onhover, etc) then add event
                    if (i.substr(0,2)=='on' && typeof(atval)=='function'){
                        var evtype=i.substr(2);
                        YAHOO.util.Event.addListener(el, evtype, atval);
                    } else {
                        var isstring=true;
                        switch (typeof(atval)){
                            case 'function' : isstring=false;
                            case 'object' : isstring=false;
                            case 'number' : isstring=false;
                        }
                        // escape attribute value if necessary
                        atvalesc=isstring ? "'"+atval+"'" : atval;
                        var evalstr='el.'+i+'='+atvalesc;
                        eval (evalstr);
                    }
                }
            }        
        },
        
        //
        // Purpose: return private grid object
        //
        getGrid:function(){
            return (me._grid);
        }       
    }
    me.constructor();
    return (me);
}