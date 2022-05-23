<?php
set_time_limit(0);
require_once 'DBHelper.php';
require_once 'DateTimeManager.php';

$storageThreshold = 5000;

$dbHelper = new DBHelper();
$conn = $dbHelper->mysqlIconnectToDB("localhost", "letmein", "changeme", 3306, "redis_test");

resetDB($conn);

$storageResponseTimes = array();

//Get the start time
$processStartTime = DateTimeManager::getEpochFromCurrentTime() * 1000;

//Check if the current storage counter is over the threshold

    for ($i = 0; $i <= $storageThreshold; $i++)
    {
        if (checkIfOverStorageThreshold($conn, $storageThreshold))
        {
            $stopProcessTime = DateTimeManager::getEpochFromCurrentTime() * 1000;
            $timeDifference = $stopProcessTime - $processStartTime;
            echo "Time Taken for whole process to Complete: $timeDifference ms\n";

            //Calculate the average response times
            $currentAverage = 0;
            foreach ($storageResponseTimes as $time)
            {
                $currentAverage += $time;
            }
            $currentAverage /= count($storageResponseTimes);
            echo "Average Storage Response Time: $currentAverage ms\n";

            exit();
        }
        else {
            $storageStartTime = DateTimeManager::getEpochFromCurrentTime() * 1000;
            addRecordToDB($conn, $i);
            incrementOrganisationID($conn);
            $storageEndTime = DateTimeManager::getEpochFromCurrentTime() * 1000;
            $storageTimeDifference = $storageEndTime - $storageStartTime;
            $storageResponseTimes[] = $storageTimeDifference;
        }
    }


/**
 * @param mysqli $conn
 * @param $storageThreshold
 * @return bool
 * @throws DBException
 */
function checkIfOverStorageThreshold($conn, $storageThreshold)
{
    $query = "SELECT * FROM counter WHERE OrganisationID='1'";
    $result = $conn->query($query);
    if (!$result)
    {
        throw new DBException(mysqli_error($conn));
    }

    $myrow = $result->fetch_array();
    $counter = abs($myrow["counter"]);
    if ($counter >= $storageThreshold)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * @param mysqli $conn
 * @param $index
 * @throws DBException
 */
function addRecordToDB($conn, $index)
{
    $query = "INSERT INTO storage VALUES (NULL, default, '$index')";
    $result = $conn->query($query);
    if (!$result)
    {
        throw new DBException(mysqli_error($conn));
    }
}

/**
 * @param mysqli $conn
 * @throws DBException
 */
function incrementOrganisationID($conn)
{
    $query = "UPDATE counter SET counter=counter+1 WHERE OrganisationID=1";
    $result = $conn->query($query);
    if (!$result)
    {
        throw new DBException(mysqli_error($conn));
    }
}

/**
 * @param mysqli $conn
 * @throws DBException
 */
function resetDB($conn)
{
    $query = "TRUNCATE storage";
    $result = $conn->query($query);
    if (!$result)
    {
        throw new DBException(mysqli_error($conn));
    }

    $query = "UPDATE counter SET counter=0 WHERE OrganisationID=1";
    $result = $conn->query($query);
    if (!$result)
    {
        throw new DBException(mysqli_error($conn));
    }
}
