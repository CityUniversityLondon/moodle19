// Get the image link from within its (parent) container.
function getImage(parent) {
            var el = parent.firstChild;
                    
            while (el) { // walk through till as long as there's an element
                if (el.nodeName.toUpperCase() == "IMG") { // found an image
                    // flickr uses \"_s\" suffix for small, and \"_m\" for big
                    // images respectively
                    return el.src.replace(/_s\.jpg$/, "_m.jpg");
                }
                el = el.nextSibling;
            }
            
            return "";
        }
		
		
		
		
		function getAlt(parent) {
            var el = parent.firstChild;
                    
            while (el) { // walk through till as long as there's an element
                if (el.nodeName.toUpperCase() == "IMG") { // found an image
                    return el.alt;
                }
                el = el.nextSibling;
            }
            
            return "";
        }
		
		
		// function to prepare the carousel for display
		function setupCarousel(carousel_el) {
		
               var carousel    = new YAHOO.widget.Carousel(carousel_el, {
														animation: { speed: 0.5 },
														isCircular: true
														});
 
					   var spotlight = YAHOO.util.Dom.getNextSiblingBy(carousel_el, function(e) {
							return e.className == 'carousel_spotlight';
						});
					   
					   spotlight = YAHOO.util.Dom.getNextSibling(YAHOO.util.Dom.getNextSibling(carousel_el));
					   
					   YAHOO.util.Dom.removeClass(carousel_el, 'js-disabled'); 	
					   YAHOO.util.Dom.removeClass(spotlight, 'js-disabled'); 	
					   
            carousel.on("itemSelected", function (index) {
                // item has the reference to the Carousel's item
                var item = carousel.getElementForItem(index);
 
                if (item) {
                    spotlight.innerHTML = "<img src='"+getImage(item)+"'><br/><div class='flickr_image_carousel_title'>"+getAlt(item)+"</div>";
                }
												  
            });

			carousel.render(); // get ready for rendering the widget
			carousel.show();
		};
		
		//function used to strip out the extra navigation buttons when more than one carousel is on a page
		// basically a bug fix!
		function removeExtraNavButtons(carousel_el) {
			
			
			var nav = YAHOO.util.Dom.getFirstChild(carousel_el);
			
			var extra_buttons = YAHOO.util.Dom.getChildren(nav);
			
			for (i=3; i < extra_buttons.length; i++) {
				nav.removeChild(extra_buttons[i]);
			}
		}
	
		
		
                
        YAHOO.util.Event.onDOMReady(function (ev) {
											  
    		carousels = YAHOO.util.Dom.getElementsByClassName('carousel_container');
			//render the carousels
    		for(i = 0; i < carousels.length; i++) {
      			setupCarousel(carousels[i]);
    		}
			
			//strip out the extra (useless) navigation buttons
			for(j = 0; j < carousels.length; j++) {
      			removeExtraNavButtons(carousels[j]);
    		}
			
			
			
		});
		

		
		
		
		
		
											  