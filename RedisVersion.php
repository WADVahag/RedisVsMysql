<?php

set_time_limit(0);
require_once 'DBHelper.php';
require_once 'DateTimeManager.php';

$dbHelper = new DBHelper();
$storageThreshold = 5000;

require 'Predis/autoload.php';



Predis\Autoloader::register();

$client = new Predis\Client();

$client->set("counter", "0");

$conn = $dbHelper->mysqlIconnectToDB("localhost", "letmein", "changeme", 3306, "redis_test");

resetDB($conn);

$storageResponseTimes = array();

//Get the start time
$processStartTime = DateTimeManager::getEpochFromCurrentTime() * 1000;

for ($i = 0; $i <= $storageThreshold; $i++)
{
    if (checkIfOverStorageThreshold($client, $storageThreshold))
    {
        flushCounterToDB($conn, $client);
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
        incrementOrganisationID($client);
        $storageEndTime = DateTimeManager::getEpochFromCurrentTime() * 1000;
        $storageTimeDifference = $storageEndTime - $storageStartTime;
        $storageResponseTimes[] = $storageTimeDifference;
    }
}

/**
 * @param \Predis\Client $client
 * @param $storageThreshold
 * @return bool
 */
function checkIfOverStorageThreshold($client, $storageThreshold)
{
    if (intval($client->get("counter")) >= $storageThreshold)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * @param \Predis\Client $client
 * @throws DBException
 */
function incrementOrganisationID($client)
{
    $client->incr("counter");
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
 * @param \Predis\Client $client
 * @throws DBException
 */
function flushCounterToDB($conn, $client)
{
    $counter = intval($client->get("counter"));

    $query = "UPDATE counter SET counter='$counter' WHERE OrganisationID='1'";
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
