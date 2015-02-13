<?php
/*********************************************************************************
 *   Filename: sitecheck.inc.php
 *   Version 1.0
 *   Copyrights 2004-2005 (c) phpSiteTools.com
 *   Powered by ServiceUptime.com
 *   Last modified: 08.24.2005
 * 
 *   Installation:
 *      1. Copy sitecheck.inc.php and checklib.inc.php files to your site folder  
 *      2. Configure settings at the bottom of sitecheck.inc.php if necessary
 *      3. Add the following PHP code into your PHP page code:
 *            include("./sitecheck.inc.php");
 *   You can also check our example.php script.
 *********************************************************************************/

require_once("./checklib.inc.php");

// phpSiteCheck tool settings

// Allowed types of checks. 
// Possible values: 'http', 'ftp', 'smtp', 'pop3'
// Remove service from this array if you do not allow to check it
$allowed_services = array('http', 'ftp', 'smtp', 'pop3'); 


// CSS style table
?>
<!-- phpSiteCheck form begin -->
<STYLE type="text/css" media="screen">
.psc_table {
    font-family: Verdana, Arial, Arial Narrow ;
    font-size: 10pt;
    width: 98%;
}
.psc_formtable {
    font-size: 10pt;
}
.psc_header {
    font-family: Arial Narrow, Arial, Verdana;
    font-weight: bold;
    font-size: 11pt;
    color: #699F01;
    background-color:#EFEFEF;
}
.psc_input {
    font-family: Verdana, Arial, Arial Narrow ;
    font-size: 12px;
    width: 150px;
}
.psc_button {
    width: 100px;
}
.psc_resulttable {
    font-family: Verdana, Arial, Arial Narrow ;
    font-size: 10pt;
    width: 100%;
}
.psc_resultheader {
    font-family: Arial Narrow, Arial, Verdana;
    font-size: 11pt;
    padding: 3px 3px 3px 3px;
    font-weight: bold;
    color: #699F01;
    background-color:#EFEFEF;
}
.psc_resultcolumns {
    font-family: Verdana, Arial, Arial Narrow ;
    font-size: 10pt;
    padding: 3px 3px 3px 3px;
    font-weight: bold;
}
.psc_result {
    padding: 3px 3px 3px 3px;
    white-space: no-wrap;
}
.psc_result_delim {
    border-bottom: 1px dashed #699F01;
    height: 1px;
    padding: 0px 0px 0px 0px;
}
.psc_error {
    color: #C71028;
    font-weight: bold;
}
.psc_copyrights {
    color: #699F01;
    padding-top: 20px;
    font-size: xx-small;
}
</STYLE>
<?

// Caution!
// Do not edit anything bellow this line if not sure

set_time_limit(90);

global $_GET, $HTTP_GET_VARS;


$PSC_VARS = ((is_array($_GET)) ? $_GET : $HTTP_GET_VARS);
$sc=0
?>
<TABLE class="psc_table">
<TR>
    <TD>
        <TABLE class="psc_formtable">
        <FORM method="GET">
        <TR>
            <TD class="psc_header" colspan="2">
                Site check
            </TD>
        </TR>
        <TR>
            <TD>
                Host name:
            </TD>
            <TD noWrap>
                <INPUT type="text" name="psc_host" value="<?=htmlspecialchars($PSC_VARS['psc_host'])?>" class="psc_input">&nbsp;
                <INPUT type="submit" value="Check!" class="psc_button" onClick="this.value='Please wait...';">
            </TD>
        </TR>
        <TR>
            <TD>
                Services:
            </TD>
            <TD noWrap>
                <TABLE class="psc_formtable" cellpadding="0" cellspacing="0" border="0">
                <TR>
                    <TD><?php if($allowed_services[$sc]){$service = $allowed_services[$sc];?><INPUT type="checkbox" name="psc_<?=$service?>" id="psc_<?=$service?>" value="1" <?=((!$PSC_VARS['psc_host'] || $PSC_VARS['psc_'.$service]) ? 'checked' : '')?>><label for="psc_<?=$service?>"><?=strtoupper($service)?></label>&nbsp;<?php }else{?>&nbsp;<?}?></TD>
                    <TD><?php $sc++; if($allowed_services[$sc]){$service = $allowed_services[$sc];?><INPUT type="checkbox" name="psc_<?=$service?>" id="psc_<?=$service?>" value="1" <?=((!$PSC_VARS['psc_host'] || $PSC_VARS['psc_'.$service]) ? 'checked' : '')?>><label for="psc_<?=$service?>"><?=strtoupper($service)?></label>&nbsp;<?php }else{?>&nbsp;<?}?></TD>
                </TR> 
                <?php if(sizeof($allowed_services) > 2) {?> 
                <TR>
                    <TD><?php $sc++; if($allowed_services[$sc]){$service = $allowed_services[$sc];?><INPUT type="checkbox" name="psc_<?=$service?>" id="psc_<?=$service?>" value="1" <?=((!$PSC_VARS['psc_host'] || $PSC_VARS['psc_'.$service]) ? 'checked' : '')?>><label for="psc_<?=$service?>"><?=strtoupper($service)?></label>&nbsp;<?php }else{?>&nbsp;<?}?></TD>
                    <TD><?php $sc++; if($allowed_services[$sc]){$service = $allowed_services[$sc];?><INPUT type="checkbox" name="psc_<?=$service?>" id="psc_<?=$service?>" value="1" <?=((!$PSC_VARS['psc_host'] || $PSC_VARS['psc_'.$service]) ? 'checked' : '')?>><label for="psc_<?=$service?>"><?=strtoupper($service)?></label>&nbsp;<?php }else{?>&nbsp;<?}?></TD>
                </TR> 
                <?}?> 
                </TABLE>
            </TD>
        </TR>
        </FORM>
        </TABLE>
    </TD>
