/**
 * Overwritten functions for fixing stuck header in collapsed topic format
 * CMDL-1358
 *
 *
 * 
 */


section_class.prototype.init_buttons = function() {
    var commandContainer = YAHOO.util.Dom.getElementsByClassName('right',null,this.getEl().childNodes[1])[0];

    //clear all but show only button
    var commandContainerCount = commandContainer.childNodes.length;

    for (var i=(commandContainerCount-1); i>0; i--) {
        if (i != 4) {
        commandContainer.removeChild(commandContainer.childNodes[i]);
        }
}

    if (main.getString('courseformat', this.sectionId) != "weeks" && this.sectionId > 0) {
        var highlightbutton = main.mk_button('div', '/i/marker.gif', main.getString('marker', this.sectionId));
        YAHOO.util.Event.addListener(highlightbutton, 'click', this.mk_marker, this, true);
        commandContainer.appendChild(highlightbutton);
        this.highlightButton = highlightbutton;
    }
//    if (this.sectionId > 0 ) {
//        var viewbutton = main.mk_button('div', '/i/hide.gif', main.getString('hidesection', this.sectionId), [['title', main.portal.strings['hide'] ]]);
//        YAHOO.util.Event.addListener(viewbutton, 'click', this.toggle_hide, this,true);
//        commandContainer.appendChild(viewbutton);
//        this.viewButton = viewbutton;
//}
}

section_class.prototype.add_handle = function() {
    var handleRef = main.mk_button('a', '/i/move_2d.gif', main.getString('movesection', this.sectionId),
            [['title', main.portal.strings['move'] ], ['style','cursor:move']]);

    YAHOO.util.Dom.generateId(handleRef, 'sectionHandle');

    this.handle = handleRef;

    this.getEl().childNodes[1].childNodes[0].appendChild(handleRef);
    this.setHandleElId(this.handle.id);
}


section_class.prototype.process_section = function() {
    this.content_td = this.getEl().childNodes[1].childNodes[1];

    if (YAHOO.util.Dom.hasClass(this.getEl(),'current')) {
        this.highlighted = true;
        main.marker = this;
    }

    // Create holder for display number for access later

    this.numberDisplay = document.createElement('div');
    this.numberDisplay.innerHTML = this.getEl().childNodes[1].childNodes[0].innerHTML;
    this.getEl().childNodes[1].childNodes[0].innerHTML = '';
    this.getEl().childNodes[1].childNodes[0].appendChild(this.numberDisplay);

    this.sectionId = this.id.replace(/section-/i, ''); // Okay, we will have to change this if we
    // ever change the id attributes format
    // for the sections.
    if (this.debug) {
        YAHOO.log("Creating section "+this.getEl().id+" in position "+this.sectionId);
    }

    // Find/edit resources
    this.resources_ul = this.content_td.getElementsByTagName('ul')[0];
    var i=0;    
    while (this.resources_ul && this.resources_ul.className != 'section img-text') {        
        this.resources_ul = this.content_td.getElementsByTagName('ul')[i];
        i++;
    }
    
    if (!this.resources_ul) {
        this.resources_ul = document.createElement('ul');
        this.resources_ul.className='section';
        this.content_td.insertBefore(this.resources_ul, this.content_td.lastChild);
    }
    var resource_count = this.resources_ul.getElementsByTagName('li').length;

    for (var i=0;i<resource_count;i++) {
        var resource = this.resources_ul.getElementsByTagName('li')[i];
        this.resources[this.resources.length] = new resource_class(resource.id, 'resources', null, this);
    }
    this.summary = YAHOO.util.Dom.getElementsByClassName('summary', null, this.getEl())[0].firstChild.data || '';
}

