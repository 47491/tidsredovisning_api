<?php

declare (strict_types=1);
require_once'../src/tasks.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testTaskFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen $funktion kan inte testas.</p>";
    }
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaUppgifterSida(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try{
    //testa hämta felaktigt sidnummer (-1)=>400
    $svar = hamtaSida(-1);
    if($svar->getStatus()===400) {
        $retur .= "<p class='ok'> hämta felaktiga sidnummer (-1) gav förväntat svar 400</p>";
    } else {
        $retur .= "<p class ='error' hämta felaktiga sidnummer (-1) gav {$svar->getStatus()}"
        . "istället för förväntat 400 </p>";
    }
    //testa hämta giltigt sidnummer (1) => 200 + rätt egenskaper
    $svar=hamtaSida(1);
    if($svar->getStatus()!==200)    {
        $retur .="<p class='error'> hämta giltigt sidnummer (1) gav förväntat svar 200 {$svar->getStatus()} "
        . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'> hämta giltigt sidnummer (1) gav förväntat svar 200 </p>";
        $result = $svar-> getContent() -> tasks;
        foreach ($result as $task){
            if(!isset($task->id)) {
                $retur .="<p class='error'> Egenskapen id saknas</p>";
                break;
            }
            if(!isset($task->activityId)) {
                $retur .="<p class='error'> Egenskapen activityId saknas</p>";
                break;
            }
            if(!isset($task->activity)) {
                $retur .="<p class='error'> Egenskapen activity saknas</p>";
                break;
            }
            if(!isset($task->date)) {
                $retur .="<p class='error'> Egenskapen date saknas</p>";
                break;
            }
            if(!isset($task->time)) {
                $retur .="<p class='error'> Egenskapen time saknas</p>";
                break;
            }
        }
    }
    // testa hämta för stor sidnr => 200 + tom array
    $svar=hamtaSida(100);
    if ($svar->getStatus()!==200) { $retur .="<p class='error'> hämta för stort sidnummer (100) gav{$svar->getStatus()} "
    . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'> hämta för stort sidnummer (100) gav förväntat svar 200</p>";
        $resultat =$svar ->getContent()->tasks;
        if(!$resultat===[]) {
            $retur .= "<p class='error'> hämta för stort sidnummer ska innehålla en tom array för tasks<br>"
            . print_r($resultat, true) . "<br> returnerades</p>";
        }
    }

    }catch (Exception $ex) {
        $retur .="<p class='error'> något gick fel, meddelandet säger: <br> {$ex->getMessage()}</p>";
    } 

    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    //testa fel ordning på datum
    $datum1=new DateTimeImmutable();
    $datum2=new DateTime("yesterday");
    $svar=hamtaDatum($datum1, $datum2);
    if($svar->getStatus()===400) {
        $retur .= "<p class='ok'> Hämta fel ordning på datum gav förväntat svar 400</p>";

    } else {
        $retur .= "<p class='error'>hämta fel ordning på datum gav {$svar->getStatus()}"
        . "istället för förväntat svar 400</p>"; 
    }

    //Testa datum utan poster 
    $datum1=new DateTimeImmutable("1970-01-01");
    $datum2=new DateTimeImmutable("1970-01-01");
    $svar = hamtaDatum($datum1, $datum2);
    
    if ($svar->getStatus()!==200) { $retur .="<p class='error'> hämta datum (1970-01-01 -- 1970-01-01) gav{$svar->getStatus()} "
    . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'>  hämta datum (1970-01-01 -- 1970-01-01)  gav förväntat svar 200</p>";
        $resultat =$svar ->getContent()->tasks;
        if(!$resultat===[]) {
            $retur .= "<p class='error'> hämta datum (1970-01-01 -- 1970-01-01) ska innehålla en tom array för tasks<br>"
            . print_r($resultat, true) . "<br> returnerades</p>";
        }
    }


    //testa giltiga datum med poster
 
    $datum1=new DateTimeImmutable("1970-01-01");
    $datum2=new DateTimeImmutable();
    $svar = hamtaDatum($datum1, $datum2);

    if($svar->getStatus()!==200)    {
        $retur .="<p class='error'> hämta poster för datum (1970-01-01 --{$datum2->format('Y-m-d')}"
        . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'> hämta poster för datum (1970-01-01 --{$datum2->format('Y-m-d')} "
        . "gav förväntat svar 200 </p>";
        $result = $svar-> getContent() -> tasks;
        foreach ($result as $task){
            if(!isset($task->id)) {
                $retur .="<p class='error'> Egenskapen id saknas</p>";
                break;
            }
            if(!isset($task->activityId)) {
                $retur .="<p class='error'> Egenskapen activityId saknas</p>";
                break;
            }
            if(!isset($task->activity)) {
                $retur .="<p class='error'> Egenskapen activity saknas</p>";
                break;
            }
            if(!isset($task->date)) {
                $retur .="<p class='error'> Egenskapen date saknas</p>";
                break;
            }
            if(!isset($task->time)) {
                $retur .="<p class='error'> Egenskapen time saknas</p>";
                break;
            }
        }
    }


    return $retur;
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaEnUppgift</h2>";

    try{
        $svar=hamtaEnskildUppgift(-1);
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med stort (100) tal get {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskildUppgift(100);
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med stort (100) tal get {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskildUppgift((int)"sju");
        if ($svar->getStatus()===400) {
            $retur .="<p class='ok'>hämta enskild med negativt tal ger förväntat svar 400</p>"; 
        } else {
            $retur .="<p class='error'>hämta enskild med bokstäver ('sju') tal ger {$svar->getStatus()}"
            . "inte förväntat svar 400</p>"; 
        }

        $svar=hamtaEnskildUppgift(3);
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
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";
    try {
    //testa allt ok
    $igar=new DateTimeImmutable("yesterday");
    $imorgon=new DateTimeImmutable("tomorrow");

    $postdata=["date"=>$igar->format('Y-m-d'),
        "time"=>"05:00",
        "activityId"=>1,
        "description"=>"hutta vad bra"];
        $db= connectDb();
        $db->beginTransaction();
        $svar= sparaNyUppgift($postdata);
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>spara ny uppgift lyckades</p>";
        } else {
            $retur .="<p class='error'> spara ny uppgift misslyckades {$svar->getStatus()}"
            ."returnerades iställe för förväntat 200</p>";
        }
        $db->rollBack();
    //testa felaktigt datum (i morgon) => 400
        $postdata["date"]=$imorgon->format("Y-m-d");

        $db->beginTransaction();
        $svar= sparaNyUppgift($postdata);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (date = imorgon)</p>";
        } else {
            $retur .="<p class='error'> spara ny uppgift returnerade {$svar->getStatus()}"
            ."returnerades iställe för förväntat 400</p>";
        }
        $db->rollBack();
    // testa felaktigt datumformat =>400
    $postdata["date"]=$igar->format("d.m.y");
    $db= connectDb();
    $db->beginTransaction();
    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (felaktigt datumformat)</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift med felaktigt datumformat "
        ."returnerades {$svar->getStatus()}400</p>";
    }
    $db->rollBack();
    // testa datum saknas =>400
    unset($postdata["date"]);
    $db->beginTransaction();
    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (datum saknas)</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift med felaktigt datumformat"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();
    // testa felaktigt tid (12 timmar) =>400
    $db->beginTransaction();
    $postdata ["date"]=$igar->format('Y-m-d');
    $postdata["time"]="12:00"; 

    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (felaktigt tid 12:00)</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift med felaktigt tid (12:00)"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();
    // testa felaktigt tidsformat =>400
    $postdata["time"]="5_30"; 
    $db->beginTransaction();
    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (felaktigttidsformat)</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift med felaktigt tidsformat"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();

    // testa tid saknas =>400
  
    $db->beginTransaction();
    unset($postdata ["time"]); 

    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift misslyckades som förväntat (felaktigttidsformat)</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift med felaktigt tidsformat"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();


    //testa description saknas => 200
    unset($postdata["description"]);
    $postdata["time"]="3:15";
      
    $db->beginTransaction();

    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===200) {
        $retur .="<p class='ok'>spara ny uppgift utan beskrivning</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift utan beskrivning"
        ."returnerades{$svar->getStatus()} istället för förväntat 200</p>";
    }
    $db->rollBack();


    //testa aktivitetsId felaktigt (-1) => 400
    $postdata["activityId"]=-1;

    $db->beginTransaction();

    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift med felaktigt aktivityId (-1) misslyckades, som förväntat</p>";
    } else {
        $retur .="<p class='error'> spara ny uppgift utan felaktigt activityId"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();

    // testa aktivitetsid som saknas (100) => 400
    $postdata["activityId"]=-100;

    $db->beginTransaction();

    $svar= sparaNyUppgift($postdata);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>spara ny uppgift med felaktigt aktivityId (100) misslyckades, som förväntat</p>";
    } else {
        
        $retur .="<p class='error'> spara ny uppgift med felaktiv activityId (100)"
        ."returnerades{$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();

    } catch (exception $ex) {
        $retur .=$ex->getmessage();
    }
    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";
    $retur .= "<p class='ok'>Testar uppdatera uppgift</p>";
    return $retur;
}

/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";
    $retur .= "<p class='ok'>Testar radera uppgift</p>";
    return $retur;
}
