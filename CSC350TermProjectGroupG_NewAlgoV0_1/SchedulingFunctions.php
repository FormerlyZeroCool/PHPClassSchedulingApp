<?php
include 'rooms.php';
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
foreach($meetingTimeList as $time)
{
    //echo "Day Code: $dayCounter Hours in Monday: $mon, Tue: $tue, Wed: $wed, Thu: $thu, Fri: $fri, Sat: $sat, Sun: $sun";
    //echo "<br>".$time->getHours()."<br>";
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
        $dayCounter = ($dayCounter+1) %7;
    else
        $dayCounter++;
}
$listOfRooms = $rooms->getList();
$dayCounter = 1;
$startingTime = 8;
$allocatedTimes = 0;
foreach($days as $day)
{
    $timeInDay = 0;
    foreach($day as $time)
    {
        $time->setStartHour($timeInDay%13);
        $time->setRoomNo($listOfRooms[floor($timeInDay/13)]->getRoomNo());
        $time->setDayOfWeek($dayCounter);
        $timeInDay += $time->getHours();
        $time->getSection()->calcHourCode();
        //$time->print();
        //echo '<br>';
    }
    $dayCounter++;
    $allocatedTimes += count($day);
}
echo count($meetingTimeList)." ".$allocatedTimes;
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
$rooms = new Rooms($listOfRooms);
$schedule = generateSchedule($sectionRecords,$rooms);
$hoursScheduled = 0;
$sn = 'localhost';
$un = 'root';
$pw = '';
$db = 'CSC350GroupG';
$conn = mysqli_connect($sn, $un, $pw, $db );
$sql = "truncate table Schedule";
runQuery($conn,$sql);

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
$sql = substr($sql,0,strlen($sql)-1);
$sql = $sql.';';
runQuery($conn,$sql);
//////////////////////////////////////////////////////////////////////
//End of code to create schedule in DB
//////////////////////////////////////////////////////////////////////

}
function allocate(&$meetingTimeList,&$rooms)
{

    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesMondayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushMon($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesTuesdayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushTue($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesWednesdayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushWed($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesThursdayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushThu($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesFridayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushFri($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesSaturdayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushSat($meetingTime);
        }
    }
    foreach($meetingTimeList as $meetingTime)
    {
        $room = $rooms->doesSundayHaveSpace($meetingTime);
        if($room != false && $meetingTime->getStartHour() < 0)
        {
            $room->pushSun($meetingTime);
        }
    }
    
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
foreach($sectionList as $sec)
{
    /*
    $sec->print();
    echo '<br>';
    */
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
   /*else
    {
        //Only for debugging to show when a class is 
        //considered invalid by Algorithm
        echo 'invalid class no class with '.$sec->getCredits();
        echo ' credits exists ';
        echo 'class '.$sec->getClassCode();
        echo ' will not be processed<br>';
       
    }*/
}
usort($cred4, 'cmpMeetingTimeAsc');
usort($cred3, 'cmpMeetingTimeAsc');
usort($cred2, 'cmpMeetingTimeAsc');
    $totalHoursPerDay = 13*$rooms->getCount();
$mon = $totalHoursPerDay;
$tue = $totalHoursPerDay;
$wed = $totalHoursPerDay;
$thu = $totalHoursPerDay;
$fri = $totalHoursPerDay;
$sat = $totalHoursPerDay;
$sun = $totalHoursPerDay;
$data = array();
foreach($cred2 as $x)
    array_push($data,$x);
foreach($cred3 as $x)
    array_push($data,$x);
foreach($cred4 as $x)
    array_push($data,$x);
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