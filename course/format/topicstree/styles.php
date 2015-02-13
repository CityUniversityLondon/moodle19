<?php // $Id: styles.php,v 1.2 2008/09/26 23:02:03 stronk7 Exp $

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com  //
//           (C) 2001-3001 Eloy Lafuente (stronk7) http://contiento.com  //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

    $bg = '#FAFAFA'; /// Change this to match your theme background color (defaults to standard ones)
?>

body ul.section .activity {
    margin: 0 !important;
    padding: 0 !important;
}

body ul.treesection, ul.treesection ul {
    list-style-type: none;
    background: <?php echo $bg ?> url(<?php echo "{$CFG->wwwroot}/course/format/topicstree/vline.png" ?>) repeat-y;
    margin: 0;
    padding: 0;
}

body ul.treesection ul {
    margin-left: 2.2em;
}
body ul.treesection li.treeactivity {
    line-height: 1.5em;
    background: url(<?php echo "{$CFG->wwwroot}/course/format/topicstree/node.png" ?>) no-repeat;
}
body ul.treesection li.treeactivity.last {
    background: <?php echo $bg ?> url(<?php echo "{$CFG->wwwroot}/course/format/topicstree/lastnode.png" ?>) no-repeat;
}
