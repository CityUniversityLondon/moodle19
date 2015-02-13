
function quickcoursesearch(){
    searchbox = document.getElementById('quickcourselistsearch');
    searchstring= new RegExp(searchbox.value,'gi');
    var quickcourselist = document.getElementById('quickcourselist');
    for (var course = quickcourselist.firstChild; course != null; course = course.nextSibling) {
        // Do stuff with course.
        if((course.title.search(searchstring) == -1)||(searchbox.value =='')){ // CHANGED AD
        //if((course.innerHTML.search(searchstring) == -1)||(searchbox.value =='')){
            course.style.display="none";
        }else{
            
            course.style.display="list-item";
        }
    }
}