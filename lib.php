<?php
require_once($CFG->libdir .'/enrollib.php');
require_once($CFG->libdir .'/moodlelib.php');
require_once($CFG->libdir .'/classes/user.php');
require_once($CFG->libdir .'/moodlelib.php');
require_once($CFG->dirroot.'/enrol/manual/locallib.php');

defined('MOODLE_INTERNAL') || die();

/*
function local_splash_extends_navigation(global_navigation $navigation){
	global $PAGE, $USER;

	if(!$USER->id){
		return;
	}


	$homenode=$navigation->find('home', navigation_node::TYPE_SETTING);
	if($homenode){
		$homenode->remove();
		$navigation=$homenode->parent;
	}

	$sitehomenode=$navigation->find('myhome', navigation_node::TYPE_SETTING);
	if($sitehomenode){
		$sitehomenode->remove();
		$navigation=$sitehomenode->parent;
	}
}*/

/**
 * SplashCourse defines course properties that can be found in Splash settig page(Site administration->Plugins->Local plugins->Splash->Splash settings).
 * Before adding new Splash Course, make sure settings are available in settings page. To create new settings go to settings.php and create 3 settings (_enabled, _description, _courses)
 * Add new course in SplashCourseList courses array

 */

class autounitprog{
private $config;
private $unitlist;
	public function __construct(){
		//$this->splashCourseName = $splashCourseName;
		$this->config = get_config('local_autounitprog');
		$this->courseLists();
		//print_object($this->unitlist);
	}

	public function assignnextunit(){

	}

	public function courseLists(){
		$allList = explode(PHP_EOL,$this->config->unit_progression_list);
		foreach($allList as $eachlist){
			$tmp = explode(',',$eachlist);
			$this->unitlist[array_shift($tmp)] = $tmp;
		}
	}


	function getUnitActivities($unit,$act=''){
	    global $DB;
	    // /lib/datalib.php
	    $mSeq = $DB->get_record_sql("SELECT GROUP_CONCAT(X.sequence) mods FROM (SELECT * FROM mdl_course_sections WHERE course=$unit ORDER BY section) X WHERE 1 GROUP BY course",array());
	    $allMods = explode(',',$mSeq->mods);
	    $selModsSeq = $allMods;

	    /*if($act != '') // if looking for a specific module type
	        $module = $DB->get_record('modules',array('name' =>$act));*/
	    //$resAsgMods = "SELECT id FROM mdl_course_modules WHERE module={$module->id} AND course=8 AND visible=1";
	    if($act != ''){
	        $selMods = array();
	        foreach(explode(',',$act) as $eact){
	            $module = $DB->get_record('modules',array('name' =>$eact));
	            $resMods = $DB->get_records_sql("SELECT id FROM mdl_course_modules WHERE module=? AND course=? AND visible=1",array($module->id,$unit));
	            foreach($resMods as $mods){
	                $selMods[]=$mods->id;
	            }
	        }
	        //print_object($allMods);
	        //print_object($selMods);
	        $selModsSeq = array_intersect($allMods,$selMods); // selected modules only, in the order they appear in the unit
	    }
	    return $selModsSeq;
	}


	function getUnitLastActivity($unit,$act){
	    $actvts = $this->getUnitActivities($unit,$act);
	    return end($actvts);
	}


