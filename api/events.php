<?php
include_once("../int/fest.php");
include_once("../int/ProgLib.php");

$performers = [];

function Get_Events($subEventId) {
    global $db,$YEAR,$Event_Access_Type;

    $qry="
        SELECT DISTINCT 
            e.EventId, e.SN as Name, e.Description, e.Day, e.Start, e.End, IF(e.IsConcert OR t.IsConcert, 1, 0) as IsConcert,
            e.NonFest, e.Importance, e.Bar, e.Food, e.BarFoodText,
            e.DoorPrice, e.SeasonTicketOnly, eS.SN as EventSponsor,
            e.Side1, e.Side2, e.Side3, e.Side4,
            t.SN as Type, e.ListDance, e.ListMusic, e.ListComedy, e.ListWorkshop, e.Family, e.Special,
            v.VenueId, v.ShortName, v.Address, v.Lat, v.Lng, vS.SN as VenueSponsor
        FROM
            Events e
            INNER JOIN EventTypes t ON t.ETypeNo = e.Type
            INNER JOIN Venues v ON v.VenueId = e.Venue
            INNER JOIN VenueYear vY ON v.VenueId = vY.VenueId
            LEFT JOIN Sponsors eS ON e.SponsoredBy = eS.SponsorId
            LEFT JOIN Sponsors vS ON vY.SponsoredBy = vS.SponsorId
        WHERE
            e.SubEvent=$subEventId
            AND e.Year='$YEAR' AND vY.Year='$YEAR' 
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
            $row['SubEvents'] = Get_Events($row['EventId']);
            $row["Access"] = $Event_Access_Type[$row['SeasonTicketOnly']];
            $row['Performer1'] = Get_Performer($row['Side1']);
            $row['Performer2'] = Get_Performer($row['Side2']);
            $row['Performer3'] = Get_Performer($row['Side3']);
            $row['Performer4'] = Get_Performer($row['Side4']);
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
            p.SideId, p.SN, 
            p.Type, p.IsASide, p.IsAnAct, p.IsOther, p.IsFunny, p.IsFamily, p.IsCeilidh, p.IsNonPerf, 
            p.Importance, p.Photo, p.CostumeDesc, p.Description, p.Blurb, 
            p.Website, p.Facebook, p.Twitter, p.Instagram, p.Facebook, p.Video,
            s.SN as Sponsor
        FROM
            Sides p
            INNER JOIN SideYear y ON p.SideId=y.SideId
            LEFT JOIN Sponsors s ON y.SponsoredBy = s.SponsorId
        WHERE
            y.Year='$YEAR'
            AND ((p.IsASide AND y.Coming=2) OR (p.IsOther AND y.YearState > 2))
            AND y.ReleaseDate <= UNIX_TIMESTAMP()
        ";
    $res = $db->query($qry);
    if ($res) {
        while($row = $res->fetch_assoc()) {
            array_push($performers, $row);
        }
    }
}

function Get_Performer($id) {
    global $performers;
    foreach ($performers as $key => $value) {
        if ($value["SideId"] == $id) {
            return $value;
        }
    }
    return null;
}

Load_Performers();

header("Content-Type: text/json");
echo json_encode(Get_Events(-1), JSON_NUMERIC_CHECK);