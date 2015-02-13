// GTLib (c) Guy Thomas 2007-2008  MIT Licence
// GTLib Widget Dialog

GTLib.Widget.Dialog=function(title,prompt,opts){
	
    /**
    /* Requires yahoo yui
    /* Region.js
    /* DD.js
    /* DDProxy.js
    /* DDTarget.js
    /* DragDrop.js
    /* DragDropMgr.js
    /* lib.gt.base.js
    /* lib.gt.dragdrop.js
    /* 
	**/

    // Public properties
	this.x;
	this.y;
	this.w=300;
	this.h=200;
	this.title;
	this.prompt;
	this.buttons;
	this.id;
	this.domObj;
    this.preInitFunct;
    this.postInitFunct;
	this.preCloseClickFunct;    // (on close click)
	this.postCloseClickFunct;   // (after close click)
    this.preCloseFunct;   // (just before dialog is removed)
    this.postCloseFunct;  // (after dialog is removed)
	this.postResizeFunct; // callback during resizing dialog
	this.lightBox; // boolean - will darken screen under dialog if set to true	
	this.showXY=false; // boolean - show co-ordinates
	this.constrain=false; // boolean - keep dialog within window
	this.cssTheme="standard"; // prepend to all css classes (needed for skinning)
    this.zIndex;
    this.ldText='Please wait. Loading Data...';
    this.children=new Array(); // child dialogs
    this.parentDialog;
    this.tbButtons={close:true, minmax:true}; // toolbar buttons
   
    this.browser={
        ie:navigator.appVersion.indexOf('MSIE')>-1,
        ie6:navigator.appVersion.indexOf('MSIE 6')>-1,
        opera:navigator.userAgent.indexOf("Opera")>-1,
        firefox:navigator.userAgent.indexOf("Firefox")>-1
    };
    
    // Private Properties
    this._winDets;
    this._ddResize;
        
	this.Init=function(){
        // check to see if already initialised
        if (me.id!="" && $GT(me.id)){
            return (false);
        }

        // set optional properties of this class instance        
        GTLib.Base.ObjectPropsFromOptions(me, opts); 
        
        // action pre init call back
        if (typeof(me.preInitFunct)=='function'){            
            me.preInitFunct();
        }        

        // get position or set to center of screen
        me.updateWinDets();
                        
        // set position from options (defaults are applied later if not set)
        me.x=opts.x ? opts.x : (parseInt(me._winDets.w)/2)-(parseInt(me.w)/2)+parseInt(me._winDets.x);        me.y=opts.y ? opts.y : (parseInt(me._winDets.y)+parseInt(me._winDets.h/2))-(parseInt(me.h)/2);

		me.title=title;
		me.prompt=prompt;
		me.buttons=typeof(opts.buttons)!='undefined' ? opts.buttons : [{}];
              
        // get z-index or set to topmost dialog's zindex +1
        if (!me.zIndex){
            var tdia=me.getTopmostDialog();
            if (tdia){
                me.zIndex=tdia.zIndex+1;
            } else {
                me.zIndex=999;
            }
        }
        
        // override theme if global override set
        if (typeof(GTLib_DialogTheme)!='undefined'){
            me.cssTheme=GTLib_DialogTheme;
        }
        
        // get / set global variable for dialogs generated
        me.registerDialog();        
		
		// Draw Dialog
		me.DrawDialog();
        
        // action  post init callback
        if (typeof(me.postInitFunct)=='function'){
            me.postInitFunct();
        }
        
        me.FixAllIEPngBGs();

        // return true - initialised ok
        return (true);
	}
    
    //
    // Purpose: Register dialog in global array and set id if not already set
    //
    this.registerDialog=function(){
        if (typeof(GTLib.Widget.Dialogs)=='undefined'){
            GTLib.Widget.Dialogs=new Array();
            dlid=0;
        } else {
            dlid=GTLib.Widget.Dialogs.length;
        }    		
        // set id if not already set
        me.id=me.id ? me.id : ('dialog_'+dlid);
        GTLib.Widget.Dialogs[dlid]=me;      
    }
    
    //
    // Purpose: Unregister dialog from global dialog array
    //
    this.unregisterDialog=function(){
        // get position of dialog in global array
        var apos=GTLib.Array.search(me, GTLib.Widget.Dialogs);
        if (apos==-1){
            return; // dialog appears to not be registered        
        }
        GTLib.Widget.Dialogs.splice(apos,1);        
    }
    
    //
    // Purpose: Get topmost dialog / get topmost lightboxed dialog
    //
    this.getTopmostDialog=function(lbox){
        var z=-1;
        var topdia=null;
        if (typeof(GTLib.Widget.Dialogs)=='undefined'){
            return (null);
        }
        for (var d=0; d<GTLib.Widget.Dialogs.length; d++){
            var dia=GTLib.Widget.Dialogs[d];
            if (!lbox || dia.lightBox){                
                if (dia.zIndex>=z){
                    z=dia.zIndex;
                    topdia=dia;
                }
            }
        }
        return (topdia);
    }
    
    //
    // Update window details - scroll top, scroll left, width, height
    //
    this.updateWinDets=function(){
        var _winDets={x:YAHOO.util.Dom.getDocumentScrollLeft(),
        y:YAHOO.util.Dom.getDocumentScrollTop(),
        w:YAHOO.util.Dom.getViewportWidth(),
        h:YAHOO.util.Dom.getViewportHeight()};
		me._winDets=_winDets;
        return (_winDets);
    }
	
	this.DrawDialog=function(){
	
		// Light box
		if (me.lightBox){
			// Create style for light box            
			var styleStr="left:0px;"+"top:0px;";
            
            if (this.browser.ie6){
                styleStr+="position:absolute!important;";
                var dh=YAHOO.util.Dom.getDocumentHeight();
                styleStr+="width:100%;height:"+dh+"px;";
            } else {
                styleStr+="position:fixed!important;";                
                styleStr+="width:100%;height:100%;";                
            }
            styleStr+="z-index:"+(me.zIndex-1)+";";


            var darkDiv=$GT('darkDiv');
            if (!darkDiv){
                // create dark div for lightboxing
                var darkDiv=GTLib.DOM.createEl('div', {id:'darkDiv', style:styleStr, 'className':'lightBoxDarkDiv'});
                document.body.appendChild(darkDiv);
            } else {
                // change dark div z-index                
                darkDiv.style.zIndex=me.zIndex;                                
            }
		}
	
		// Create style for dialog container
		var styleStr="";
		styleStr+="position:absolute;";
		styleStr+="left:"+me.x+"px;"+"top:"+me.y+"px;";
		styleStr+="width:"+me.w+"px;"+"height:"+me.h+"px;";
	
		
		// Create container for dialog
		var dialogContainer=GTLib.DOM.createEl('div', {id:me.id, style:styleStr});
        dialogContainer.style.zIndex=me.zIndex;
		me.domObj=dialogContainer;
       
        // fix IE6 field zindex bug
        me.FixIE6FieldZIndexBug();
        
        // Create corner divs
        var tlcorner=GTLib.DOM.createEl('div', {id:me.id+"_tlcorner", 'className':'TopLeftCorner'});
        var trcorner=GTLib.DOM.createEl('div', {id:me.id+"_trcorner", 'className':'TopRightCorner'});
        var blcorner=GTLib.DOM.createEl('div', {id:me.id+"_blcorner", 'className':'BotLeftCorner'});
        var brcorner=GTLib.DOM.createEl('div', {id:me.id+"_brcorner", 'className':'BotRightCorner'});
        dialogContainer.appendChild(tlcorner);
        dialogContainer.appendChild(trcorner);
        dialogContainer.appendChild(blcorner);
        dialogContainer.appendChild(brcorner);
        
		// Create toolbar for moving dialog
		var toolBar=GTLib.DOM.createEl('div', {id:me.id+"_toolBar", 'className':"DialogToolbar"});
		var toolBarTitle=GTLib.DOM.createEl('div', {id:me.id+"_toolBarTitle", 'className':"DialogToolbarTitle"});
		toolBarTitle.innerHTML=me.title;
		toolBar.appendChild(toolBarTitle);		
		var toolBarButtons=GTLib.DOM.createEl('div', {id:me.id+"_toolBarButtons", 'className':"DialogToolbarButtons"});
		toolBar.appendChild(toolBarButtons);
		// Add toolbar to dialog
		dialogContainer.appendChild(toolBar);
		
		// Create base bar for containing win drag
		var baseBar=GTLib.DOM.createEl('div', {id:me.id+"_baseBar", 'className':"DialogBaseBar"});
		var baseBarInner=GTLib.DOM.createEl('div', {'className':"DialogBaseBarInner"});
		baseBar.appendChild(baseBarInner);
		// Add base bar to dialog
		dialogContainer.appendChild(baseBar);

		// Create body container of dialog
		var dialogInner=GTLib.DOM.createEl('div', {id:me.id+"_inner", 'className':'DialogInner'});
		dialogContainer.appendChild(dialogInner);

        // Create edge divs
        var l_edge=GTLib.DOM.createEl('div', {id:me.id+"_l_edge", 'className':'LeftEdge'});
        var r_edge=GTLib.DOM.createEl('div', {id:me.id+"_r_edge", 'className':'RightEdge'});
        var b_edge=GTLib.DOM.createEl('div', {id:me.id+"_b_edge", 'className':'BotEdge'});
        var t_edge=GTLib.DOM.createEl('div', {id:me.id+"_t_edge", 'className':'TopEdge'});         
        dialogContainer.appendChild(l_edge);
        dialogContainer.appendChild(r_edge);
        dialogContainer.appendChild(b_edge);
        dialogContainer.appendChild(t_edge);
	
		// Create prompt
		var prompt=GTLib.DOM.createEl('div', {id:me.id+"_prompt", 'className':"DialogPrompt"});
		prompt.innerHTML=me.prompt;
		
		// Add prompt to dialog
		dialogInner.appendChild(prompt);
				
		// Add window drag expand button
		var winDragContainer=GTLib.DOM.createEl('div', {id:me.id+"_winDragExpContainer", 'className':"WindowDragExpContainer"});
		var winDrag=GTLib.DOM.createEl('div', {id:me.id+"_winDragExpander", 'className':"WindowDragExpander"});
        winDrag.style.zIndex=(me.zIndex+1);
		winDragContainer.appendChild(winDrag);
		baseBar.appendChild(winDragContainer);
        
        this._ddResize = new GTLib.DD.ZIndex(me.id+"_winDragExpander","", {scroll:false, dragZ:me.zIndex+1});
        this._ddResize.onDrag=function(){me.Resize()};
        this._ddResize.setHandleElId(me.id+"_winDragExpander");
       
		// Add co-ordinates
		if (me.showXY){
			var coords=GTLib.DOM.createEl('div', {id:me.id+"_coords", 'className':"DialogCoords"});
			var posX=GTLib.DOM.createEl('div', {id:me.id+"_posX", 'className':"PosX"});
			var posY=GTLib.DOM.createEl('div', {id:me.id+"_posY", 'className':"PosY"});
			var sizeX=GTLib.DOM.createEl('div', {id:me.id+"_sizeX", 'className':"SizeX"});
			var sizeY=GTLib.DOM.createEl('div', {id:me.id+"_sizeY", 'className':"SizeY"});
			coords.appendChild(posX);
			coords.appendChild(posY);
			coords.appendChild(sizeX);
			coords.appendChild(sizeY);
			dialogInner.appendChild(coords);
		}
		
		// Add Dialog To Document
		document.body.appendChild(dialogContainer);	
   
		// Add dragable functionality       
        var dd = new GTLib.DD.ZIndex(dialogContainer.id,"", {scroll:false, dragZ:me.zIndex+1});
        dd.onDrag=function(){me.Moved()};
        dd.setHandleElId(me.id+"_toolBar");

		// Add toolbar buttons to dialog
		me.AddToolbarButtons();
		
		// Add buttons to dialog
		me.AddButtons(baseBar);
		
		// Fix dialog Inner size (Necessary for opera)
		dialogInner.style.height=(parseInt(dialogContainer.offsetHeight)-parseInt(toolBar.offsetHeight)-parseInt($GT(me.id+"_baseBar").offsetHeight))+"px";
		
		
		// Show Co-ordinates
		me.UpdateCoords();

        // set theme
        me.SetTheme(me.cssTheme);

	}
    
    this.SetTheme=function(theme){
        me.cssTheme=theme;
        $GT(me.id).className=me.cssTheme+'_DW';
        // call later - resize after new theme has been applied
        setTimeout(function(){me.Resize()}, 5);
        // IE - do twice (sometimes it fails and causes an error on setting edge dims, only affects IE)
        if (GTLib.Browser.ie){
            setTimeout(function(){me.Resize()}, 100);
        }
    }
    
       
	
	this.Moved=function(){
		// Stop movement from exceeding window if constrain is set to true
		if (me.constrain){
			// Make sure dialog does not go outside current window space
			var farX=parseInt(me.domObj.style.left)+parseInt(me.domObj.style.width);
			var farY=parseInt(me.domObj.style.top)+parseInt(me.domObj.style.height);
			// Make sure not less than window left and top
			if (farX<parseInt(me.domObj.style.width)){
				me.domObj.style.left="0px";
			}
			if (farY<parseInt(me.domObj.style.height)){
				me.domObj.style.top="0px";
			}		
			// Make sure not greater than window right and bottom
			if (farY>me._winDets.fullHeight){
				me.domObj.style.top=me._winDets.fullHeight-parseInt(me.domObj.style.height)+"px";
			}
			
			// Set right pad (account for scroll bars, etc)
			if (this.browser.ie){				
				var rPad=20;
			} else if (this.browser.opera){
				var rPad=10;
			} else if (this.browser.firefox){
				var rPad=4;
			}
			if (farX>(me._winDets.fullWidth-rPad)){
				me.domObj.style.left=(me._winDets.fullWidth-parseInt(me.domObj.style.width))-rPad+"px";
			}
			if (farY>me._winDets.fullHeight){
				me.domObj.style.top=me._winDets.fullHeight-parseInt(me.domObj.style.height);
			}
		}
		
		me.UpdateCoords();
	}
	
	this.Resize=function(){
    
        // abort if dialog no longer exists
        if (!$GT(me.id)){
            return;
        }
	        
		var toolBar=$GT(me.id+"_toolBar");
		var baseBar=$GT(me.id+"_baseBar");
		var winDrag=$GT(me.id+"_winDragExpander");
	
        // reize window
		var newWidth=parseInt(me.domObj.style.width)+parseInt(winDrag.offsetLeft);
		var newHeight=parseInt(me.domObj.style.height)+parseInt(winDrag.offsetTop);
		
		if (newWidth>50){
			me.domObj.style.width=newWidth+"px";
		}
		if (newHeight>(toolBar.offsetHeight+baseBar.offsetHeight)){
			// resize dialog
			me.domObj.style.height=newHeight+"px";
			// resize dialog inner div
			$GT(me.id+"_inner").style.height=(newHeight-parseInt($GT(me.id+"_toolBar").offsetHeight)-parseInt($GT(me.id+"_baseBar").offsetHeight))+"px";			
		}
		
		// reposition win drag
		winDrag.style.left="0px";
		winDrag.style.top="0px";
        if (this._ddResize){
            this._ddResize.resetConstraints();
        }
        		
        // update window co-ordinates
		me.UpdateCoords();        
        me.SetEdgeDims();
        me.IE6ZIndexBugFrameResize();
        
        // call back for resize
        if (typeof(me.postResizeFunct)=='function'){
            me.postResizeFunct();
        }
	}
    
    this.SetEdgeDims=function(){
        // set edge heights        
        var dContDims=GTLib.DOM.getElDims(me.domObj);
        var tlc=$GT(me.id+"_tlcorner");
        var tlcDims=GTLib.DOM.getElDims(tlc);
        var trc=$GT(me.id+"_trcorner");
        var trcDims=GTLib.DOM.getElDims(trc);
        var blc=$GT(me.id+"_blcorner");
        var blcDims=GTLib.DOM.getElDims(blc);
        var brc=$GT(me.id+"_brcorner");
        var brcDims=GTLib.DOM.getElDims(brc);
        try {
            $GT(me.id+"_l_edge").style.height=((dContDims.h-tlcDims.h)-blcDims.h)+"px";
            $GT(me.id+"_l_edge").style.top=tlcDims.h+"px";
            $GT(me.id+"_r_edge").style.height=((dContDims.h-trcDims.h)-brcDims.h)+"px";
            $GT(me.id+"_r_edge").style.top=trcDims.h+"px";
            $GT(me.id+"_b_edge").style.width=((dContDims.w-blcDims.w)-brcDims.w)+"px";
            $GT(me.id+"_b_edge").style.left=blcDims.w+"px";
            $GT(me.id+"_t_edge").style.width=((dContDims.w-tlcDims.w)-trcDims.w)+"px";
            $GT(me.id+"_t_edge").style.left=tlcDims.w+"px";
        } catch (e){
            // Unable to set edge dimensions
        }
    }
	
	this.UpdateCoords=function(){
		/**
		/*Purpose - Updates co-ordinates if they are to be shown
		**/
		if (me.showXY){
			$GT(me.id+"_posX").innerHTML="x "+me.domObj.style.left;
			$GT(me.id+"_posY").innerHTML="y "+me.domObj.style.top;
			$GT(me.id+"_sizeX").innerHTML="w "+me.domObj.style.width;
			$GT(me.id+"_sizeY").innerHTML="h "+me.domObj.style.height;
		}
	}
	
	this.AddToolbarButtons=function(){    
        // Add toolbar close button to dialog if necessary
        if (me.tbButtons.close){    		
    		var closeBut=me.AddToolbarButton(me.id+"_close", "DialogToolbarButton ButtonClose");
    		
    		// Add pre close function to dialog close button (button in toolbar)
    		if (typeof(me.preCloseClickFunct)=='function'){			
    			YAHOO.util.Event.addListener(closeBut, 'click', function(){me.preCloseClickFunct()});
    		}

    		// Add close function to close button in toolbar
    		YAHOO.util.Event.addListener(closeBut.id, 'click', function(){me.CloseDialog()});
    		
    		// Add post close function to dialog close button (button in toolbar)
    		if (typeof(me.postCloseClickFunct)=='function'){
    			YAHOO.util.Event.addListener(closeBut, 'click', function(){me.postCloseClickFunct()});
    		}
        }

        
		// Add toolbar max button to dialog if necessary
        if (me.tbButtons.minmax){
    		var maxBut=me.AddToolbarButton(me.id+"_maximise", "DialogToolbarButton ButtonMax");
    		
    		//Add max function to max button in toolbar
    		YAHOO.util.Event.addListener(maxBut, 'click', function(){me.MaximiseDialog()});
    		
    		// Add minimise button to dialog
    		var minBut=me.AddToolbarButton(me.id+"_minimise", "DialogToolbarButton ButtonRest");
    		minBut.style.display="none";
    		
    		//Add min function to min button in toolbar
    		YAHOO.util.Event.addListener(minBut, 'click', function(){me.MinimiseDialog()});
        }
	}
	
	this.AddToolbarButton=function(buttonId, buttonClass){
		var toolBarButtons=$GT(me.id+"_toolBarButtons");
		var but=GTLib.DOM.createEl('div', {id:buttonId, 'className':buttonClass});		
		YAHOO.util.Event.addListener(but, 'mouseover', function(){me.IconButtonMouseOver(buttonId)});		
		YAHOO.util.Event.addListener(but, 'mouseout', function(){me.IconButtonMouseOut(buttonId)});
		toolBarButtons.appendChild(but);
		return (but);
	}
	
	this.AddButtons=function(buttonParent){
		/**
		/* Purpose - Adds buttons to dialog
		**/
		
		// create div for holding buttons
		var butContainer=GTLib.DOM.createEl('div', {id:this.id+'_buttonContainer', 'className':'DialogButtonContainer'});
		buttonParent.appendChild(butContainer);
		
		for (var a=0; a<me.buttons.length; a++){
			var name=me.buttons[a].name;
            var butclass=me.buttons[a].butclass;
            var butstyle=me.buttons[a].butstyle ? me.buttons[a].butstyle : '';
            if (typeof(butclass)=='undefined'){
                butclass='';
            } else {
                butclass=' '+butclass;
            }
			var functCode=me.buttons[a].functCode;
			if (typeof(name)!='undefined'){
				// Create button
				var butId=me.buttons[a].butid ? me.buttons[a].butid : me.id+"_button_"+a;
				var butInnerId=me.id+"_buttonInner_"+a;
				var but=GTLib.DOM.createEl('div', {id:butId, 'className':'DialogButton'+butclass, style:butstyle});
				if (this.browser.ie6){
					// this line is for IE 6- without it, the button would take up the full width of the dialog
					but.style.width="20px";
				}
				var butInner=GTLib.DOM.createEl('div',{id:butInnerId, 'className':'DialogButtonInner'});
				butInner.innerHTML=name;
				but.appendChild(butInner);
                // Add button to default container if no parent element specified                
                if (typeof(me.buttons[a].parent)=='undefined'){
                    butContainer.appendChild(but);
                } else {                    
                    $GT(me.buttons[a].parent).appendChild(but); // parent can be an id or element
                }
				// Add button events
                this.AddButtonEvents(but, butId, butInnerId, functCode);
			}
		}				
	}
    
    this.AddButtonEvents=function(but, butId, butInnerId, cbClick){
        // Add click function
        YAHOO.util.Event.addListener(but, 'click', cbClick);
        // Add mouse over
        YAHOO.util.Event.addListener(but, 'mouseover', function(){me.ButtonMouseOver(butId, butInnerId)});
        // Add mouse out
        YAHOO.util.Event.addListener(but, 'mouseout', function(){me.ButtonMouseOut(butId, butInnerId)});        
    }
	
	this.ButtonMouseOver=function(butId, butInnerId){
		var but=$GT(butId);
		var butInner=$GT(butInnerId);
		but.className=but.className.replace('DialogButton', 'DialogButton_hover');
		butInner.className=butInner.className.replace('DialogButtonInner', 'DialogButtonInner_hover');
	}

	this.ButtonMouseOut=function(butId, butInnerId){
		var but=$GT(butId);
		var butInner=$GT(butInnerId);		
		but.className=but.className.replace("DialogButton_hover","DialogButton");
		butInner.className=butInner.className.replace("DialogButtonInner_hover","DialogButtonInner");		
	}	
	
	this.IconButtonMouseOver=function(butId){
		var but=$GT(butId);
		but.className=but.className+"_hover";
	}
	
	this.IconButtonMouseOut=function(butId){
		var but=$GT(butId);
                if (!but){
                    return;
                }
		but.className=but.className.replace("_hover","");
	}
	
	this.CloseDialog=function(){

        // Add pre close function to dialog close button (button in toolbar)
		if (typeof(me.preCloseFunct)=='function'){
			var cont=me.preCloseFunct();
		}
        if (cont==false){
            return (false); // cancel close if preclose function returns false
        }
                
        // close children and only continue if closing of children was ok
        cont=me.CloseChildren(); // close child dialogs
        if (cont==false){
            return (false); // cancel close if preclose function returns false
        }       
        
        // unregister dialog
        me.unregisterDialog();       
        
	
		// Remove dark div if lightBox true and there are no other dialogs requiring the dark div		
        topdia=me.getTopmostDialog(true); // topmost dialog that is light boxed
        if (topdia!=null){
            // reset dark div zindex
            var darkDiv=$GT('darkDiv');
            if (darkDiv){
                darkDiv.style.zIndex=topdia.zIndex;
            }            
        } else {
            // remove dark div
    		if (me.lightBox){       
                me.RemoveDarkDiv();
    		}
        }
        
        // remove the dialog div
        me.RemoveDialogDiv();
        
        // Execute post close function
		if (typeof(me.postCloseFunct)=='function'){			
			me.postCloseFunct();
		}
        
        // remove from children list of parent dialog
        this.DetatchFromParent();
        
        return (true);
	}
    
    this.CloseChildren=function(){
        // close child dialogs
        while (typeof(me.children[0])!='undefined'){
            var success=me.children[0].CloseDialog();
            if (!success){
                return (false);
            }
        }        
        return (true);
    }
	
	this.MaximiseDialog=function(){
    
        this.updateWinDets();
        var hgt=(me._winDets.h)+"px";
		$GT(me.id).style.width="100%";
		$GT(me.id).style.height=hgt;
        
        // accomodate ie6
        this.IE6ZIndexBugFrameResize();
        
        var y=parseInt(me._winDets.y);        
		$GT(me.id).style.left="0px";
		$GT(me.id).style.top=y+"px";
		$GT(me.id+"_maximise").style.display="none"; // hide maximise
		$GT(me.id+"_minimise").style.display=""; // show minimise
		$GT(me.id+"_winDragExpContainer").style.display="none"; // hide size drag
		// resize dialog inner div
		$GT(me.id+"_inner").style.height=(parseInt($GT(me.id).offsetHeight)-parseInt($GT(me.id+"_toolBar").offsetHeight)-parseInt($GT(me.id+"_baseBar").offsetHeight))+"px";
        me.SetEdgeDims();
         // call back for resize
        if (typeof(me.postResizeFunct)=='function'){
            me.postResizeFunct();
        }
	}
	
	this.MinimiseDialog=function(){
		$GT(me.id).style.width=me.w+"px";
		$GT(me.id).style.height=me.h+"px";
		$GT(me.id).style.left=me.x+"px";
		$GT(me.id).style.top=me.y+"px";
		$GT(me.id+"_minimise").style.display="none"; // show maximise
		$GT(me.id+"_maximise").style.display=""; // hide minimise		
		$GT(me.id+"_winDragExpContainer").style.display=""; // show size drag
		// resize dialog inner div
		$GT(me.id+"_inner").style.height=(me.h-parseInt($GT(me.id+"_toolBar").offsetHeight)-parseInt($GT(me.id+"_baseBar").offsetHeight))+"px";		
        me.SetEdgeDims();
        
        // accomodate ie6
        this.IE6ZIndexBugFrameResize();
        
         // call back for resize
        if (typeof(me.postResizeFunct)=='function'){
            me.postResizeFunct();
        }
	}	

	this.RemoveDialogDiv=function(){
		var div=me.domObj;
        div.style.visibility="hidden"; // this line for the benefit of Opera
		try{
			var divParent=div.parentNode;
			divParent.removeChild(div);
		} catch(e){
			// nothing to close
		}
	}
	
	this.RemoveDarkDiv=function(){
        if ($GT('darkDiv')){
            $GT('darkDiv').style.visibility='hidden'; // this line for the benefit of opera (otherwise dark div remains visible after its removed!)
            document.body.removeChild($GT('darkDiv'));
        }
	}
	
	this.SetPrompt=function(newPrompt){
		if (typeof(newPrompt) == "string"){
			$GT(me.id+"_prompt").innerHTML=newPrompt;
		} else {
			$GT(me.id+"_prompt").innerHTML='';
			$GT(me.id+"_prompt").appendChild(newPrompt);
		}
	}
    
    this.SetTitle=function(newTitle){
        $GT(me.id+"_toolBarTitle").innerHTML=newTitle;
    }

    this.StatusLoading=function(loadingText){
    	if ($GT(me.id+'_loader')){
            me.StatusLoadingRemove();
        }
        var ldCont=GTLib.DOM.createEl('div', {id:this.id+'_loader', 'className':'mainLoader'});
        var ldTextNode=GTLib.DOM.createEl('div', {'style':'position:relative'});
        var ldImg=GTLib.DOM.createEl('div', {'className':'imgLoaderBar'});
        var ldText=loadingText ? loadingText : this.ldText;
        ldTextNode.innerHTML=ldText;
        ldCont.appendChild(ldTextNode);
        ldCont.appendChild(ldImg, {});
        $GT(me.id+"_prompt").style.display='none';
        me.domObj.appendChild(ldCont);
    }
    
    this.StatusLoadingRemove=function(){
        $GT(me.id+'_loader').parentNode.removeChild($GT(me.id+'_loader'));
        $GT(me.id+"_prompt").style.display='';
    }
    
    this.RemoveButtons=function(){
        var butCont=$GT(this.id+'_buttonContainer');
        butCont.parentNode.removeChild(butCont);
    }
    
    
    this.AddChildDialog=function(title,prompt,opts){

        var childNum=this.children.length;
        var opts=opts ? opts : {};
        
        // set zindex based on number of child dialogs spawned already
        if (this.zIndex){
            opts.zIndex=this.zIndex+(this.children.length+1);
        }
        
        // set position from options (defaults are applied later if not set)
        var x=opts.x;
        var y=opts.y;
        
        // set width from options
        var w=opts.w ? opts.w : 300; // default width is 300 pixels
        var h=opts.h ? opts.h : 200; // default height is 200 pixels
        
        // set id from options or default to number of dialog children
        var id=opts.id ? opts.id : this.id+"_"+childNum;
        
        // set lightboxing from options
        var lightbox=opts.lightbox ? opts.lightbox : false;
       
        // set toolbar buttons from options
        var tbButtons=opts.tbButtons ? opts.tbButtons : {close:true, minmax:true};
       
        // set buttons from options
        var buttons=opts.buttons ? opts.buttons : [{}];
        
        var chPosOffset=10; // pixels to offset multiple children by (staggerred windows)
    
        // Process x and y to include dialog instance offset
		if (x==null){
			var x=parseInt(YAHOO.util.Dom.getX(me.domObj))+(parseInt(me.domObj.offsetWidth/2)-parseInt(w/2))+(childNum*chPosOffset);
		}		
		if (y==null){			
			var y=parseInt(YAHOO.util.Dom.getY(me.domObj))+(parseInt(me.domObj.offsetHeight/2)-parseInt(h/2))+(childNum*chPosOffset);
		}
        
        opts.x=x;
        opts.y=y;
	
        var dg=new Dialog(title,prompt,opts);
        dg.id=id;
        dg.lightBox=lightbox;
        dg.tbButtons=tbButtons;
        var created=dg.Init();        
        if (created){
            dg.parentDialog=me;        
            this.children.push(dg); // only add to list of children if it was actually created.
            return (dg);
        } else {
            return (false);
        }
        
    }

    this.DetatchFromParent=function(){
        // remove from children list of parent dialog   
        if (me.parentDialog){
            var pKids=me.parentDialog.children; // parent children
            for (var p=0; p<pKids.length; p++){
                if (pKids[p].id==me.id){
                    me.parentDialog.children.splice(p,1); // remove from parents children
                    return; // exit
                }
            }
        }
    }
    
    this.FixAllIEPngBGs=function (){
        // I HATE IE6
        this.FixIEPngBG($GT(me.id+"_t_edge"));
        this.FixIEPngBG($GT(me.id+"_r_edge"));        
        this.FixIEPngBG($GT(me.id+"_b_edge"));         
        this.FixIEPngBG($GT(me.id+"_l_edge"));
        this.FixIEPngBG($GT(me.id+"_tlcorner"));
        this.FixIEPngBG($GT(me.id+"_trcorner"));
        this.FixIEPngBG($GT(me.id+"_blcorner"));
        this.FixIEPngBG($GT(me.id+"_brcorner"));  
    }
    
    this.FixIEPngBG=function (elmnt){
        // I HATE IE6
        if ((this.browser.ie6) && (document.body.filters)){
            bkg = (elmnt.background? elmnt.background : (elmnt.style.backgroundImage? elmnt.style.backgroundImage : elmnt.currentStyle.backgroundImage));
            bkg=bkg.replace('url(', '');
            bkg=bkg.replace(')', '');
            while (bkg.indexOf('"')>-1){
                bkg=bkg.replace('"', '');
            }
            elmnt.style.backgroundImage="url(images/blank.gif)"; // has to have a background image otherwise it wont be recognised on drag. (image doesn't even have to exist)
            elmnt.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+bkg+"', sizingMethod='scale');";
        }
        // END OF  I HATE IE6
    }

    this.FixIE6FieldZIndexBug=function(){
        // This iframe enables IE6 to float over fields
        if (this.browser.ie6){
            var frame=GTLib.DOM.createEl('iframe', {id:this.id+'frame'});
            frame.style.position='absolute';
            frame.style.zIndex='0';
            frame.style.top='5px';
            frame.style.left='5px';          
            var w=parseInt(this.domObj.style.width);
            var h=parseInt(this.domObj.style.height);
            frame.style.width=(w-10)+"px";
            frame.style.height=(h-10)+"px";
            frame.frameborder=0;
            this.domObj.appendChild(frame);
        }
    }
    
    this.IE6ZIndexBugFrameResize=function(){
        if (this.browser.ie6){
            var w=this.domObj.offsetWidth>0 ? this.domObj.offsetWidth : this.domObj.style.width;
            var h=this.domObj.offsetHeight>0 ? this.domObj.offsetHeight : this.domObj.style.height;            
            w=parseInt(w);
            h=parseInt(h);                 
            var frame=$GT(this.id+'frame');
            frame.style.width=(w-10)+"px";
            frame.style.height=(h-10)+"px";
        }
    }
	var me=this;
}

// Export dialog function
if (GTLib.ExportFuncs){
    Dialog=GTLib.Widget.Dialog;
}
