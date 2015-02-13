
function quickfindsearch(roleid){
    searchbox = document.getElementById('quickfindlistsearch'+roleid);
    searchstring= new RegExp(searchbox.value,'gi');
    var quickfindlist = document.getElementById('quickfindlist'+roleid);
    for (var person = quickfindlist.firstChild; person != null; person = person.nextSibling) {
        // Do stuff with person.
        if((person.innerHTML.search(searchstring) == -1)||(searchbox.value =='')){
            person.style.display="none";
        }else{
            person.style.display="list-item";
        }
    }
}