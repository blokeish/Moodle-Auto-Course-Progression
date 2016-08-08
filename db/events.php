<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
		array(
				'eventname'   => '\mod_assign\event\assessable_submitted',
				'includefile' => '/local/autounitprog/lib.php',
				'callback'    => 'onAssignmentSubmission',
				'schedule'         => 'instant',
				'internal'         => false,
				'priority'    => 9999,
		)
);
