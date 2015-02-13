<?php


        // print button offering zip file function to teacher
        $options["id"] = "$cm->id";
        $options["download"] = "zip";

        echo '<br/><div align="center">';
        print_single_button("submissions.php",$options, get_string('zipdownloadallsubmissions', 'assignment'));
        echo '</div>';


        // print button offering upload function to teacher
        $options["id"] = "$cm->id";
        $options["upload"] = "zip";

        
        echo '<br/><div align="center">';
        //print_single_button("submissions.php",$options, get_string('zipuploadallresponses', 'assignment'));

        $customresponses = $this->custom_responsesform(true);
        if (!empty($customresponses)) {
            echo $customresponses;
        }
        echo '</div>';
       
?>
