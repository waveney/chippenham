<?php
include_once("../int/fest.php");
include_once("../int/ProgLib.php");

//how do I get second event type?
//how do I get event performers

function Get_Events() {
    global $db,$YEAR,$Event_Access_Type;
    $qry="
        SELECT DISTINCT 
            e.EventId, e.SN as Name, e.Description, e.Day, e.Start, e.End,
            e.DoorPrice, e.SeasonTicketOnly,
            t.SN as Type,
            v.VenueId, v.ShortName, v.Address, v.Lat, v.Lng
        FROM
            Events e
            INNER JOIN EventTypes t ON t.ETypeNo=Type
            INNER JOIN Venues v ON v.VenueId=e.Venue
        WHERE
            Year='$YEAR' And 
            e.Public And t.Public And v.Complete
        ";
    $res = $db->query($qry);
    if ($res) {
        $evs = [];
        while($ev = $res->fetch_assoc()) {
            $ev["Access"] = $Event_Access_Type[$ev['SeasonTicketOnly']];
            array_push($evs, $ev);
        }
        return $evs;
    }
    return [];
}

header("Content-Type: text/json");
echo json_encode(Get_Events());