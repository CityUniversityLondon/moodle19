<?PHP // $Id: assignment.php,v 1.14.2.12 2010/09/16 05:45:41 samhemelryk Exp $ 
      // assignment.php - created with Moodle 1.7 beta + (2006101003)


$string['allowdeleting'] = 'Allow deleting';
$string['allowmaxfiles'] = 'Maximum number of uploaded files';
$string['allownotes'] = 'Allow notes';
$string['allowresubmit'] = 'Allow resubmitting';
// CMDL-1629 Additional option to send email confirmation for late assignment submissions (REQ0033173)
$string['allsubmissions'] = 'All submissions';
// end CMDL-1629
$string['alreadygraded'] = 'Your assignment has already been graded and resubmission is not allowed.';
$string['assignment'] = 'Assignment';
$string['assignment:grade'] = 'Grade assignment';
$string['assignment:submit'] = 'Submit assignment';
$string['assignment:view'] = 'View assignment';
$string['assignmentdetails'] = 'Assignment details';
$string['assignmentmail'] = '$a->teacher has posted some feedback on your
assignment submission for \'$a->assignment\'

You can see it appended to your assignment submission:

    $a->url';
$string['assignmentmailhtml'] = '$a->teacher has posted some feedback on your
assignment submission for \'<i>$a->assignment</i>\'<br /><br />
You can see it appended to your <a href=\"$a->url\">assignment submission</a>.';
$string['assignmentname'] = 'Assignment name';
// CMDL-1108 add assignment receipt functionality
$string['assignmentsubmissionreceipt'] = 'Assignment Submission Confirmation';
// end CMDL-1108
// CMDL-1251 add deletion titles to receipt
$string['assignmentdeletionreceipt'] = 'Assignment Deletion Confirmation';
// end CMDL-1251
$string['assignmenttype'] = 'Assignment type';
$string['availabledate'] = 'Available from';
// CMDL-1175 add bulk upload of feedback
$string['bulkuploadfeedback'] = 'bulk upload of feedback';
// end CMDL-1175
$string['cannotdeletefiles'] = 'An error occurred and files could not be deleted';
$string['comment'] = 'Comment';
$string['commentinline'] = 'Comment inline';
// CMDL-1620 Change default setting in Moodle Assignments (REQ0031559)
$string['configallowmaxfiles'] = 'Sets the default number of files that can be submitted';
// end CMDL-1620
$string['configitemstocount'] = 'Nature of items to be counted for student submissions in online assignments.';
$string['configmaxbytes'] = 'Default maximum assignment size for all assignments on the site (subject to course limits and other local settings)';
$string['configshowrecentsubmissions'] = 'Everyone can see notifications of submissions in recent activity reports.';
// CMDL-1592 Enable send for marking default as no (REQ0026604)
$string['configtrackdrafts'] = 'Enables a two stage submission process - draft and send for marking';
// end CMDL-1592
$string['confirmdeletefile'] = 'Are you absolutely sure you want to delete this file?<br /><strong>$a</strong>';
// CMDL-1108 add assignment receipt functionality
$string['courseid'] = 'Course ID';
$string['datedeleted'] = 'Date deleted';
$string['datesubmitted'] = 'Date submitted';
// end CMDL-1108
$string['deleteallsubmissions'] = 'Delete all submissions';
$string['deletefilefailed'] = 'Deleting of file failed.';
$string['description'] = 'Description';
$string['draft'] = 'Draft';
$string['duedate'] = 'Due date';
$string['duedateno'] = 'No due date';
$string['early'] = '$a early';
$string['editmysubmission'] = 'Edit my submission';
$string['emailstudents'] = 'Email alerts to students';
$string['emailteachermail'] = '$a->username has updated their assignment submission
for \'$a->assignment\'

It is available here:

    $a->url';
