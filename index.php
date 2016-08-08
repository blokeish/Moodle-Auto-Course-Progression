<?php

require_once(dirname(__FILE__).'/../../config.php');

require_login();
$sitecontext = context_system::instance();
$strtitle = 'Didasko Auto Unit Progression';

$PAGE->set_context($sitecontext);
$PAGE->set_title($strtitle);
$PAGE->set_url($CFG->wwwroot . '/local/autounitprog/index.php');

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);



echo $OUTPUT->footer();
