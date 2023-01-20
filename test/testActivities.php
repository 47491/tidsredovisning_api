<?php

declare (strict_types=1);
require_once "../src/activities.php";
/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testActivityFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen test_$funktion finns inte.</p>";
    }
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    $retur .= "<p class='ok'>Testar hämta alla aktiviteter</p>";
    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try{
        $svar=hamtaEnskild(-1);
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med stort (100) tal get {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskild(100);
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med stort (100) tal get {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskild((int)"sju");
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med bokstäver ('sju') tal ger {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskild(3);
        if ($svar->getStatus()===200) {
            $retur .="<p class='ok'>hämta enskild med 3 ger förväntat svar 200</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med 3 ger {$svar->getStatus()}"
            . "inte förväntat svar 200</p>"; 
        }
    } catch (exception $ex) {
        $retur .="<p class='error'> Någor gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    //testa tom aktivitet
    $aktivitet="";
    $svar=sparaNy($aktivitet);
    if($svar->getStatus()===400){
        $retur .="<p class='ok'>Spara tom aktivitet misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'> Spara tom aktivitet returerade {$svar->getStatus()} förväntades 400</p>";
    } 

    //Testa lägg till 
    $db= connectDb();
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar=sparaNy($aktivitet);
    if($svar->getstatus()===200) {
        $retur .="<p class='ok'>Spara tom aktivitet misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'> Spara tom aktivitet returerade {$svar->getStatus()} förväntades 200</p>";
    }  
    $db->rollBack();
    
    //Testa lägg till samma
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar=sparaNy($aktivitet);
    $svar=sparaNy($aktivitet);
    if($svar->getstatus()===400) {
        $retur .="<p class='ok'>Spara aktivitet två gånger misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'> Spara tom aktivitet returerade {$svar->getStatus()} förväntades 400</p>";
    }  
    $db->rollBack();

    return $retur;
  
    
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";

    try{
    //Testa uppdater med ny text i aktivitet
    $db=connectDb();
    $db->beginTransaction();
    $nyPost=sparaNy("Nizze");
    if($nyPost->getStatus()!==200){
        throw new Exception("skapa ny post misslyckades", 10001);
    } 
    $uppdateringsId=(int)$nyPost->getContent()->id; //Den nya posten id
    $svar=uppdatera($uppdateringsId, "Pelle");  //Prova att updatera

    if($svar->getStatus()===200 && $svar->getContent ()->result===true){
        $retur .="<p class='ok'>Uppdatera aktivitet lyckades</p>";
    } else {
        $retur .="<p class='error'> uppdatera aktivitet misslyckades";
  

        if(isset($svar->getContent()->result)) {
            $retur .= var_export($svar->getContent()->result) . "returnerades istället för förväntat 'true'";
        } else {
            $retur .= "{$svar->getStatus()} returnerades istället för förväntat 200";
        }
        $retur .="</p>";
    } 

    $db->rollBack();

    //Testa uppdatera med samma text i aktivitet

    //Testa Med tom aktivitet

    //testa med ogiltigt id (-1)

    //testa med obefintligt id (100)
} catch (Exception $ex) {
    $db->rollBack();
    if ($ex->getCode()===10001) {
        $retur .= "<p class='error'>Spara ny post misslyckades, uppdatera går inte att testa!!!</p>";
    } else {
        $retur .="<p class='error'>Fel inträffande:<br>{$ex->getmessage()}</p>";
    }
}
    
    return $retur;
}
    

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    $retur .= "<p class='ok'>Testar radera aktivitet</p>";
    return $retur;
}