</TR>
<?php
if($PSC_VARS['psc_host']) {
?>
<TR>
    <TD>
<?php
    if(!pscValidateHost($PSC_VARS['psc_host'])) {
?>
        <DIV class="psc_error">Indicated host name is invalid. Please ender a valid hostname or IP address.</DIV>
<?php        
    }
    elseif(!$PSC_VARS['psc_http'] && !$PSC_VARS['psc_ftp'] && !$PSC_VARS['psc_pop3'] && !$PSC_VARS['psc_smtp']) {
?>
        <DIV class="psc_error">Please check at lest one service.</DIV>
<?php        
    }
    else {
        $psc_results = array();
        if($PSC_VARS['psc_http']) {
            $psc_results[] = pscDoCheck($PSC_VARS['psc_host'], 'http');
        }
        if($PSC_VARS['psc_http']) {
            $psc_results[] = pscDoCheck($PSC_VARS['psc_host'], 'http', 443);
        }
        if($PSC_VARS['psc_ftp']) {
            $psc_results[] = pscDoCheck($PSC_VARS['psc_host'], 'ftp');
        }
        if($PSC_VARS['psc_pop3']) {
            $psc_results[] = pscDoCheck($PSC_VARS['psc_host'], 'pop3');
        }
        if($PSC_VARS['psc_smtp']) {
            $psc_results[] = pscDoCheck($PSC_VARS['psc_host'], 'smtp');
        }

        if(sizeof($psc_results)) {
?>
        <BR><BR>
        <TABLE class="psc_resulttable" cellspacing="0">
        <TR>
            <TD class="psc_resultheader" colspan="5">
                Check result
            </TD>
        <TR>
        <TR>
            <TD class="psc_resultcolumns">
                Host
            </TD>
            <TD class="psc_resultcolumns">
                Service
            </TD>
            <TD class="psc_resultcolumns">
                Port
            </TD>
            <TD class="psc_resultcolumns">
                Time
            </TD>
            <TD class="psc_resultcolumns" align="center">
                Result
            </TD>
        </TR>
        <TR><TD colspan="5" class="psc_result_delim"><IMG src="/images/psc_1x1.gif" width="1" height="1" border="0"></TD></TR>
<?php
            foreach($psc_results as $psc_result) {
?>
        <TR>
            <TD class="psc_result"><?=$psc_result['host']?></TD>
            <TD class="psc_result"><?=$psc_result['service']?></TD>
            <TD class="psc_result"><?=$psc_result['port']?></TD>
            <TD><?=$psc_result['time']?>s</TD>
            <TD class="psc_result" align="center"><strong><?=$psc_result['error']?></strong></TD>
        </TR>
        <TR><TD colspan="5" class="psc_result_delim"><IMG src="/images/psc_1x1.gif" width="1" height="1" border="0"></TD></TR>
<?php
            }
?>
        </TABLE>
<?php
        }


    }
?>
    </TD>
</TR>
<?php
}
?>
<!-- DO NOT REMOVE NEXT LINE AS THIS PROHIBITED BY LISENCE AGREEMENT -->
<TR><TD class="psc_copyrights">Powered by <A href="http://www.serviceuptime.com/?psc" target="_blank">ServiceUptime.com</A>.</TD></TR>
</TABLE>

<?php
pscDatabaseCheck('mdb', 'lsjef!n9oWI', 'moodle-p.mis1.city.ac.uk:1521/moodlep');
?>

<!-- phpSiteCheck form end -->
