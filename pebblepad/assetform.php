<?php
/* 
 * Builds a form based on asset type for users to populate.
 */
class assetForm{

    protected $hasTitle;
    protected $hasDescription;
    protected $hasStartDate;
    protected $hasEndDate;
    protected $hasReason;
    protected $hasKnowgledge;
    protected $hasImpact;
    protected $hasEvidence;
    protected $hasReflection;
    protected $hasFile;

    function __construct() {
        $this->hasTitle = false;
        $this->hasDescription = false;
        $this->hasStartDate = false;
        $this->hasEndDate = false;
        $this->hasReason = false;
        $this->hasKnowgledge = false;
        $this->hasImpact = false;
        $this->hasEvidence = false;
        $this->hasReflection = false;
        $this->hasFile = false;
    }

    function set_hasTitle($bool){
        $this->hasTitle = $bool;
    }
     function get_hasTitle(){
        return $this->hasTitle;
    }

    function set_hasDescription($bool){
        $this->hasDescription = $bool;
    }
    function get_hasDescription(){
        return $this->hasDescription;
    }

    function set_hasStartDate($bool){
        $this->hasStartDate = $bool;
    }
    function get_hasStartDate(){
        return $this->hasStartDate;
    }

    function set_hasEndDate($bool){
        $this->hasEndDate = $bool;
    }
    function get_hasEndDate(){
        return $this->hasEndDate;
    }

    function set_hasReason($bool){
        $this->hasReason = $bool;
    }
    function get_hasReason(){
        return $this->hasReason;
    }

    function set_hasKnowgledge($bool){
        $this->hasKnowgledge = $bool;
    }
    function get_hasKnowgledge(){
        return $this->hasKnowgledge;
    }

    function set_hasImpact($bool){
        $this->hasImpact = $bool;
    }
    function get_hasImpact(){
        return $this->hasImpact;
    }

    function set_hasEvidence($bool){
        $this->hasEvidence = $bool;
    }
    function get_hasEvidence(){
        return $this->hasEvidence;
    }

    function set_hasReflection($bool){
        $this->hasReflection = $bool;
    }
    function get_hasReflection(){
        return $this->hasReflection;
    }

    function set_hasFile($bool){
        $this->hasFile = $bool;
    }
    function get_hasFile(){
        return $this->hasFile;
    }

}

function getForm($form, $userInput){
    global $CFG;
    if ($userInput == null){
        $userInput = new assetInput();
    }

    $data = '';
    $data.= '<tr><td>&nbsp;</td></tr>';

    $data.= '<tr><td>title</td></tr>';
    $data.= '<tr><td><input style="width:100%"; type=text name="title" value="'.stripslashes($userInput->get_title()).'" /></td></tr>';
    
    if ($form->get_hasDescription()){
        $data.= "<tr><td >description</td></tr>";
        $data.= "<tr><td ><textarea style='width:100%' name='description' rows=3 >".stripslashes($userInput->get_description())."</textarea></td></tr>";
    }
    //dates should always contain both start and end for now
    if ($form->get_hasStartDate()){
        $data.= "<tr><td><img id='dat' style='cursor:pointer;' onclick=toggle(this) src='".$CFG->wwwroot."/pebblepad/images/plus.gif' alt='show/hide' title='expand'> add dates</td></tr>";
        $data.= "<tr id='dat1' style='display:none;'><td><table  class='assettable' style='width:100%;'>";
        $data.= "<tr><td>&nbsp;start date <input type=text name='startdate' id='startdate' class='date-pick' size=16 value='".stripslashes($userInput->get_startDate())."' /></td></tr>";
    }    
    if ($form->get_hasEndDate()){
        $data.= "<tr><td >&nbsp;end date <input type=text name='enddate' id='enddate' class='date-pick' size=16 value='".stripslashes($userInput->get_endDate())."' /></td></tr>";
        $data.= "</table></td></tr>";
    }
    
    if ($form->get_hasReason()){
        $data.= "<tr><td >reason</td></tr>";
        $data.= "<tr><td><textarea style='width:100%' name='reason' rows=3>".stripslashes($userInput->get_reason())."</textarea></td></tr>";
    }

    if ($form->get_hasKnowgledge()){
        $data.= "<tr><td >knowledge</td></tr>";
        $data.= "<tr><td><textarea style='width:100%' name='knowledge' rows=3>".stripslashes($userInput->get_knowgledge())."</textarea></td></tr>";
    }

    if ($form->get_hasImpact()){
        $data.= "<tr><td >impact</td></tr>";
        $data.= "<tr><td><textarea style='width:100%' name='impact' rows=3>".stripslashes($userInput->get_impact())."</textarea></td></tr>";
    }

    if ($form->get_hasEvidence()){
        $data.= "<tr><td >evidence</td></tr>";
        $data.= "<tr><td ><textarea style='width:100%' name='evidence' rows=3>".stripslashes($userInput->get_evidence())."</textarea></td></tr>";
    }

    if  ($form->get_hasReflection()){

        $data.= "<tr><td>reflection</td></tr>";
        $data.= "<tr><td><textarea style='width:100%' name='reflection' rows=3>".stripslashes($userInput->reflection)."</textarea></td></tr>";
        $data.= "<tr><td><img id='ref' style='cursor:pointer;' onclick=toggle(this) src='".$CFG->wwwroot."/pebblepad/images/plus.gif' alt='show/hide' title='expand'> hrs, points</td></tr>";
        $data.= "<tr id='ref1' style='display:none;'><td><table  class='assettable' style='width:100%;'>";

        $data.= "<tr><td>total hours spent on this activity</td></tr>";
        $data.= "<tr><td><input type=text name='hrs' size=4  maxlength=5 value='".stripslashes($userInput->hrs)."' />&nbsp;hrs&nbsp;&nbsp;";
        $data.= "<input type=text name='mins' size=4 maxlength=2  value='".stripslashes($userInput->mins)."'  />&nbsp;mins</td></tr>";

        $data.= "<tr><td>this activity is worth&nbsp;<input type=text name='points' size=4 maxlength=5 value='".stripslashes($userInput->points)."' />&nbsp;points</td></tr>";

        $data.= "</td></tr></table>";
    }
    
    if ($form->get_hasFile()){
        $data.= "<tr><td><br />select file&nbsp;<input style='width:300px;' type=File name='file' /></td></tr>";
    }

    return $data;
}
?>
