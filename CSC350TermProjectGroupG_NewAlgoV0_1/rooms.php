<?php
class Rooms{
    private $roomsList = array();
    function __construct($roomsList)
    {
        sort($roomsList);
        foreach($roomsList as $room)
        {
            array_push($this->roomsList,new Room($room));
        }
    }
    function getCount()
    {
        return count($this->roomsList);
    }
    function getList()
    {
        return $this->roomsList;
    }
    function deAllocateSection($section)
    {
        foreach($section->getMeetingTimesList() as $time)
        {
            foreach($this->roomsList as $room)
            {
                $room->deAllocateTime($time);
            }
        }
    }
    //Does Monday have the time if not then false
    //Has another meetingTime from this section been allocated if yes then false

    function doesMondayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('mon') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesTuesdayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('tue') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesWednesdayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('wed') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesThursdayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('thu') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesFridayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('fri') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesSaturdayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('sat') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
    function doesSundayHaveSpace($meetingTime)
    {
        $roomToAllocate = false;
        foreach($this->roomsList as $room)
        {
            if($room->getDaySpace('sun') >= $meetingTime->getHours())
            {
                $roomToAllocate = $room;
            }
        }
        return $roomToAllocate;
    }
}
class meetingTimeStorage extends SplObjectStorage {
    public function getHash($o) {
        return $o->getId();
    }
}
class Room{
    private $roomNo;

    private static $monLookup;
    private static $tueLookup;
    private static $wedLookup;
    private static $thuLookup;
    private static $friLookup;
    private static $satLookup;
    private static $sunLookup;
    private static $allDayLookup;
    private $meetingTimeLookup;
    private const timeInDay = 14;
    private $remainingTimeMon;
    private $remainingTimeTue;
    private $remainingTimeWed;
    private $remainingTimeThu;
    private $remainingTimeFri;
    private $remainingTimeSat;
    private $remainingTimeSun;
    private $mon = array(),
    $tue = array(),
    $wed = array(),
    $thu = array(),
    $fri = array(),
    $sat = array(),
    $sun = array();
    function __construct($roomNo)
    {
        Room::$monLookup = new SPLObjectStorage();
        Room::$tueLookup = new SPLObjectStorage();
        Room::$wedLookup = new SPLObjectStorage();
        Room::$thuLookup = new SPLObjectStorage();
        Room::$friLookup = new SPLObjectStorage();
        Room::$satLookup = new SPLObjectStorage();
        Room::$sunLookup = new SPLObjectStorage();
        Room::$allDayLookup = new SPLObjectStorage();
        $this->meetingTimeLookup = new SPLObjectStorage();
        $this->roomNo = $roomNo;
        $this->remainingTimeMon = Room::timeInDay;
        $this->remainingTimeTue = Room::timeInDay;
        $this->remainingTimeWed = Room::timeInDay;
        $this->remainingTimeThu = Room::timeInDay;
        $this->remainingTimeFri = Room::timeInDay;
        $this->remainingTimeSat = Room::timeInDay;
        $this->remainingTimeSun = Room::timeInDay;
    }
    function getRoomNo()
    {
        return $this->roomNo;
    }

}

?>