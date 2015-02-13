<?php  //$Id: settings.php,v 1.0 2008-05-13 17:38:47 sbourget Exp $

if (empty($CFG->enablerssfeeds)) {
    $options = array(0 => get_string('rssglobaldisabled', 'admin'));
    $str = get_string('configenablerssfeeds', 'ipodcast').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');

} else {
    $options = array(0=>get_string('no'), 1=>get_string('yes'));
    $str = get_string('configenablerssfeeds', 'ipodcast');
}
$settings->add(new admin_setting_configselect('ipodcast_enablerssfeeds', get_string('configenablerssfeeds2', 'ipodcast'),
                   $str, 0, $options));

unset($options);
$options = array(0=>get_string('no'), 1=>get_string('yes'));
$settings->add(new admin_setting_configselect('ipodcast_enablerssitunes', get_string('configenablerssitunes2', 'ipodcast'),
                   get_string('configenablerssitunes', 'ipodcast'), 0, $options));

unset($options);
$options = array(0=>get_string('no'), 1=>get_string('yes'));
$settings->add(new admin_setting_configselect('ipodcast_usemediafilter', get_string('configusemediafilter2', 'ipodcast'),
                   get_string('configusemediafilter', 'ipodcast'), 0, $options));

$settings->add(new admin_setting_configtext('ipodcast_darwinurl', get_string('configdarwinurl2', 'ipodcast'),
                   get_string('configdarwinurl', 'ipodcast'),'', PARAM_URL));

$settings->add(new admin_setting_configexecutable('ipodcast_mp4creatorpath', get_string('configmp4creatorpath2', 'ipodcast'),
                   get_string('configmp4creatorpath', 'ipodcast'),'', PARAM_URL));

$settings->add(new admin_setting_configexecutable('ipodcast_mp4infopath', get_string('configmp4infopath2', 'ipodcast'),
                   get_string('configmp4infopath', 'ipodcast'),'', PARAM_URL));
?>
