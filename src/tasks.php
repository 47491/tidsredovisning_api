<?php

declare (strict_types=1);

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter 
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum(new DateTimeImmutable($route->getParams()[0]), new DateTimeImmutable($route->getParams()[1]));
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return SparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift((int) $route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param int $sida
 * @return Response
 */
function hamtaSida(int $sida): Response {
    $postePerSida=3;

    //kolla att id är ok
    $kollatSidnr=filter_var($sida, FILTER_VALIDATE_INT);
    if(!$kollatSidnr || $kollatSidnr<1) {
        $out = new stdClass();
        $out-> error=["felaktigt sidnummer ($sida( angivet", "Läsning misslyckades"];
        return new Response($out, 400);
    }
    //koppla mot databasen
    $db=connectDb();
    //hämta antal poster 
    $result=$db->query("SELECT COUNT(*) FROM kategorier");
    if($row=$result->fetch()) {
        $antalPoster=$row[0];
    }
    $antalSidor=ceil($antalPoster/$postePerSida);
    //hämta aktuella poster
    $first=($kollatSidnr-1) *$postePerSida;
    $postePerSida;
    $result=$db->query("SELECT t.ID, Tid, Datum, KategoriID, Beskrivning, kategorier"
    ." FROM uppgifter t "
    ." INNER JOIN kategorier a ON KategoriID=a.ID "
    ." ORDER BY Datum asc "
    ." LIMIT $first, $postePerSida ");
    //loopa resultatsettet och skapa data
    $record=[];
    while($row=$result->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["KategoriID"];
        $rec->activity=$row["kategorier"];
        $rec->date=$row["Datum"];
        $rec->time=substr($row["Tid"], 0,5);
        $rec->description=$row["Beskrivning"];
        $record[]=$rec;
    }
    //returnera utdata 
    $out=new stdClass();
    $out->pages=$antalSidor;
    $out->tasks=$record;
    return new Response($out);
}

/**
 * Hämtar alla poster mellan angivna datum
 * @param DateTimeInterface $from
 * @param DateTimeInterface $tom
 * @return Response
 */
function hamtaDatum(DateTimeInterface $from, DateTimeInterface $tom): Response {
    // kolla indata
    if($from->format('Y-m-d')>$tom->format('Y-m-d')) {
        $out=new stdClass();
        $out->error=["felaktig indata", "från-datum ska vara mindre än till-datum"];
        return new Response($out, 400);

    }

    // koppla databas
    $db=connectDb();
    // hämta poster
    $stmt=$db->prepare("SELECT t.ID, Tid, Datum, KategoriID, Beskrivning, kategorier"
        ." FROM uppgifter t "
        ." INNER JOIN kategorier a ON KategoriID=a.ID "
        ." WHERE Datum between :from AND :to"
        ." ORDER BY Datum asc ");
    $stmt->execute(["from"=>$from->format('Y-m-d'), "to"=>$tom->format('Y-m-d')]);

// loopa resultatsettet och skapa utdata
    $records =[];
    while($row=$stmt->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["KategoriID"];
        $rec->activity=$row["kategorier"];
        $rec->date=$row["Datum"];
        $rec->time=substr($row["Tid"], 0,5);
        $rec->description=$row["Beskrivning"];
        $records[]=$rec;
    }
    //returnera utdata 
    $out=new stdClass();
    $out->tasks=$records;

    return new Response($out);
}

/**
 * Hämtar en enskild uppgiftspost
 * @param int $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(int $id): Response {
    return new Response("Hämta task $id", 200);
}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postData): Response {
    //kolla indata
    $check=kontrolleraIndata($postData);
    if($check!=="") {
        $out=new stdClass();
        $out->error=["felaktig indata", $check];
        return new response ($out, 400);

    }
    //koppla mot databas
    $db= connectDb();

    //förbered och exekvera SQL
    $stmt=$db->prepare("INSERT INTO uppgifter"
        . "(Datum, Tid, kategoriID, beskrivning)"
        . " VALUES (:date, :time, :activityId, :description)");
       
        $stmt->execute(["date"=>$postData["date"],
        "time"=>$postData["time"],
        "activityId"=>$postData["activityId"],
        "description"=>$postData["description"]?? ""]);

    //kontrollera svar
    $antalPoster=$stmt->rowCount();
    if($antalPoster>0)  {
        $out=new stdClass();
        $out->id=$db->lastInsertId();
        $out->message=["spara ny uppgift lyckades"];
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["spara ny uppgift misslyckades"];
        return new response($out,400);
    }
    //skapa utdata



    return new Response("Sparar ny task", 200);
}

/**
 * Uppdaterar en angiven uppgiftspost med ny information 
 * @param int $id id för posten som ska uppdateras
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(int $id, array $postData): Response {
    return new Response("Uppdaterar task $id", 200);
}

/**
 * Raderar en uppgiftspost
 * @param int $id Id för posten som ska raderas
 * @return Response
 */
function raderaUppgift(int $id): Response {
    return new Response("Raderar task $id", 200);
}

function kontrolleraIndata(array $postData):string {
    try{
        // kontrollera giltigt datum
        if(!isset($postData["time"])) {
            return "datum saknas (date)";
        }

        if(!isset($postData["date"])) {
            return "datum saknas (date)";
        }
        $datum= DateTimeImmutable::createFromFormat ("Y-m-d", $postData["date"]);
        if(!$datum || $datum->format('Y-m-d')>date("Y-m-d")) {
            return"Ogiltigt datum";
        }
        //kontrollera giltig tid 
        $tid=DateTimeImmutable::createFromFormat("H:i", $postData["time"]);
        if(!$tid || $tid->format("H:i")>"08:00") {
            return "ogiltigt tid (time)";
        }
        // kontrollera aktivitetsid
        $aktivitetsId=filter_var($postData["activityId"], FILTER_VALIDATE_INT);
        if(!$aktivitetsId || $aktivitetsId<1) {
            return "ogiltigt aktivitesid (activityId)";
        }
        $svar=hamtaEnskildAktivitet($aktivitetsId);
        if($svar->getStatus()!==200) {
            return "angivet aktivitetsid saknas";
        }
        return "";
    } catch (exception $exc) {
        return $exc->getMessage();
    } 
}