$string['emailteachermailhtml'] = '$a->username has updated their assignment submission
for <i>\'$a->assignment\'</i><br /><br />
It is <a href=\"$a->url\">available on the web site</a>.';
$string['emailteachers'] = 'Email alerts to teachers';
$string['emptysubmission'] = 'You have not submitted anything yet';
$string['enableemailnotification'] = 'Send notification emails';
$string['existingfiledeleted'] = 'Existing file has been deleted: $a';
$string['failedupdatefeedback'] = 'Failed to update submission feedback for user $a';
$string['feedback'] = 'Feedback';
$string['feedbackfromteacher'] = 'Feedback from the $a';
$string['feedbackupdated'] = 'Submissions feedback updated for $a people';
$string['finalize'] = 'No more submissions';
$string['finalizeerror'] = 'An error occurred and that submission could not be finalised';
$string['graded'] = 'Graded';
$string['guestnosubmit'] = 'Sorry, guests are not allowed to submit an assignment. You have to log in/ register before you can submit your answer';
$string['guestnoupload'] = 'Sorry, guests are not allowed to upload';
$string['helpoffline'] = '<p>This is useful when the assignment is performed outside of Moodle.  It could be
   something elsewhere on the web or face-to-face.</p><p>Students can see a description of the assignment, 
   but can\'t upload files or anything.  Grading works normally, and students will get notifications of 
   their grades.</p>';
$string['helponline'] = '<p>This assignment type asks users to edit a text, using the normal
   editing tools.  Teachers can grade them online, and even add inline comments or changes.</p>
   <p>(If you are familiar with older versions of Moodle, this Assignment
   type does the same thing as the old Journal module used to do.)</p>';
$string['helpupload'] = '<p>This type of assignment allows each participant to upload one or more files in any format. 
   These might be a Word processor documents, images, a zipped web site, or anything you ask them to submit.</p>
   <p>This type also allows you to upload multiple response files. Response files can be also uploaded before submission which
   can be used to give each participant different file to work with.</p>
   <p>Participants may also enter notes describing the submitted files, progress status or any other text information.</p>
   <p>Submission of this type of assignment must be manually finalised by the participant. You can review the current status
   at any time, unfinished assignments are marked as Draft. You can revert any ungraded assignment back to draft status.</p>';
$string['helpuploadsingle'] = '<p>This type of assignment allows each participant to upload a 
   single file, of any type.</p> <p>This might be a Word processor document, an image, 
   a zipped web site, or anything you ask them to submit.</p>';
