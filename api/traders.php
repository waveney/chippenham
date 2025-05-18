<?php
include_once("../int/fest.php");
include_once("../int/ProgLib.php");
include_once("../int/TradeLib.php");

$locations = [];

function Get_Traders(): array {
    global $db,$YEAR,$Trade_Days,$Trade_State,$YEARDATA;
    if ($YEARDATA != null && $YEARDATA['TradeState'] > 1) {
        $qry="
            SELECT
                t.Tid as id, t.SN as Name, t.BizName,
                t.Website, t.Photo, t.GoodsDesc,
                tp.SN as TypeName, tp.Description as TypeDescription, tp.Colour as typeColour, tp.ListOrder as TypeOrder,
                y.PitchLoc0, y.PitchLoc1, y.PitchLoc2, y.Days
            FROM
                Trade t
                INNER JOIN TradeYear y ON t.Tid = t.Tid
                INNER JOIN TradePrices tp ON tp.id = t.TradeType
                INNER JOIN TradeLocs
            WHERE
                t.IsTrader=1 AND t.Status=0 AND
                y.Year='$YEAR' AND
                ((y.BookingState>=".$Trade_State['Deposit Paid']." AND y.BookingState<".$Trade_State['Wait List'] . ") OR y.ShowAnyway)
            ";
        $res = $db->query($qry);
        if ($res) {
            $traders = [];
            while($row = $res->fetch_assoc()) {
                $row['PitchLoc0'] = Get_API_Location($row['PitchLoc0']);
                $row['PitchLoc1'] = Get_API_Location($row['PitchLoc1']);
                $row['PitchLoc2'] = Get_API_Location($row['PitchLoc2']);
                $row['Days'] = $Trade_Days[$row['Days']];
                array_push($traders, $row);
            }
            return $traders;
        }
    }
    return [];
}

function Load_Locations() {
    global $db,$locations;
    $locations = [];

    $qry="
        SELECT
            l.TLocId as id, l.SN as locationName, l.prefix as locationPrefix
        FROM
            TradeLocs l
        ";
    $res = $db->query($qry);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            array_push($locations, $row['Id']);
        }
    }
}

function Get_API_Location($id) {
    global $locations;
    foreach ($locations as $key => $value) {
        if ($value["id"] == $id) {
            return $value;
        }
    }
    return null;
}

function Get_FoodAndDrink(): array {
    global $db,$YEAR;
    $qry="
        SELECT
            fd.id, fd.SN as Name, fd.Description,
            fd.Website, fd.PostCode, fd.Phone, fd.Photo, fd.Lat, fd.Lng, fd.Address,
            fd.Vegan, fd.Vegetarian, fd.Food, fd.Drink,
            fd.Notes, fd.Importance, fd.MapImp, fd.Directions,
            m.SN as TypeName, m.Icon as TypeIcon
        FROM
            FoodAndDrink fd
            LEFT JOIN MapPointTypes m ON m.id = fd.Type
        WHERE
            fd.Year='$YEAR'
        ";
    $res = $db->query($qry);
    if ($res) {
        $traders = [];
        while($row = $res->fetch_assoc()) {
            array_push($traders, $row);
        }
        return $traders;
    }
    return [];
}

Load_Locations();

header("Content-Type: text/json");
$data = array();
$data['traders'] = Get_Traders();
$data['foodAndDrink'] = Get_FoodAndDrink();
echo json_encode($data, JSON_NUMERIC_CHECK);