<?php
include_once("../int/fest.php");
include_once("../int/ProgLib.php");

$performers = [];
$venues = [];

//!! need to implement overlays

function Get_Events($subEventId) {
    global $db,$YEAR,$Event_Access_Type;

    $qry="
        SELECT DISTINCT 
            e.EventId as Id, e.SN as Name, e.Description, e.Day, e.Start, e.End, e.SlotEnd, IF(e.IsConcert OR t.IsConcert, 1, 0) as IsConcert,
            e.NonFest, e.Importance, e.Bar, e.Food, e.BarFoodText,
            e.DoorPrice, e.SeasonTicketOnly, e.SponsoredBy,
            e.Side1, e.Side2, e.Side3, e.Side4,
            e.Roll1, e.Roll2, e.Roll3, e.Roll4,
            t.SN as Type, e.ListDance, e.ListMusic, e.ListComedy, e.ListWorkshop, e.Family, e.Special,
            e.Venue
        FROM
            Events e
            INNER JOIN EventTypes t ON t.ETypeNo = e.Type
        WHERE
            e.SubEvent=$subEventId
            AND e.Year='$YEAR'
            AND e.Public <= 1 AND t.Public AND NOT t.DontList
        ";
    $res = $db->query($qry);
    if ($res) {
        $evs = [];
        while($row = $res->fetch_assoc()) {
            if ($subEventId > 0 && $row['IsConcert']) {
                $row['Start'] = null;    
                $row['End'] = null;
            }
            $row['SubEvents'] = Get_Events($row['Id']);
            $row["Access"] = $Event_Access_Type[$row['SeasonTicketOnly']];
            $row['Performers'] = Get_Performers($row);
            $row['Venues'] = API_Get_Venues($row);
            $row['Sponsors'] = Get_Sponsors($row['SponsoredBy'], $row['Name'], 2, $row['Id']);

            unset($row['Side1']);
            unset($row['Side2']);
            unset($row['Side3']);
            unset($row['Side4']);
            unset($row['Roll1']);
            unset($row['Roll2']);
            unset($row['Roll3']);
            unset($row['Roll4']);
            unset($row['SponsoredBy']);
            unset($row['Venue']);

            array_push($evs, $row);
        }
        return $evs;
    }
    return [];
}

function Load_Performers() {
    global $db,$YEAR,$performers;
    $qry="
        SELECT DISTINCT 
            p.SideId as Id, p.SN as Name,
            p.Type, p.IsASide, p.IsAnAct, p.IsOther, p.IsFunny, p.IsFamily, p.IsCeilidh, p.IsNonPerf,
            p.Importance, p.Photo, p.CostumeDesc, p.Description, p.Blurb,
            p.Website, p.Facebook, p.Twitter, p.Instagram, p.Facebook, p.Video,
            y.SponsoredBy
        FROM
            Sides p
            INNER JOIN SideYear y ON p.SideId=y.SideId
        WHERE
            y.Year='$YEAR'
            AND ((p.IsASide AND y.Coming=2) OR ((p.IsAnAct OR p.IsOther OR p.IsFunny OR p.IsFamily OR p.IsCeilidh) AND y.YearState > 2))
            AND y.ReleaseDate <= UNIX_TIMESTAMP()
        ";
    $res = $db->query($qry);
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $row['Sponsors'] = Get_Sponsors($row['SponsoredBy'], $row['Name'], 3, $row['Id']);
            unset($row['SponsoredBy']);
            array_push($performers, $row);
        }
    }
}

function Get_Performers($eventRow) {
    global $db,$Perf_Rolls;
    $performers = [];

    for ($i = 1; $i <= 4; $i++) {
        $performer = Get_Performer($eventRow['Side'.$i], $Perf_Rolls[$eventRow['Roll'.$i]]);
        if ($performer != null) { array_push($performers, $performer); }
    }

    $bigEventPerformers = Get_BigEventIds($eventRow['Id'], 'Perf');
    foreach ($bigEventPerformers as $id) {
        $performer = Get_Performer($id);
        if ($performer != null) { array_push($performers, $performer); }
    }
    
    return $performers;
}

function Get_Performer($id, $roll = null) {
    global $performers;
    foreach ($performers as $key => $value) {
        if ($value["Id"] == $id) {
            $value['Roll'] = $roll;
            return $value;
        }
    }
    return null;
}



function Load_Venues() {
    global $db,$YEAR,$venues;
    $qry="
        SELECT DISTINCT 
            v.VenueId as Id, v.ShortName, v.Address, v.Lat, v.Lng, y.SponsoredBy
        FROM
            Venues v
            INNER JOIN VenueYear y ON v.VenueId = y.VenueId
        WHERE
            y.Year='$YEAR'
        ";
    $res = $db->query($qry);
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $row['Sponsors'] = Get_Sponsors($row['SponsoredBy'], $row['Name'], 3, $row['Id']);
            unset($row['SponsoredBy']);
            array_push($venues, $row);
        }
    }
}

function API_Get_Venues($eventRow) {
    global $db;
    $venues = [];

    $venue = API_Get_Venue($eventRow['Venue']);
    if ($venue != null) { array_push($venues, $venue); }

    $bigEventVenues = Get_BigEventIds($eventRow['Id'], 'Venue');
    foreach ($bigEventVenues as $id) {
        $venue = API_Get_Venue($id);
        if ($venue != null) { array_push($venues, $venue); }
    }

    return $venues;
}

function API_Get_Venue($id) {
    global $venues;
    foreach ($venues as $key => $value) {
        if ($value["Id"] == $id) {
            return $value;
        }
    }
    return null;
}

function Get_BigEventIds($eventId, $Type) {
    global $db;
    $ids = [];

    $qry="
        SELECT DISTINCT 
            b.Identifier as Id, b.EventOrder
        FROM
            BigEvent b
        WHERE
            b.Event = $eventId
            AND Type = '$Type'
        ORDER BY
            b.EventOrder
        ";
    $res = $db->query($qry);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            array_push($ids, $row['Id']);
        }
    }
    return $ids;
}

function Get_Sponsors($SponsoredBy, &$Name, $TType, $Tid) {
    global $YEAR;
    $sponsors = [];
    if ($SponsoredBy ?? 0) {
        $Spid = $SponsoredBy;
        if ($Spid > 0) {
            $Spon = Gen_Get('Trade', $Spid, 'Tid');
            Add_Sponsor_Object($Spon, $sponsors);
        } else {
            $Spids = Gen_Get_Cond('Sponsorship',"Year=$YEAR AND ThingType=$TType AND ThingId=$Tid ORDER BY Importance, RAND()");
            if ($Spids) {
                foreach ($Spids as $Spid) {
                    $Spon = Gen_Get('Trade',$Spid['SponsorId'],'Tid');
                    Add_Sponsor_Object($Spon, $sponsors);
                }
            }
        }
    }
    return $sponsors;
}

function Add_Sponsor_Object($Spon, &$sponsors) {
    if ($Spon != null) {
        $sponsor = array();
        $sponsor['Id'] = $Spon['Tid'];
        $sponsor['Name'] = $Spon['BizName'] ? $Spon['BizName'] : $Spon['SN'];
        $sponsor['Logo'] = $Spon['Logo'];
        $sponsor['Photo'] = $Spon['Photo'];
        $sponsor['Website'] = $Spon['Website'];
        array_push($sponsors, $sponsor);
    }
}

Load_Performers();
Load_Venues();

header("Content-Type: text/json");
echo json_encode(Get_Events(-1), JSON_NUMERIC_CHECK);