$string['hideintro'] = 'Hide description before available date';
$string['itemstocount'] = 'Count';
$string['late'] = '$a late';
// CMDL-1629 Additional option to send email confirmation for late assignment submissions (REQ0033173)
$string['latesubmissions'] = 'Late submissions';
// end CMDL-1629
$string['maximumgrade'] = 'Maximum grade';
$string['maximumsize'] = 'Maximum size';
$string['modulename'] = 'Assignment';
$string['modulenameplural'] = 'Assignments';
// CMDL-1108 add assignment receipt functionality
$string['name'] = 'Name';
// end CMDl-1108
$string['newsubmissions'] = 'Assignments submitted';
$string['noassignments'] = 'There are no assignments yet';
$string['noattempts'] = 'No attempts have been made on this assignment';
$string['nofiles'] = 'No files were submitted';
$string['nofilesyet'] = 'No files submitted yet';
$string['nomoresubmissions'] = 'No further submissions are allowed.';
// CMDL-1247 change submissions message
$string['nosubmissionsyet'] = 'Sorry, this assignment is not yet available for submissions';
// end CMDL-1247
$string['notavailableyet'] = 'Sorry, this assignment is not yet available.<br />Assignment instructions will be displayed here on the date given below.';
$string['notes'] = 'Notes';
$string['notesempty'] = 'No entry';
$string['notesupdateerror'] = 'Error when updating notes';
$string['notgradedyet'] = 'Not graded yet';
$string['notsubmittedyet'] = 'Not submitted yet';
$string['nosubmitusers'] = 'No users were found with permissions to submit this assignment';
$string['onceassignmentsent'] = 'Once the assignment is sent for marking, you will no longer be able to delete or attach file(s). Do you want to continue?';
$string['overwritewarning'] = 'Warning: uploading again will REPLACE your current submission';
$string['pagesize'] = 'Submissions shown per page';
$string['preventlate'] = 'Prevent late submissions';
$string['quickgrade'] = 'Allow quick grading';
// CMDL-1175 add bulk upload of feedback
$string['responsefiles'] = 'Response files';
// end CMDL-1175
$string['responsesfiles'] = 'Bulk upload feedback';
$string['reviewed'] = 'Reviewed';
// CMDL-1108 add assignment receipt functionality
$string['receiptsubject'] = 'Assignment Submission Confirmation';
// end CMDL-1108
// CMDL-1251 add deletion titles to receipt
$string['receiptsubjectdel'] = 'Assignment Deletion Confirmation';
// end CMDL-1251
// CMDL-1108 add assignment receipt functionality
$string['receipt'] = 'Receipt';
// end CMDL-1108
// CMDL-1175 add bulk upload of feedback
$string['responsecheckfilemissing'] = 'Check failed. do_not_edit.txt is missing';
$string['responsecheckfilemismatch'] = 'Check failed. Zip file does not match this assignment';
$string['responsedirerror'] = 'Check failed. The assignment directory has changed or the do_not_edit.txt file has been moved. Check that the do_not_edit.txt file is in the assignment directory and try again.';
// end CMDL-1175
$string['saveallfeedback'] = 'Save all my feedback';
$string['sendformarking'] = 'Send for marking';
$string['showrecentsubmissions'] = 'Show recent submissions';
// CMDL-1108 add assignment receipt functionality
$string['studentid'] = 'Student ID';
// end CMDL-1108
$string['submission'] = 'Submission';
$string['submissiondraft'] = 'Submission draft';
$string['submissionfeedback'] = 'Submission feedback';
$string['submissions'] = 'Submissions';
$string['submissionsaved'] = 'Your changes have been saved';
$string['submissionsnotgraded'] = '$a submissions not graded';
$string['submitassignment'] = 'Submit your assignment using this form';
$string['submitedformarking'] = 'Assignment was already submitted for marking and can not be updated';
$string['submitformarking'] = 'Final submission for assignment marking';
$string['submitted'] = 'Submitted';
$string['submittedfiles'] = 'Submitted files';
// CMDL-1108 add assignment receipt functionality
$string['teachers'] = 'Teachers';
// end CMDL-1108
$string['trackdrafts'] = 'Enable Send for marking';
$string['typeoffline'] = 'Offline activity';
$string['typeonline'] = 'Online text';
$string['typeupload'] = 'Advanced uploading of files';
$string['typeuploadsingle'] = 'Upload a single file';
$string['unfinalize'] = 'Revert to draft';
$string['unfinalizeerror'] = 'An error occurred and that submission could not be reverted to draft';
$string['uploadbadname'] = 'This filename contained strange characters and couldn\'t be uploaded';
$string['uploadedfiles'] = 'uploaded files';
$string['uploaderror'] = 'An error happened while saving the file on the server';
$string['uploadfailnoupdate'] = 'File was uploaded OK but could not update your submission!';
$string['uploadfiletoobig'] = 'Sorry, but that file is too big (limit is $a bytes)';
$string['uploadnofilefound'] = 'No file was found - are you sure you selected one to upload?';
$string['uploadnotregistered'] = '\'$a\' was uploaded OK but submission did not register!';
$string['uploadsuccess'] = 'Uploaded \'$a\' successfully';
$string['usernosubmit'] = 'Sorry, you are not allowed to submit an assignment.';
// CMDL-1108 add assignment receipt functionality
$string['username'] = 'Username';
$string['validationfailed'] = 'Validation failed, validate another file?';
$string['validationsuccess'] = 'Validation success, validate another file?';
$string['verifyfile'] = 'Verify File';
// end CMDL-1108
$string['viewfeedback'] = 'View assignment grades and feedback';
$string['viewsubmissions'] = 'View $a submitted assignments';
$string['yoursubmission'] = 'Your submission';
// CMDL-1175 add bulk upload of feedback
$string['zipdownloadallsubmissions'] = 'Bulk download all submissions';
// end CMDl-1175

?>
