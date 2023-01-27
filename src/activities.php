<?php

declare (strict_types=1);
require_once __DIR__ . "/funktioner.php";
/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function activities(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::GET) {
            return hamtaAllaAktiviteter();
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildAktivitet((int) $route->getParams()[0]);
        }
        if (isset($postData["activity"]) && $route->getMethod() === RequestMethod::POST) {
            return SparaNyAktivitet((string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraAktivitet((int) $route->getParams()[0], (string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaAktivitet((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Returnerar alla aktiviteter som finns i databasen
 * @return Response
 */
function hamtaAllaAktiviteter(): Response {
//Koppla mot databasen
$db = connectDb();

// Hämta alla poster från tabellen
$resultat = $db->query("SELECT id, kategorier from kategorier");

//Lägga in posterna i en array
$retur = [];
while ($row = $resultat->fetch()) {
    $post = new stdClass();
    $post->id = $row['id'];
    $post->activity = $row['kategorier'];
    $retur[] = $post;


    $out=new stdClass();
    $out->activities=$retur;

    return new Response($out, 200);
    
}
}

/**
 * Returnerar en enskild aktivitet som finns i databasen
 * @param int $id Id för aktiviteten
 * @return Response
 */

/**
 * Lagrar en ny aktivitet i databasen
 * @param string $aktivitet Aktivitet som ska sparas
 * @return Response
 */
function SparaNyAktivitet(string $aktivitet): Response {
    $kontrolleradAktivitet=trim($aktivitet);
    $kontrolleradAktivitet= filter_var($kontrolleradAktivitet, FILTER_SANITIZE_ENCODED);
    
    if($kontrolleradAktivitet===""){
        $out=new stdClass();
        $out->error=["fel vid spara", "activity kan inte vara tom"];
        return new Response($out, 400);
    }

    try{

    
    //koppla mot databas
    $db= connectDb();

    $stmt=$db->prepare("INSERT INTO Kategorier (Kategorier) Values (:Kategori)");
    $antalPoster=$stmt->execute(["Kategori"=>$kontrolleradAktivitet]);
    $antalPoster=$stmt->rowCount();
    

    //returera svaren
    if($antalPoster>0) {
        $out=new stdClass();
        $out->message=["Spara lyckades", "$antalPoster post(er) lades till"];
        $out->id=$db->lastInsertid();
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["Något gick fel vid spara", implode(",", $db->errorInfo())];
        return new Response($out, 400);
    }
    } catch (Exception $ex){

        $out=new stdClass();
        $out->error=["Något gick fel vid spara", $ex->getMessage()];
        return new Response($out, 400);

    }
}
/**
 * Uppdaterar angivet id med ny text
 * @param int $id Id för posten som ska uppdateras
 * @param string $aktivitet Ny text
 * @return Response
 */
// kontrolera data 
 function hamtaEnskildAktivitet(int $id): Response{
    $kollatID= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID  || $kollatID<1) {
        $out= new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response ($out, 400);
    }

    $db=connectDb ();
     // koppla databas och hämta post
    $stmt=$db->prepare("select id, Kategorier from kategorier where id=:id");
    if (!$stmt->execute(["id"=>$kollatID])) {
        $out=new stdClass();
        $out->error=["fel vid läsning från databasen", implode(",",$stmt->errorInfo())];
        return new Response($out,400);
    }
    // Sätt utdata och returnera
    if($row=$stmt->fetch()){
        $out=new stdClass();
        $out->id=$row["id"];
        $out->activity=$row["Kategorier"];
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["hittade ingen post med id=$kollatID"];
        return new Response($out, 400);
    }

    //return new Response("hämta akrivitet $id",200);
 }
function uppdateraAktivitet(int $id, string $aktivitet): Response {
    //kontrollera indata
    $kollatID= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID  || $kollatID<1) {
        $out= new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response ($out, 400);
    }
    $kontrolleradAktivitet=trim($aktivitet);
    $kontrolleradAktivitet= filter_var($kontrolleradAktivitet, FILTER_SANITIZE_ENCODED);
   
    if($kontrolleradAktivitet===""){
        $out=new stdClass();
        $out->error=["fel vid uppdateraAktivitet", "activity kan inte vara tom"];
        return new Response($out, 400);
    }

    try{
    //koppla databas
    $db= connectDb();


    //uppdateraAktivitet post
    $stmt = $db->prepare ("Update kategorier"
        ." SET Kategorier=:aktivitet"
        ." WHERE id=:id");
    $stmt->execute(["aktivitet" =>$kontrolleradAktivitet,"id" => $kollatID]);
    $antalPoster = $stmt->rowCount();

    //returnera svar
  
    $out = new stdClass();
    if($antalPoster > 0) {
        $out->result =true;
        $out->message = ["uppdateraAktivitet lyckades", "$antalPoster poster uppdaterades"];
    } else  {
        $out->result = false;
        $out->message = ["uppdateraAktivitet lyckades", "oposter uppdaterades"];
    }

    return new Response ($out, 200);
} catch (Exception $ex) {
    $out =new stdClass();
    $out->error = ["Något fick fel vid spara", $ex->getMessage()];
    return new Response($out, 400);
}
}

/**
 * Raderar en aktivitet med angivet id
 * @param int $id Id för posten som ska raderas
 * @return Response
 */
function raderaAktivitet(int $id): Response {
    //kontrollera id
    $kollatID= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID  || $kollatID<1) {
        $out= new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response ($out, 400);
    }

    try{
    //koppla mot databas
    $db= connectDb();

    //skicka raderaAktivitet-kommando
    $stmt = $db->prepare ("DELETE From kategorier"
        ." WHERE id=:id");
    $stmt->execute(["id" =>$kollatID]);
    $antalPoster = $stmt->rowCount();
    //kontrollera databas-svar och skapa utdata-svar
        $out=new stdClass();
        if($antalPoster>0) {
            $out->result=true;
            $out->message=["Radera lyckades", "$antalPoster psot(er) raderades"];
        } else {
            $out->result=false;
            $out->message=["Radera misslyckades", "inga poster raderades"];
        }

        return new Response($out);
}  catch (Exception $ex) {
    $out =new stdClass();
    $out->error = ["Något fick fel vid raderaAktivitet", $ex->getMessage()];
    return new Response($out, 400);
}
}