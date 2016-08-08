<?php
defined('MOODLE_INTERNAL') || die;
if ($hassiteconfig) {

	$ADMIN->add('localplugins', new admin_category('autounitprog', get_string('autounitprog','local_autounitprog')));

 	$didasko_settings = new admin_settingpage('local_autounitprog', get_string('autounitprogsettings','local_autounitprog'));
 	$ADMIN->add('autounitprog', $didasko_settings);
 	$didasko_settings->add(new admin_setting_configtextarea('local_autounitprog/unit_progression_list',get_string('progressionList','local_autounitprog'), get_string('progressionListDescription','local_autounitprog'), ''));
	//$didasko_settings->add(new admin_setting_configtext('local_autounitprog/marketing_emial_subject',get_string('recepientEmailsSubject','local_autounitprog'), get_string('recepientEmailSubjectDescription','local_autounitprog'), get_string('recepientEmailSubjectDescriptionDefault','local_autounitprog')));
}
