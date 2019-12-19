<?php
include 'classesClasses.php';


function cmpMeetingTimeDesc($a, $b) 
{
    return $b->getPriority() - $a->getPriority();
}
function cmpMeetingTimeAsc($a, $b) 
{
    return $a->getPriority() - $b->getPriority();
}
function runQuery($conn,$sql)
{
    $result = mysqli_query($conn, $sql);						
    if(!$result)												
        { echo "error running: ".$sql; }
}
class record{
    public $credits;
    public $classCode;
    public $timesMeetingPerWeek;
    function __construct($credits,$classCode,$timesMeetingPerWeek)
    {
        $this->credits = $credits;
        $this->classCode = $classCode;
        $this->timesMeetingPerWeek = $timesMeetingPerWeek;
    }

}
function SchedulingAlgorithm(&$meetingTimeList,&$rooms,$mon,$tue,$wed,$thu,$fri,$sat,$sun)
{

$days = array(
    array(),
    array(),
    array(),
    array(),
    array(),
    array(),
    array()
);
$dayCounter = 0;
//Loop to allocate Times
foreach($meetingTimeList as $time)
{
    //Monday's Schedule
    if($dayCounter == 0 && $mon >= $time->getHours())
    {
        $mon -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 1)
        {
            $time->getSection()->setDayOfWeekCode(1);
        }
    }
    //Tuesday's Schedule
    else if($dayCounter == 1 && $tue >= $time->getHours())
    {
        $tue -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 2)
        {
            $time->getSection()->setDayOfWeekCode(2);
        }
    }
    //Wednesday's Schedule
    else if($dayCounter == 2 && $wed >= $time->getHours())
    {
        $wed -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 3)
        {
            $time->getSection()->setDayOfWeekCode(3);
        }
    }
    //Thursday's Schedule
    else if($dayCounter == 3 && $thu >= $time->getHours())
    {
        $thu -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 4)
        {
            $time->getSection()->setDayOfWeekCode(4);
        }
    }
    //Friday's Schedule
    else if($dayCounter == 4 && $fri >= $time->getHours())
    {
        $fri -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 5)
        {
            $time->getSection()->setDayOfWeekCode(5);
        }
    }
    //Saturday's Schedule
    else if($dayCounter == 5 && $sat >= $time->getHours())
    {
        $sat -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 6)
        {
            $time->getSection()->setDayOfWeekCode(6);
        }
    }
    //Sunday's Schedule
    else if($dayCounter == 6 && $sun >= $time->getHours())
    {
        $sun -= $time->getHours();
        array_push($days[$dayCounter],$time);
        if($time->getSection()->getDayOfWeekCode() == 0 || 
        $time->getSection()->getDayOfWeekCode() > 7)
        {
            $time->getSection()->setDayOfWeekCode(7);
        }
    }
    if($dayCounter != 0)
        $dayCounter = ($dayCounter+1) % 7;
    else
        $dayCounter++;
}
$rooms;
$dayCounter = 1;
$startingTime = 8;
$allocatedTimes = 0;
//Go through generated dataset to calculate the meaning of the allocations
//Assigning things like start hour, day of week, and all relevant info for section number
foreach($days as $day)
{
    $timeInDay = 0;

    foreach($day as $time)
    {
        $time->setStartHour($timeInDay%13);
        $time->setRoomNo($rooms[floor($timeInDay/13)]);
        $time->setDayOfWeek($dayCounter);
        $timeInDay += $time->getHours();
        $time->getSection()->calcHourCode();
        //if($time->getSection()->getHourCode() <1)
        {
            $time->print();
            echo '<br>';
        }
    }
    $dayCounter++;
    //For Checking Allocated Count
    $allocatedTimes += count($day);
}
}
function findPartialAllocations(&$sectionList)
{
    $partials = array();
    foreach($sectionList as $section)
    {
        if($section->isPartiallyAllocated())
        {
            array_push($partials,$section);
        }
    }
    return $partials;
}
function buildSchedule($listOfRooms,$sectionRecords)
{
//////////////////////////////////////////////////////////////////////
//Start of code to create schedule in DB
//////////////////////////////////////////////////////////////////////
//
$schedule = generateSchedule($sectionRecords,$listOfRooms);
$hoursScheduled = 0;
//server name
$sn = 'localhost';
//user name
$un = 'root';
//password
$pw = '';
//DB name
$db = 'CSC350GroupG';
$conn = mysqli_connect($sn, $un, $pw, $db );
//Remove stale data from old schedule
$sql = "truncate table Schedule";
runQuery($conn,$sql);
//Generate SQL To create schedule records
$sql = "insert into Schedule values";
foreach($schedule as $time)
{
    $meetingTimeId = $time->getMeetingTimeId();
    $sectionNo = $time->getSection()->getSectionCode();
    $roomNo = $time->getRoomNo();
    $startTime = $time->getStartHour();
    $endTime = $time->getEndHour();
    $DayOfWeek = $time->getDayOfWeek();
    $classCode = $time->getSection()->getClassCode();

    $sql .= "($meetingTimeId,'$sectionNo','$roomNo',$startTime,$endTime,'$DayOfWeek','$classCode'),";
    
}
//final prep of SQL insert statment
$sql = substr($sql,0,strlen($sql)-1);
$sql = $sql.';';
runQuery($conn,$sql);
//////////////////////////////////////////////////////////////////////
//End of code to create schedule in DB
//////////////////////////////////////////////////////////////////////

}
function generateSchedule($recordList,$rooms)
{
$sectionList = array();
foreach($recordList as $x)
{
    array_push($sectionList,
        new Section($x->credits,$x->classCode,$x->timesMeetingPerWeek));
}
$cred4 = array();
$cred3 = array();
$cred2 = array();
//Separated out to separate lists for Sorting
foreach($sectionList as $sec)
{
    if($sec->getCredits() == 3)
    {
        foreach($sec->getMeetingTimesList() as $meetingTime)
            array_push($cred3,$meetingTime);
    }
    else if($sec->getCredits() == 4)
    {
        foreach($sec->getMeetingTimesList() as $meetingTime)
            array_push($cred4,$meetingTime);
    }
    else if($sec->getCredits() == 2)
    {
        foreach($sec->getMeetingTimesList() as $meetingTime)
            array_push($cred2,$meetingTime);
    }
}
//sorting lists according to credits, then hours in meeting
usort($cred4, 'cmpMeetingTimeAsc');
usort($cred3, 'cmpMeetingTimeAsc');
usort($cred2, 'cmpMeetingTimeAsc');
    $totalHoursPerDay = 13*count($rooms);
$mon = $totalHoursPerDay;
$tue = $totalHoursPerDay;
$wed = $totalHoursPerDay;
$thu = $totalHoursPerDay;
$fri = $totalHoursPerDay;
$sat = $totalHoursPerDay;
$sun = $totalHoursPerDay;
$data = array();
//Join Sorted arrays into 
foreach($cred2 as $x)
    array_push($data,$x);
foreach($cred3 as $x)
    array_push($data,$x);
foreach($cred4 as $x)
    array_push($data,$x);
//Algorithm to schedule list of meeting times
SchedulingAlgorithm($data,$rooms,$mon,$tue,$wed,$thu,$fri,$sat,$sun);

$toBeDeAllocated = findPartialAllocations($sectionList);

foreach($toBeDeAllocated as $sectionToDeAllocate)
{
    $rooms->deAllocateSection($sectionToDeAllocate);
}
//End Final deallocation
$finalSchedule = array();
foreach($sectionList as $section)
{
    foreach($section->getMeetingTimesList() as $time)
    {
        if($time->getStartHour() != -1)//-1 start hour means time is unallocated
        {
            array_push($finalSchedule,$time);
        }
    }
}
return $finalSchedule;
}
?>