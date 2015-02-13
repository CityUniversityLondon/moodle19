(function () {

    /**
    * YuiDialog is a simple implementation of Dialog that can be used to 
    * submit a single value. Forms can be processed in 3 ways -- via an 
    * asynchronous Connection utility call, a simple form POST or GET, 
    * or manually.
    * @namespace GTLib.Widget
    * @class YuiDialog
    * @extends GTLib.Widget.YuiDialog
    * @constructor
    * @param {String} el The element ID representing the YuiDialog 
    * <em>OR</em>
    * @param {HTMLElement} el The element representing the YuiDialog
    * @param {Object} userConfig The configuration object literal containing 
    * the configuration that should be set for this YuiDialog. See 
    * configuration documentation for more details.
    */
	GTLib.Widget.YuiDialog = function (el, userConfig) {
    
		// force defaults
		var force_defaults={
	        'zindex':'9999',
	        'modal':true,
	        'fixedcenter':true,
	        'width':'300px',
	        'height':'150px'
		};		
		for (var f in force_defaults){
			if (typeof(userConfig[f])=='undefined'){
				userConfig[f]=force_defaults[f];
			}			
		}
		
        GTLib.Widget.YuiDialog.superclass.constructor.call(this, 
            el, userConfig);
    
    };
    
    

    var Dom = YAHOO.util.Dom,
        YuiDialog = GTLib.Widget.YuiDialog;
    
 
     
     YAHOO.extend(YuiDialog, YAHOO.widget.SimpleDialog, {
     
         // @todo - add a maximize button
         
    	 /**
    	  * add child dialog
    	  */
    	 addChild:function(el, userConfig){
			var cdg=new GTLib.Widget.YuiDialog("cdg1", userConfig);     
			cdg.render(document.body);
			cdg.body.style.overflow='scroll';
			return (cdg);
     	 }
     	
     

     });

 }());    