
<!DOCTYPE html>
<html>

<body>

<?php
include 'SchedulingFunctions.php';

$path = $_POST['rooms'];
if (!file_exists($path)) {
    print 'File not found';
  }
  else{
    $roomFile = fopen($path, "r") or die("Unable to open file!");
    $arrayofRooms = array();
    while(!feof($roomFile)) {
        $tempString = fgets($roomFile);
        if ($tempString == "") {

        }
         else 
           $arrayofRooms[] = $tempString;
      
    }
      fclose($roomFile);
}


      $path = $_POST['schedule'];
      if (!file_exists($path)) {
          print 'File not found';
        }
        else{
            $scheduleFile = fopen($path, "r") or die("Unable to open file!");
            $arrayOfCourseNo = array();
            $arrayOfCourseMeetingTimes = array();

            while(!feof($scheduleFile)) {
                 $splitCourseNoFromMeet = fgets($scheduleFile);
                 $tempArray = explode(",",$splitCourseNoFromMeet);
                 if (!isset($tempArray[1]) || !isset($tempArray[0])) {

                 }
                 else {
                  if (count(explode(' ', $tempArray[0])) > 1) {  // check for white spaces. If file has CSC 101 instead of CSC101. 
                    $courseNoWithoutSpace = array();
                    $courseNoWithoutSpace = explode(' ', $tempArray[0]);
                    $course = $courseNoWithoutSpace[0];
                    $course .= $courseNoWithoutSpace[1];

                    $arrayOfCourseNo[] = $course;
                    $arrayOfCourseMeetingTimes[] = $tempArray[1];  
                  }
                  else {

                   $arrayOfCourseMeetingTimes[] = $tempArray[1];  
                   $arrayOfCourseNo[] = $tempArray[0];
                  }
                 }
  
            }
            fclose($scheduleFile);


            $sn = 'localhost';
            $un = 'root';
            $pw = '';
            $db = 'CSC350GroupG';
            $conn = mysqli_connect($sn, $un, $pw, $db );
            
            if (mysqli_connect_error()) {
                die("Database connection failed: " . mysqli_connect_error());
            }
            else{ 
              //Only get the credits for the coursesNo that were given in the CSV schedule file. 
              $creditForCourseNo = array();
              $courses = array();
              $courseCredit = array();
            $sql=" SELECT * FROM AvailableClasses";
            $resultTwo = $conn->query($sql);
           
            while($row = $resultTwo->fetch_assoc()){
              $courses[$row["CourseNo"]] = $row["HoursMeeting"];
            }
            
            if(count($courses) < 1 )
            {
                echo "ERROR: No course credits in array.";
            }
            else
            {
              $data =  array();
              $arraySize = count($arrayOfCourseNo);

              for($i = 0; $i < $arraySize; $i++){
                array_push($data,new record((int)$courses[$arrayOfCourseNo[$i]],$arrayOfCourseNo[$i],(int)$arrayOfCourseMeetingTimes[$i]));
                }
                
              
              buildSchedule($arrayofRooms,$data);

            }
            
          }
           
    }

?>






<h2>Choose an Option From Below</h2>

<p>Click On The Button To Display Grid For Full Schedule</p>
<a href="350.php"><button>View Full Schedule</button></a>
<p>Pick A Room To View The Room's Schedule For The Week.</p>

<?php
$sn = 'localhost';
$un = 'root';
$pw = '';
$db = 'CSC350GroupG';
$conn = mysqli_connect($sn, $un, $pw, $db );

if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}
else{  // CHANGE ACTION TO BEN'S FILE - TO DISPLAY ROOM SCHDULE. get['roomchoice'] TO GET THE ROOM CHOSEN
   echo "<form action=\"table.php\" method=\"get\">
    <select id=\"roomchoice\" name=\"roomchoice\">";
    $sql="SELECT DISTINCT Room FROM Schedule ORDER BY Room";
    $result = $conn->query($sql);
    if($result->num_rows < 1 ){
        echo "ERROR: No Rooms found to display in dropdown menue";
     }
     else{
      $tempArray = array();
     while($row = $result->fetch_assoc()){
        $room = $row["Room"];
     echo '<option value="'.$room.'">'.$room.' </option>';
    
     }
   echo "</select>
    <input type=\"submit\" value=\"Submit!\">
    </form>";

    }
}
$conn->close();

?>
    <p>Select A Class AND A Class Number Then Click Submit.</p>

<?php
$sn = 'localhost';
$un = 'root';
$pw = '';
$db = 'CSC350GroupG';                           
$conn = mysqli_connect($sn, $un, $pw, $db );

if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}
else{
    $sql=" SELECT DISTINCT CourseNo FROM Schedule";
    $result = $conn->query($sql);
    if($result->num_rows < 1 ){
        echo "ERROR: No Courses found to display in dropdown menue";
     }
    else{
    echo "<form action=\"courseNoOption.php\" method=\"get\">
    <select id=\"courseChoice\" name=\"courseChoice\">";
    
    $courseArray = array();
    $courseNumberArray = array();
     while($row = $result->fetch_assoc()){
        $courseNo = $row["CourseNo"];
        $splitCourseNumberArray = str_split($courseNo, 3); //Serparate course from the number. EX. CSC101 = CSC and 101.
        $courseArray[] = $splitCourseNumberArray[0];
        $courseNumberArray[] = $splitCourseNumberArray[1];
    }
     // Delete duplicate courses. Otherwise duplicate course display in dropdown.
     $courseArrayNoDuplicate = array_unique($courseArray);
     $courseNumberArrayNoDuplicate = array_unique($courseNumberArray);
     $arrayLength = count($courseArray);
     for($i = 0; $i < $arrayLength; $i++){
         if($courseArrayNoDuplicate[$i] != "")
        echo '<option value="'.$courseArrayNoDuplicate[$i].'">'.$courseArrayNoDuplicate[$i].' </option>';
     }
     echo '<option value="*"> * </option>';
   echo "</select>
   <select id=\"courseNumberChoice\" name=\"courseNumberChoice\">";
    for($i = 0; $i < $arrayLength; $i++){
        if($courseNumberArrayNoDuplicate[$i] != "")
       echo '<option value="'.$courseNumberArrayNoDuplicate[$i].'">'.$courseNumberArrayNoDuplicate[$i].' </option>';
    }
    echo '<option value="*"> * </option>';
  echo "</select>
    <input type=\"submit\" value=\"Submit!\">
    </form>";

    }
}


?>

</body>
</html>
