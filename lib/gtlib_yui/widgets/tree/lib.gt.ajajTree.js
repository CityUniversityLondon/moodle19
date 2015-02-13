// GTLib (c) Guy Thomas 2007-2008  MIT Licence

// * Requires
// * yui

GTLib.Widget.AJAJTree = function(el, treeURL){

    // Establish short cut functions for this class
    if (typeof(createEl)=='undefined'){
        var createEl=GTLib.DOM.createEl;
    }

    var me={
        //
        // Constructor method
        //    
        constructor: function(el, treeURL) {
            this.treeURL=treeURL; // where is the tree initially going to get its data from?
            this.domObj;
            this.def_kids_req; // default AJAX request to retrieve / refresh child nodes
            this.elName;
            this.respObj; // ajax response object
            this.AJAX_url_root;
            this.currentId; // needed if there's an AJAX error
            this.docsSelectable; // boolean - can nodes be selected?
            this.foldersSelectable; // boolean - can folders be selected?
            this.rootSelectable; // boolean - can the root item be selected?
            this.selectedNodes; // nodes which are selected
            this.multiSelect; // boolean - can more than one document or folder be selected?    
            this.rootTitle; // title to show at top of tree
            this.rootImgClass; // class of root item
            this.w; // width
            this.h; // height
            this.ldText='Please wait. Loading Tree...';
            this.debug=false;
            
            this.selectedNodes= new Array ();
            
            if (this.AJAX_url_root){
                this.AJAX_url_root=this.closeRootURL(AJAX_url_root);
            } else {
                this.AJAX_url_root="";
            }
                    
            this.domObj=$GT(el);
            if (this.domObj){    
                this.w=parseInt(this.domObj.offsetWidth);
                this.h=parseInt(this.domObj.offsetHeight);    
                this.elName=this.domObj.id;
            } else {            
                this.elName=el;
                // set the element default dimensions
                this.w=!this.w ? 100 : parseInt(this.w);
                this.h=!this.h ? false : parseInt(this.h);
            }
         
        },

        init: function(){
            if (!this.domObj){
                this.domObj=createEl('div', {id:this.elName, 'class':'DHTMLTreeContainer'});               
                this.domObj.style.width=this.w+"px";            
                if (this.h){
                    this.domObj.style.height=this.h+"px";
                }
                document.body.appendChild(this.domObj);        
            }        
            
            // If multi select specified but docs & folders not selectable - force docs to be selectable
            if (this.multiSelect){
                if (!this.docsSelectable && !this.foldersSelectable){
                    this.docsSelectable=true;
                }
            }
            
            // Add loading image
            var ldCont=GTLib.DOM.createEl('div', {id:this.elName+'_loader', 'class':'mainLoader'});
            var ldText=GTLib.DOM.createEl('div', {'style':'position:relative'});
            var ldImg=GTLib.DOM.createEl('div', {'class':'imgLoaderBar'});
            ldText.innerHTML=this.ldText;
            ldCont.appendChild(ldText);
            ldCont.appendChild(ldImg, {});
            this.domObj.appendChild(ldCont);
                   
            this.requestTree();            
            this.rootImgClass=!this.rootImgClass ? "root_def" : this.rootImgClass;         
            
        },
        
        //
        // Purpose: Make sure the url ends with a forward slash
        //
        closeRootURL: function(url){        
            if (url!=""){
                if (url.charAt(url.length-1)!="/"){
                    url=url+"/";
                }
            }
            return (url);
        },
        
        //
        // Purpose: Returns true if url has http:// protocol prefix
        //
        URLHasProtocol: function(url){
            return(url.indexOf('http://')>-1 || url.indexOf('https://')>-1);            
        },
        
        //
        //  Purpose: Send request asking for tree data
        //
        requestTree: function(){                
            var me=this;            
            AJAXPost(this.treeURL, '', {success:function(o){me.LoadTreeData(o)}, failure:function(){alert('failed '+me.treeURL)}})            
        },
        

        HandleErrorDHTML: function(id){
        
            var ldDiv=$GT(this.elName+"_"+"ldr_"+id);
            if (ldDiv){
                var parent=ldDiv.parentNode;
                parent.removeChild(ldDiv);    
            } 
            
        },
        
        LoadTreeData: function(o){
            // Remove loading title
            this.domObj.removeChild($GT(this.elName+'_loader'));
                            
            // If debug on then //log response
            if (this.debug){
                //log(o.responseText);
            }
            
            // evaluate javascript response
            eval(o.responseText);
            
            if (response.criticalError){
                alert ('The following critical error has occurred:'+response.criticalError);
            }
                    
              
            this.respObj=response;
            
            if (response.criticalError){
                alert (response.criticalError);
                return(false);
            }
     
            if (response.type!="dhtml_tree"){
                alert ('error - response is not for a dhtml tree');
                return;
            }
            
        
            // Get default request for retrieving and refreshing child nodes
            this.def_kids_req=trim(response.kidsAjax);
            
                    
            //Add tree div, buttons, etc to document        
            treeUl=createEl('ul',{id:this.elName+"_tree"});
            treeUl.style.minWidth=(this.w-2)+"px"; // note set minimum width - not width (this enables tree to grow inside scroll area)
            treeUl.style.clear='both';
            treeUl.style.overflow="visible";            
            treeUl.className='DHTMLTree';
     
            this.domObj.appendChild(treeUl);
     
            if (this.rootTitle){
                this.addRootTitle();
            }
            
            this.ParseTreeBranch(this.respObj, 0, treeUl); // parse root of tree
            
            var el=$GT(this.elName+"_tree");
        },
        
        
        addRootTitle: function(){
            var idpfx=this.elName+"_";
            var nodeLi=createEl('li', {id:idpfx+'root', 'class':'nodeLi'});
            if (GTLib.Browser.ie){
                nodeLi.style.height=nodeLi.style.minHeight;
            }
            var nid=-1;
            var imgDiv=createEl('div', {id:idpfx+"img_"+nid, 'class':'treenode '+this.rootImgClass});
            var txtDiv=createEl('div', {id:idpfx+"txt_"+nid, 'class':'nodeTxt'});
            txtDiv.innerHTML=this.rootTitle;
                        
            var dragDiv=createEl('div', {style:'position:relative; float:left', 'class':'dragDiv'});
                        
            treeUl.appendChild(nodeLi);            
            dragDiv.appendChild(imgDiv);
            dragDiv.appendChild(txtDiv);
            nodeLi.appendChild(dragDiv);
                        
            this.ApplyNodeEvents('root', nid, txtDiv);            
            
            return (nodeLi);
        },
        
        
        GetNodeById: function(node, id){
                
            var chkId=this.GetNodeId(node);
            if (chkId==id){
                return(node);
            } // if found node then return it

            if (!node.data || node.data==null){
                return (false);
            }
            
            var kids=node.data;
            
            for (var c=0; c<=kids.length; c++){
            
                var kid=kids[c];
                if (kid!=null){
                    var kidId=this.GetNodeId(kid);
                    if (kidId==id){                
                        return (kid); // if found node then return it
                    }
                    
                    // recurrsive call - check kids kids to see if the node exists there
                    var foundNode=this.GetNodeById(kid,id);
                    if (foundNode){
                        return (foundNode);
                    }
                }
            }    
            return (false);            
        },
        
        LoadBranchData: function(o){
        
            // If debug on then //log response
            if (this.debug){
                //log(o.responseText);
            }
        
            eval(o.responseText);
                   
            if (response.criticalError){
                alert (response.criticalError);
                return (false);
            }

            var branchData=response.data;
                        
            // get branch parent id - we need to know where to stick this data!
            var branchPId=trim(response.branchParent);
            branchPId=this.deTokenise(branchPId);
                    
            // remove any existing children to parentNode
            var parentNode=this.GetNodeById(this.respObj, branchPId);
            var parentNode=this.removeChildNodes(parentNode);
          
            // add branch data to parentNode        
            var parentNode=this.addChildNodes(parentNode, branchData);

            // render dhtml
            var dNode=$GT(this.elName+"_"+branchPId);
            depth=this.nodeDepth(parentNode);
            var kUl=$GT(this.elName+"_"+"kids_"+branchPId); // ul for containing children to branch
                    
            this.ParseTreeBranch(parentNode, depth+1, kUl); // apply xml nodes to dhtml tree object        
            this.openFolderDisp(branchPId); // open dhtml folder
            
            // remove loader image and text
            var ldDiv=$GT(this.elName+"_"+"ldr_"+branchPId);
            var parent=ldDiv.parentNode;
            parent.removeChild(ldDiv);
            
        },
        

        isLastNodeInBranch: function(node){
            var pnode=node.parentNode;
            if (typeof(pnode.tagName)=="undefined"){
                return (false);
            }
            var nodes=pnode.childNodes;
            var lastNode=this.getLastNode(nodes);
            
            
            // is the last node of nodes parents children the same as node?                
            if (node==lastNode){
                return (true);
            } else {
                return (false);
            }
        },
        
        getLastNode: function(nodes){
            // Gets last node with tag name 'node' from array of nodes
            for (n=nodes.length-1; n>0; n--){ // length-1 because length isn't 0 based but the array is
                if (nodes[n].tagName=='node'){
                    return (nodes[n]);
                }
            }
            return (false); // if there is no node in array with tagName 'node' return false
        },
        
        nodeDepth: function(node){
            // this works out how deep a node of type 'node' is
            var depth=0;
            var pnode=node;        
            do {
                var pnode=pnode.parent;
                depth=depth+1;                    
                
            } while (pnode)        
            return (depth);
        },
        
        treeUl: function(){
            return($GT(this.elName));
        },
        
        addChildNodes: function(node, children){
            node.data=children;        
            return (node);
        },
        
        removeChildNodes: function(node){                
            node.data=null;        
            return (node);
        },

        //
        // Author: Guy thomas
        // Date: 2006 April 1st
        // Purpose: Parse this branch, implementing all children (this is done via WriteTreeItem calling back ParseTreeBranch for sub branches)
        //        
        ParseTreeBranch: function(branch, depth, appendTo){
            var nodes=branch.data;            
            if (nodes==null){return;} // if no nodes found then exit                
            for (var n=0; n<=nodes.length; n++){            	
                if (nodes[n]!=null){
                    var nid=this.ApplyNodeId(nodes[n], depth, n);
                    this.WriteTreeItem(nodes[n], depth, nid, appendTo, nodes.length-1);
                }
            }
        },
            
        
        //
        // Author: Guy thomas
        // Date: 2006 April 2nd
        // Purpose: If node has an id then return it, otherwise assign one according to current branch depth of tree and position in branch
        //        
        ApplyNodeId: function(node, depth, n){
            var nid=this.GetNodeId(node);
            
            if (nid==""){
                //alert ("no nid");
                // if nid could not be found then create one according to current depth of tree and position in branch
                var nid=depth+"_"+n;
                // create node id
                node.id=nid;
            }
            return (nid);
        },
        
        GetNodeId: function(node){
            /**
            /* Author: Guy thomas
            /* Date: 2006 April 2nd
            /* Purpose: Attempts to retrieve a nodes id (if it has one)
            **/        
            try {
                var id=node.id;
            }    catch (e) {
                var id="";
            }
            return (id);
        },
        
        WriteTreeItem: function(node, depth, nid, appendTo, numNodes){
                    
            var idpfx=this.elName+"_"; // id prefix
        
            // get node data direct from node object (new node)
            var nodename=node.name;
            var nodetype=node.type;
            var nodelink=node.linkId ? node.linkId : -1;
                            
            var linkAppend=nodelink!=-1 ? "linked" : ""; // append css node class with linked
            var nodetypecss=nodetype+linkAppend; // css node type class (folder becomes folderlinked if node is linked)
            if (nodetype=="file"){
                var nodeext=""+node.extension;
                var usenodeext=" ext_"+nodeext;
            } else {
                var usenodeext="";
            }
            
            switch (nodetype){
                case "doc" : var brCls="branch leaf"; break;
                case "link" : var brCls="branch leaf"; break;
                case "file" : var brCls="branch leaf"; break;            
                case "folder" : var brCls="branch node"; break;
            }
            

            // DHTML tree node will have exactly the same id as XML node (except prefixed with tree object name)    
            var nodeLi=createEl('li', {id:idpfx+nid, 'class':'nodeLi'});                            
            var branchDiv=createEl('div', {id:idpfx+"branch_"+nid, 'class':brCls});            
            var dragDiv=createEl('div', {style:'position:relative; float:left', 'class':'dragDiv'});            
            var imgDiv=createEl('div', {id:idpfx+"img_"+nid, 'class':'treenode img_'+nodetypecss+usenodeext});           
            var txtDiv=createEl('div', {id:idpfx+"txt_"+nid, 'class':'nodeTxt'});
            txtDiv.innerHTML=nodename;
            
            this.ApplyNodeEvents(nodetype, nid, txtDiv);        
            appendTo.appendChild(nodeLi);
                                    
            dragDiv.appendChild(imgDiv);    
            dragDiv.appendChild(txtDiv);
            
            nodeLi.appendChild(branchDiv);
            nodeLi.appendChild(dragDiv);
                                    
            if (nodetype=="folder"){
                this.ApplyFolderEvent(node, branchDiv);
                this.ApplyFolderEvent(node, imgDiv);
                var kidsUl=createEl('ul', {id:this.elName+"_"+"kids_"+nid, style:'display:none', 'class':'branchContainer'});                
                nodeLi.appendChild(kidsUl); // add container for storing children
                if (!node.newNode){
                    this.ParseTreeBranch(node, depth+1, kidsUl);// recurrsive call
                }
            }
            
            // return node list item and drag div - necessary for editable version of tree (ajajTreeEditable)
            return ({nodeLi:nodeLi, dragDiv:dragDiv});
            
        },
        

        //
        // Returns true if the element is a folder and its open (element must be li with class nodeLi)
        //
        FolderIsOpen: function(el){
            if (el.className!='nodeLi'){
                return (false);
            }
            
            // get element id
            var id=el.id.replace(this.elName+"_", "");
            
            // get branch
            var branch=$GT(this.elName+"_"+"branch_"+id);
            if (!branch){
                // if no branch item then its not a folder
                return (false);
            }
            
            if (hasElementClass(branch, "node_exp") || hasElementClass(branch, "node_exp_end")){
                return (true);
            }
        },
                    
        
        TreeNodeText: function(treeNode){
            // return text of tree node
            var txtNode=getFirstElementByTagAndClassName('div', 'nodeTxt', treeNode);
            return (txtNode.innerHTML);
        },


        VisChildrenByTagAndClass: function(parent, tag, className, visibility){
            var kids=getElementsByTagAndClassName(tag, className, parent);
            for (k=0; k<kids.length; k++){
                var kid=kids[k];
                kid.style.visibility=visibility;
            }        
        },

        
        //
        // Author: Guy thomas
        // Date: 2006 April 2nd
        // Purpose: Apply on click event to folder (calls folder clicked function)
        //        
        ApplyFolderEvent: function(node,clickDiv){
            var me=this;
            YAHOO.util.Event.addListener(clickDiv, 'click', function(){me.folderClicked(node)});                
        },
        
        //
        // Author: Guy thomas
        // Date: 2006 April 7th
        // Purpose: Apply mouse over and mouse out events to node
        //        
        ApplyNodeEvents: function(nodetype, nid, txtDiv){
            var me=this;
            var apply=false;
            
            if (nodetype=="folder" && this.foldersSelectable){
                apply=true;
            } else if (nodetype=="root" && this.rootSelectable){
                apply=true;
            } else if (nodetype=="doc" && this.docsSelectable){
                apply=true;
            }
            
            if (apply){            
                YAHOO.util.Event.addListener(txtDiv, 'mousedown', function(e){me.NodeMouseDown(e, nid)});
            }
            
        },
        
 
        //
        // Author: Guy thomas
        // Date: 2006 April 7th
        // Purpose: Apply mouse click events to node
        //  
        NodeMouseDown: function(e, id){
       
        
            if (!e) var e = window.event;
        
            // work out width of menu - left of txt and set size
            var treeUl=$GT(this.elName);        
            var txtDiv=$GT(this.elName+"_"+"txt_"+id);
                    
            if (!txtDiv){return;}
            if (txtDiv.className.indexOf("_selected")==-1){
                this.doSelect(txtDiv,id);            
            } else {
                // only deselect if right button was not clicked
                if (e.which){
                    var rightclick = (e.which == 3);
                } else if (e.button) {
                    var rightclick = (e.button == 2);
                }
                
                if (!rightclick){
                    this.doDeSelect(txtDiv,id);
                }

            }
            
        },
        
        doSelect: function(txtDiv,id){

            if (!this.multiSelect){
                // remove existing selected item
                if (this.selectedNodes[0]){
                    var prevNodeId=this.selectedNodes[0];
                    var prevNode=$GT(this.elName+"_"+"txt_"+prevNodeId);
                    this.doDeSelect(prevNode,prevNodeId);
                }
            }

            txtDiv.className=txtDiv.className.replace("_hover","");
            txtDiv.className=txtDiv.className+"_selected";
            this.selectedNodes.push(id);
        },
        
        arrayPos: function(arr, itm){
            for (a=0; a<=arr.length; a++){
                var chkitem=arr[a];
                if (typeof(chkitem)!="undefined"){
                    if (chkitem==itm){
                        return (a);
                    }
                }
            }
            return (-1);
        },
        
        doDeSelect: function(txtDiv,id){
            txtDiv.className=txtDiv.className.replace("_selected","");
            var aPos=this.arrayPos(this.selectedNodes, id);
            if (aPos>-1){
                this.selectedNodes.splice(aPos, 1);
            }

        },
        
        folderClicked: function(node){
            if (node.state=="opened"){            
                this.closeFolder(node);            
            } else {
                this.openFolder(node);            
            }        
            return;        
        },
        
        //
        // Open folder by xml node
        //
        openFolder: function(node){
        
            var nid=node.id;

            node.state="opened";
                    
            if (node.data==null && !node.newNode){
                // get by ajax
                this.openFolderAJAX(nid, node);
            } else {
                // unhide
                this.openFolderDisp(nid);
            }
        },
        
        //
        // Open a folder by id instead of xml node ref
        //
        openFolderById: function(id){
            var node=this.GetNodeById(this.respObj, id);
            this.openFolder(node);
        },

        appendToClassPreserveEnd: function(str, appStr){
            // appends a string to a class name but keeps _end at the end
            var addEnd=false;
            if (str.indexOf("_end")>-1){
                str=str.replace("_end","");
                var addEnd=true;
            } 
            str+=appStr;
            str+=addEnd ? "_end" : "";
            return (str);
        },
            
        openFolderDisp: function(nid){
            
            // Change image classes to opened
            var branchDiv=$GT(this.elName+"_"+"branch_"+nid);
            branchDiv.className=this.appendToClassPreserveEnd(branchDiv.className,"_exp");
            
            var imgDiv=$GT(this.elName+"_"+"img_"+nid);
            imgDiv.className=imgDiv.className+"_exp";        
            
            var kNode=$GT(this.elName+"_"+"kids_"+nid);
            kNode.style.display="block";
        },
        
        openFolderAJAX: function(nid, node){
        
               
            // Add loading text to folder
            var imgDiv=$GT(this.elName+"_"+"img_"+nid); // get image div
            var dNode=$GT(this.elName+"_"+nid);
            var parDiv=imgDiv.parentNode.parentNode; // get image div's container
            var ldDiv=createEl('div', {id:this.elName+"_"+"ldr_"+nid});            
            var depth=this.nodeDepth(node)+1;
            if (GTLib.Browser.ie){
                ldDiv.style.margin="0px 0px 0px 0px";
                ldDiv.style.border="0px none transparent";
            } else {
                ldDiv.style.margin="0px 0px 0px -1px";
                ldDiv.style.border="1px solid transparent";
            }
            var csname=depth==0 ? "branch spacer": "branch";
            
            var txtDiv=createEl('div', {'class':'ajax_loader_text'});
            txtDiv.innerHTML="loading...";            
            txtDiv.style.margin="0px 0px 0px 5px";
            var ldImgDiv=createEl('div', {'class':"imgLoader"});
            var kUl=$GT(this.elName+"_"+"kids_"+nid);        
                    
            // if no kids div then create one
            if (!kUl){
                var kidsUl=createEl('ul', {id:this.elName+"_"+"kids_"+nid, 'class': 'branchContainer', style:'display:block'});                
                dNode.appendChild(kidsDiv); // add container for storing children
            }
                    
            kUl.style.display="block"; // unhide kids div
                    
            ldDiv.appendChild(ldImgDiv);
            ldDiv.appendChild(txtDiv);        
            ldDiv.style.height="16px";
            //ldDiv.style.border="0px none";
            kUl.appendChild(ldDiv);
            
            
            // Get request for retrieving and refreshing child nodes
            var kids_req=node.kids_ajax_override ? node.kids_ajax_override : this.def_kids_req; // if nothing detailed for retrieving children, use default


            kids_req=this.convertTokens(kids_req, nid)
            
            // if the url is not relative to this site - i.e it is an absolute url with a protocol -e.g. http://www.google.com then dont prefix with AJAX root url
            if (!this.URLHasProtocol(kids_req)){
                kids_req=this.AJAX_url_root+kids_req; // prefix with ajax root url
            } else {
                // make sure that protocol for kids_req is the same as treeURL
                // or it will cause problems
                if (this.treeURL.indexOf('http://')>-1){
                    kids_req=kids_req.replace('https://','http://');
                } else if (this.treeURL.indexOf('https://')>-1){
                    kids_req=kids_req.replace('http://','https://');
                }
            }
                                
            
            // Send request asking for branch data                            
            var sendStr="pid="+this.tokenise(nid);
            //log ("kids ajaj req = "+kids_req);
            var me=this;            
            AJAXPost(kids_req, sendStr, {success:function(o){me.LoadBranchData(o); me.currentId=nid;}, failure:function(){alert('failed: '+kids_req)}})

        },
        

        
        convertTokens: function(s, nid){
            // Converts tokens and value identifiers in a string
            var node=this.GetNodeById(this.respObj, nid);
            s=s.replace("[@id]", nid);
            s=this.deTokenise(s); // convert ampersand token back        
            if (node){
                s=this.extractNodeValues(s, node);
            }
            return (s);
        },
        
        tokenise: function(s){
            s=String(s);
            s=s.replace("&", "~~AMP~~"); // convert & to token
            return (s);
        },
        
        deTokenise: function(s){
            s=String(s);
            s=s.replace("~~AMP~~", "&"); // convert token to &
            return (s);
        },
        
        extractNodeValues: function(s, node){
            /**
            /* Author: Guy Thomas
            /* Date: May 17th 2006
            /* Purpose:
            /* converts node value identifiers to actual node values - e.g. [#name] to the nodes name
            /* PARAMS IN
            /* s = string to replace node value identifiers with actual node values
            /* node = the node to get the values of
            **/
            var tmpArr= new Array();
            s=String(s);
            tmpArr=s.split("]");
            var newStr="";
            for (t=0; t<tmpArr.length-1; t++){
                var tmpStr=tmpArr[t];
                var niPos=tmpStr.indexOf("[#");
                if (niPos>-1){// node value identified
            
                    // Get node value name
                    var nodeValNm=tmpStr.substring(niPos+2); // niPos+2 to exclude [# from node value name
                    
                    // Get node value
                    var nodeVal=""+node.getElementsByTagName(nodeValNm)[0].childNodes[0].nodeValue;

                    // Get string without node value identifier
                    var noNode=tmpStr.substring(0, niPos);
                    
                    // convert array item to string with actual node value
                    tmpArr[t]=noNode+nodeVal;

                } else {
                    // no node value identified so add ] back on to string
                    tmpArr[t]=tmpArr[t]+"]";    
                }
                newStr+=tmpArr[t];
            }
            if (tmpArr.length-1==0){
                return (s); // return original string - no node value identifiers detected
            }
            return (newStr); // return new string with node value identifiers converted to actual node values            
        },
        
        //
        // close folder by xml node
        //
        closeFolder: function(node){
            
            var nid=node.id;
            
            // Change image classes to closed
            var branchDiv=$GT(this.elName+"_"+"branch_"+nid);
            branchDiv.className=branchDiv.className.replace("_exp","");
            
            var imgDiv=$GT(this.elName+"_"+"img_"+nid);
            imgDiv.className=imgDiv.className.replace("_exp","");        
            
            node.state="closed";
            var dNode=$GT(this.elName+"_"+"kids_"+nid);
            dNode.style.display="none";
        },
        
        
        //
        // close a folder by id instead of xml node ref
        //
        closeFolderById: function(id){
            //log("Closing folder "+id);
            var node=this.GetNodeById(this.respObj, id);
            this.closeFolder(node);
        },
        
        //
        // return array of selected nodes (actual nodes, not just ids)
        //
        getSelectedNodes: function(){
            var sv=new Array();        
            for (s=0; s<this.selectedNodes.length; s++){
                var ndId=this.selectedNodes[s];
                var nd=this.GetNodeById(this.respObj, ndId);            
                sv[s]=nd;
            }
            return (sv);
        }
    }
    me.constructor(el, treeURL);
    return (me);
}


// Export tree function
if (GTLib.ExportFuncs){
    AJAJTree=GTLib.Widget.AJAJTree;
}
