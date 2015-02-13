<?php // $Id$
include_once('../../lib/formslib.php');

class assignment_verifyfile_form extends moodleform {

    function definition() {
        global $COURSE;
        $mform    =& $this->_form;
        $id = $this->_customdata['id'];
        
        $mform->addElement('text', 'receipt', get_string('receipt', 'assignment'), array('size' => 60, 'maxlength' => 47));
        $mform->addRule('receipt', null, 'required');
        $mform->addRule('receipt', null, 'minlength', 47, 'client');
        $mform->addRule('receipt', null, 'maxlength', 47, 'client');
        $mform->addRule('receipt', get_string('invalidreceipt', 'assignment'), 'regex', '/([a-zA-Z1-7]{4,4}[0-9]{1,1}-){7,7}[a-zA-Z1-7]{4,4}[0-9]{1,1}/');
        $mform->setHelpButton('receipt', array('receipt', get_string('receipt', 'receipt'), 'assignment'));
      
        $this->set_upload_manager(new upload_manager('assignment', true, false, $COURSE, false, 0, true, true, false));
        $mform->addElement('file', 'assignment', get_string('assignment', 'assignment'));
        $mform->addRule('assignment', null, 'required');

        $this->add_action_buttons(true, get_string('verifyfile', 'assignment'));

        $mform->addElement('hidden', 'id', $id);
    }

}

?>