	function getUserNextUnit($user,$cur_unit){
	    global $DB;
	    if(!is_object($user))
	        $user = core_user::get_user($user);
	    if(!is_object($cur_unit))
	        $cur_unit = get_course($cur_unit);

	    //print_object($user);
	    //$Ret = ''; //array('unit'=>false,'cluster'=>false);
	    $nxtUnit = '';
	    $QualUnit = $this->GetStudQualification($user->id);
	    //print_object($QualUnit);
	    $period = -1;
	    $CurUntIdx = false;
	    $NxtUntIdx = 0;
			if($QualUnit->shortname==$cur_unit->shortname){
				$nxtUnit = $this->unitlist[$QualUnit->shortname][0];
			}
			else {
			$CurUntIdx = array_search($cur_unit->shortname,$this->unitlist[$QualUnit->shortname]);
			//echo $CurUntIdx;
			if($CurUntIdx!==FALSE){
				if(($CurUntIdx+1) < count($this->unitlist[$QualUnit->shortname]))
					$nxtUnit = $this->unitlist[$QualUnit->shortname][$CurUntIdx+1];
				}
			}
			//echo $nxtUnit;
			if(!empty($nxtUnit))
				$nxtUnit = $DB->get_record('course', array('shortname'=>$nxtUnit));
	    /*do{
	        $period++;
	        $CurUntIdx = array_search($cur_unit->shortname,$CFG->QualUnits[$QualUnit->shortname][$period]);
	    }while($CurUntIdx===false && $period<COUNT($CFG->QualUnits[$QualUnit->shortname]));

	    //echo '>'.$CurUntIdx.'-'.$period;
	    if($CurUntIdx!==FALSE){
	        if(($CurUntIdx+1) < count($CFG->QualUnits[$QualUnit->shortname][$period]))
	            $nxtUnit = $CFG->QualUnits[$QualUnit->shortname][$period][$CurUntIdx+1];             //$NxtUntIdx = $CurUntIdx+1;
	        else{
	            //$NxtUntIdx = 0;
	            if(!$sameCluster){ // look into next cluster to find the next unit
	                $period++; // check if period ++ exist
	                if($period<count($CFG->QualUnits[$QualUnit->shortname]))
	                    $nxtUnit = $CFG->QualUnits[$QualUnit->shortname][$period][0]; //array('unit'=>$CFG->QualUnits[$QualUnit->shortname][$period][$NxtUntIdx],'cluster'=>$period);
	            }
	        }

	    }*/

	        return $nxtUnit==''?false:$nxtUnit;
	}


	function GetStudQualification($userid){

	        // get the intro unit that they are doing, it by default is the first unit assigned to them unless theu change their qualifiaction
	    $enrolledUnits = enrol_get_all_users_courses($userid,true); // only get active units. Student could be suspended from the qual intro unit
	    $initalEnrol; //= new stdClass();
	    foreach($enrolledUnits as $unit){
	        if(array_key_exists($unit->shortname, $this->unitlist)){
	            $initalEnrol = $unit;
	            break;
	        }
	    }
	    return $initalEnrol; // is_object($initalEnrol)?false:
	}


	function enroltoUnit($unit,$student){
		global $DB;
		if(!is_object($unit))
				$unit = get_course($unit);

		if(!is_object($student))
				$student = core_user::get_user($student);

		$manplugin = enrol_get_plugin('manual');
		$maninstance1 = $DB->get_record('enrol', array('courseid'=>$unit->id, 'enrol'=>'manual'), '*', MUST_EXIST);
		$studentrole = $DB->get_record('role', array('shortname'=>'student'));
		$manplugin->enrol_user($maninstance1, $student->id, $studentrole->id);
	}

}


class eventhndlr_autoprogress{
	public function onEventTrigger(){

	}
}

function onAssignmentSubmission(\mod_assign\event\assessable_submitted $event){
	//print_object($event);
	//echo $event->contextid;
	$unitprog = new autounitprog();
	//print_object($unitprog->getUserNextUnit($event->userid,$event->courseid));
	if($unitprog->getUnitLastActivity($event->courseid,'assign') == $event->contextinstanceid){
		$nxtUnit =$unitprog->getUserNextUnit($event->userid,$event->courseid);
		if(!empty($nxtUnit))
			$unitprog->enroltoUnit($nxtUnit,$event->userid);
	}

	//die();
}

/*
	function local_autounitprog_cron(){
		global $DB, $OUTPUT;




		try {

		} catch(Exception $e) {
			echo $OUTPUT->error_text($e);
			return false;
		}

		return true;
	}


}
*/
