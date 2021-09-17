<?php


/* ----------------------
Includes
---------------------- */
include 'config.php';
include 'functions.php';
include 'simple_html_dom.php';
include 'database.php';



/* ----------------------
Initialization
---------------------- */
$website = "https://api.telegram.org/bot".$token;



/* ----------------------
Updates
---------------------- */
$updates = file_get_contents("php://input");
$updates = json_decode($updates,TRUE);



/* ----------------------
Variables
---------------------- */
$json = json_encode($updates, JSON_PRETTY_PRINT);
$text = $updates["message"]["text"];
$chatID = $updates["message"]["from"]["id"];
$name = $updates["message"]["from"]["first_name"];
$username = $updates["message"]["from"]["username"];
$query = $updates["callback_query"];
$queryUserID = $query["from"]["id"];
$querydata = $query["data"];
$querymsid = $query["message"]["message_id"];



/* ----------------------
Timezone
---------------------- */
setlocale(LC_TIME, 'it_IT');
$date = date("Y-m-d");
$time = date("H:i");
$day = strftime("%a");
$_day = date("m-d");
$timestamp = time();
$day_0 = date("d-m-Y");
$day_1 = strtotime($day_0);




/* ----------------------
Menus
---------------------- */
$corsi_menu ='[{"text":"Digital%20health%20and%20bioinformatic%20-%20Magistrale","callback_data":"06229"}],[{"text":"Alimentare%20-%20Magistrale","callback_data":"06228"}],[{"text":"Chimica%20-%20Magistrale","callback_data":"06222"}],[{"text":"Civile%20-%20Magistrale","callback_data":"06221"}],[{"text":"Elettronica%20-%20Magistrale","callback_data":"06224"}],[{"text":"Gestionale%20-%20Magistrale","callback_data":"06226"}],[{"text":"Informatica%20-%20Magistrale","callback_data":"06227"}],[{"text":"Meccanica%20-%20Magistrale","callback_data":"06223"}],[{"text":"Civile%20ambiente%20e%20territorio%20-%20Magistrale","callback_data":"06225"}],[{"text":"Chimica%20-%20Triennale","callback_data":"06122"}],[{"text":"Civile%20-%20Triennale","callback_data":"06121"}],[{"text":"Civile%20ambiente%20e%20territorio%20-%20Triennale","callback_data":"06125"}],[{"text":"Elettronica%20-%20Triennale","callback_data":"06124"}],[{"text":"Gestionale%20-%20Triennale","callback_data":"06126"}],[{"text":"Informatica%20-%20Triennale","callback_data":"06127"}],[{"text":"Meccanica%20-%20Triennale","callback_data":"06123"}],[{"text":"Edile-architettura%20-%20Quinquennale","callback_data":"06601"}]';
$orarimenu = '[{"text":"Invia%20PDF","callback_data":"oraripdf"}],[{"text":"Invia%20per%20materia","callback_data":"orarimateria"}],[{"text":"Invia%20per%20anno","callback_data":"orarianno"}],[{"text":"Importare%20in%20Calendar","callback_data":"importgooglecalendar"}]';
$profile = '[{"text":"Modifica%20Materia","callback_data":"modsubject"}],[{"text":"Cancella%20Profilo","callback_data":"delprofile"}]';
$skipmenu = '[{"text":"Salta%20questo%20passaggio","callback_data":"skip"}]';
$eliminafeeback = '[{"text":"Elimina%20feeback","callback_data":"deletefeed"}]';
$utilitymenu = '[{"text":"Visualizza%20Webcam","callback_data":"viewwebcam"},{"text":"Aule%20libere","callback_data":"aulelibere"}],[{"text":"Cerca%20in%20Biblioteca","callback_data":"biblio"},{"text":"Menu%20Mensa","callback_data":"menumensa"}]';
$menuwebcam = '[{"text":"Piazza%20del%20Rettorato","callback_data":"webcamrettorato"}],[{"text":"Piazza%20del%20Sapere","callback_data":"webcamsapere"}],[{"text":"Veduta%20verso%20Ovest","callback_data":"webcamovest"}]';
$info = '[{"text":"Github","url":"https://github.com/gstru/ing-unisa-bot/"}]';
$exit = '[{"text":"","callback_data":""}]';



/* ----------------------
Query Data
---------------------- */
// State Config
if($querydata == "06229"||$querydata == "06228"||$querydata == "06222"||$querydata == "06221"||$querydata == "06224"||$querydata == "06226"||$querydata == "06227"||$querydata == "06223"||$querydata == "06225"||$querydata == "06122"||$querydata == "06121"||$querydata == "06125"||$querydata == "06124"||$querydata == "06126"||$querydata == "06127"||$querydata == "06123"||$querydata == "06601"){
mysql_query("UPDATE `Utenti` SET `State`='config5',`Corso`='$querydata',`Log`='$date $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo");
break;
};


// Mod Subject
if($querydata == "modsubject"){
mysql_query("UPDATE `Utenti` SET `State`='modsubject',`Log`='$date $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Ok, quale materia stai studiando?</b>\n<i>Inserisci un insegnamento</i>");
break;
};



// Del Profile
if($querydata == "delprofile"){
mysql_query("DELETE FROM `Utenti` WHERE `ChatID`='$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Profilo cancellato!</b>");
break;
};



// Orari PDF
if($querydata == "oraripdf"){
  $search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
  $Row = mysql_fetch_assoc($search);
  $id = $Row["ChatID"];
  $corso = $Row["Corso"];
  if(isset($id)){
    if(isset($corso)){
      $search = mysql_query("SELECT * FROM `Corsi` WHERE `Corso` LIKE '$corso'");
      $Row = mysql_fetch_assoc($search);
      $NomeDipartimento = $Row["NomeDipartimento"];
      $CodeLink = $Row["CodeLink"];
      $link = $Row["Link"];
      $CorsoLink = $Row["CorsoLink"];
      editMessage($queryUserID,$querymsid,"true","Ok, provo a recuperare il calendario in pdf");
      $easycourse = "https://easycourse.unisa.it/EasyCourse/Orario/Dipartimento_di_Ingegneria_$NomeDipartimento/2021-2022/$CodeLink/Curricula/".$link.$CorsoLink.$corso.".pdf?$timestamp";
      $handle = curl_init($easycourse);
      curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
      $response = curl_exec($handle);
      $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
      if($httpCode !== 404 AND $httpCode !== 500 ) {
        sendDocument($queryUserID,$easycourse);
      }else{
        editMessage($queryUserID,$querymsid,"true","Niente, il link in questo momento non funziona");
      }
    };
  };
  break;
};

// Import Google Calendar
if($querydata == "importgooglecalendar"){
$url = "https://telegra.ph/Come-importare-il-calendario-EasyCourse-in-Google-Calendar-con-i-Fogli-di-Google-09-17";
editMessage($queryUserID,$querymsid,"false",$url);
break;
};

// View Webcam
if($querydata == "viewwebcam"){
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Quale webcam vuoi visionare?</b>",$menuwebcam,'inline');
break;
};



// Webcam Veduta verso Ovest
if($querydata == "webcamovest"){
editMessage($queryUserID,$querymsid,"true","<b>Ti invio l'ultima immagine da Veduta verso Ovest</b>");
sendPhoto($queryUserID,"https://web.unisa.it/uploads/stazionemeteo/webcam/stecca9.jpg?$timestamp");
break;
};



// Webcam Piazza Rettorato
if($querydata == "webcamrettorato"){
editMessage($queryUserID,$querymsid,"true","<b>Ti invio l'ultima immagine da Piazza del Rettorato</b>");
sendPhoto($queryUserID,"http://www.campanialive.it/webcam/unisa/piazzacampus.jpg?$timestamp");
break;
};



// Webcam Piazza del Sapere
if($querydata == "webcamsapere"){
editMessage($queryUserID,$querymsid,"true","<b>Ti invio l'ultima immagine da Piazza del Sapere</b>");
sendPhoto($queryUserID,"https://web.unisa.it/uploads/stazionemeteo/webcam/piazzaDelSapere.jpg?$timestamp");
break;
};



// Room Finder
if($querydata == "aulelibere"){
  editMessage($queryUserID,$querymsid,"true","<b>Ok cerco le aule libere attendi...</b>");
  $buildings = ["FSTEC-05-06","FINV-09C","FINV-07E"];
  $n_buildings = count($buildings);
  for($i=0; $i<$n_buildings; $i++){
    $agenda_unisa = "https://www.unisa.it/proxy-test/easycourse/AgendaStudenti/rooms_call_new.php?sede=$buildings[$i]&date=$day_0";
    $newupdates = file_get_contents($agenda_unisa);
    $newupdates = json_decode($newupdates,TRUE);
    $edificio = $newupdates["area_rooms"]["$buildings[$i]"];
    $n_events = $newupdates["n_events"];
    $events = $newupdates["events"];
    if($n_events > 0){
      for($j=0; $j<$n_events; $j++){
        $CodiceAula = $events["$j"]["CodiceAula"];
        $CodiciAule[] = $CodiceAula;
      }
      $n_CodiciAule = count($CodiciAule);
      $rooms = array();
      foreach($edificio as $room => $value) {
        if(!in_array($room, $rooms))
        {
          array_push($rooms,$room);
        }
      }
      $n_rooms = count($rooms);
      for($k=0; $k<$n_rooms; $k++){
        $room_code = $edificio["$rooms[$k]"]["room_code"];
        $room_name = $edificio["$rooms[$k]"]["room_name"];
        $area_code = $edificio["$rooms[$k]"]["area_code"];
        $area_name = $edificio["$rooms[$k]"]["area"];
        if(in_array($room_code, $CodiciAule)){
          for($g=0; $g<$n_CodiciAule; $g++){
            $NomeAula = $events["$g"]["NomeAula"];
            $CodAula = $events["$g"]["CodiceAula"];
            $NomeSede = $events["$g"]["NomeSede"];
            $AppStart = $events["$g"]["from"];
            $AppStart = date('H:i', strtotime($AppStart));
            $AppEnd = $events["$g"]["to"];
            $AppEnd = date('H:i', strtotime($AppEnd));
            $Slot = "&#x26D4; <b>è occupata dalle $AppStart alle $AppEnd</b> &#x26D4;";
            if($NomeAula !== NULL){
              $item["NomeAula"] = $NomeAula;
              $item["NomeSede"] = $NomeSede;
              $item["CodiceAula"] = $CodAula;
              $item["Slot"] = $Slot;
              $classi[] = $item;
            };
          }}else{
            $item["NomeAula"] = "$room_name";
            $item["NomeSede"] = "$area_name";
            $item["CodiceAula"] = "$room_code";
            $item["Slot"] = "&#x2705; <b>è libera per tutta la giornata</b> &#x2705;";
            $classi[] = $item;
          }
        }
      };
    };
    editMessage($queryUserID,$querymsid,"true","<b>Ci sono quasi...</b>");
    $classi = array_values(array_unique($classi, SORT_REGULAR));
    // $classi = json_encode($classi);
    foreach ($classi as $key1 => $value1) {
      foreach ($value1 as $key2) {
        $items[] = $key2;
      }
      sendMessage($queryUserID,"true", str_replace(array('[',']','{','}','"',',','    '), '', json_encode($items, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
      unset($items);
    }
    // editMessage($queryUserID,"true", str_replace(array('[',']','{','}','"',',','    '), '', json_encode($classi, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
    break;
  };



// Biblio
if($querydata == "biblio"){
  $search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
  $Row = mysql_fetch_assoc($search);
  $id = $Row["ChatID"];
  $state = $Row["State"];
  if(isset($id)){
    mysql_query("UPDATE `Utenti` SET `State`='biblio',`Log`='$date $time' WHERE `ChatID` LIKE '$queryUserID'");
    editMessage($queryUserID,$querymsid,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
  }else{
    mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Materia`, `Log`) VALUES ('$queryUserID','$name','$username','biblio',NULL,NULL,'$date $time')");
    editMessage($queryUserID,$querymsid,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
  };
  break;
};



// Menu Mensa
if($querydata == "menumensa"){
  mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
  $increment = 0;
  $search = mysql_query("SELECT * FROM `Festivi`");
  while($Row = mysql_fetch_assoc($search)){
    $festivo = $Row["Festivo"];
    if($_day == "$festivo"){
      $increment++;
    }};
    if($increment == "0"){
      if($day !== "sab" && $day !== "dom"){
        if($time >= "00:01" && $time <= "10:00"){
          editMessage($queryUserID,$querymsid,"true","Torna più tardi, aggiorno questa funzione alle 10:00");
        }else if($time >= "10:00" && $time <= "21:00"){
          editMessage($queryUserID,$querymsid,"true","<b>Ok, provo a recuperare il menu della mensa di oggi, attendi...</b>");
          $adisu = "https://www.adisurcampania.it/archivio2_aree-tematiche_0_8.html.$timestamp";
          $handle = curl_init($adisu);
          curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
          $response = curl_exec($handle);
          $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
          if($httpCode == 404 || $httpCode == 500 ) {
            editMessage($queryUserID,$querymsid,"true","<b>Sembra che il sito Adisu non sia raggiungibile :(</b>");
            curl_close($handle);
          }else{
            try {
              curl_close($handle);
              $html = new DOMDocument();
              $html->loadHtml($response);
              $xpath = new DomXPath($html);
              $nodeList_1 = $xpath->query('//*[@id="ist_2_8"]/div/div[2]/p[84]/a');
              $nodeList_2 = $xpath->query('//*[@id="ist_2_8"]/div/div[2]/p[85]/a');
              $nodeList_3 = $xpath->query('//*[@id="ist_2_8"]/div/div[2]/p[86]/a');
              foreach($nodeList_1 as $node_1) {
                $menuUrl[] = $node_1->getAttribute('href');
              }
              foreach($nodeList_2 as $node_2) {
                $menuUrl[] = $node_2->getAttribute('href');
              }
              foreach($nodeList_3 as $node_3) {
                $menuUrl[] = $node_3->getAttribute('href');
              }
              editMessage($queryUserID,$querymsid,"true","<b>Ok, ci sono menu in arrivo...</b>");
              sendDocument($queryUserID,$menuUrl[0]);
              sendDocument($queryUserID,$menuUrl[1]);
              sendDocument($queryUserID,$menuUrl[2]);
            } catch (\Exception $e) {
              editMessage($queryUserID,$querymsid,"true","<b>Niente da fare, non sono riuscito a recuperare il menu :(</b>");
            }}
          }else{
            editMessage($queryUserID,$querymsid,"true","<b>Sono le $time, torna domani per il nuovo menu</b>");
          }}else{
            editMessage($queryUserID,$querymsid,"true","<b>Il menu della mensa non verrà pubblicato oggi</b>");
          }}else{
            editMessage($queryUserID,$querymsid,"true","<b>Il menu della mensa non verrà pubblicato oggi perchè festivo</b>");
          };
          break;
        };



/* ----------------------
Switch
---------------------- */
switch ($text){



// Start
case '/start':
sendMessage($chatID,"true","Ciao <b>$name!</b>\nBenvenuto su <b>IngUnisaBot</b>.\n\nPer utilizzare alcune funzioni devi impostare un tuo profilo.\nPer farlo utilizza il comando /config.\nSe non sei interessato ignora questo step o configuralo più tardi.\n\nDigita /comandi per visualizzare i comandi del bot o seleziona una funzione dal menu");
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','0',NULL,NULL,'$date $time')");
break;



// Commands
case '/comandi':
sendMessage($chatID,"true","/start - avvia bot\n/config - configura il tuo profilo\n/profilo - informazioni del tuo profilo\n/orari - cerca gli orari delle lezioni\n/esami - trova le date dei prossimi appelli\n/cercadocente - ricerca dei docenti\n/trovacompagno - cerca un compagno di studio\n/utility - funzioni utili\n/info - informazioni sul bot\n/canc - interrompe l'operazione corrente\n\n<b>Seleziona una funzione per iniziare</b>");
break;



// Modul Backend Message
case '/code':
mysql_query("UPDATE `Utenti` SET `State`='code',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Sei nel backend messaggi. Puoi mandare un messaggio a tutti i membri");
break;



// Config
case '/config':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='config',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','config',NULL,NULL,'$date $time')");
};
sendMessage($chatID,"true","<b>Qual è il tuo corso di studi?</b>",$corsi_menu,'inline');
mysql_query("UPDATE `Utenti` SET `State`='config',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
break;



// Info
case '/info':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Qui trovi il repository per qualsiasi info o segnalazione",$info,'inline');
break;



// Profile
case '/profilo':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$corso = $Row["Corso"];
$materia = $Row["Materia"];
if($materia === NULL){
}else{
  $search = mysql_query("SELECT * FROM `Esami` WHERE `Corso` LIKE '$corso' AND `Code` LIKE '$materia'");
  $Row = mysql_fetch_assoc($search);
  $nomemateria = $Row["Esame"];
  if(!isset($nomemateria)){
    $nomemateria = "impossibile trovare materia";
  }};
  if(isset($corso)){
    sendMessage($chatID,"true","<b>Corso di studi:</b> $corso\n<b>Materia:</b> $nomemateria",$profile,'inline');
  }else{
    sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
  }
break;



// Esc
case 'Esci':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0', `Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;



// Canc
case '/canc':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;



// Lessons
case '/orari':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
if(isset($id)){
  if(isset($corso)){
    sendMessage($chatID,"true","<b>Ok, seleziona una funzione</b>",$orarimenu,'inline');
  }else{
    sendMessage($chatID,"true","<b>Non hai ancora impostato il tuo profilo o è incompleto</b>");
  }}else{
    sendMessage($chatID,"true","<b>Non hai ancora impostato il tuo profilo o è incompleto</b>");
  };
break;



// Exams
case '/esami':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
$materia = $Row["Materia"];
if(isset($id)){
  if(isset($corso) && isset($materia)){
    $search = mysql_query("SELECT * FROM `Corsi` WHERE `Corso` LIKE '$corso'");
    $Row = mysql_fetch_assoc($search);
    $code = $Row["Code"];
    $dipartimento = $Row["Dipartimento"];
    $html = file_get_html("https://esse3web.unisa.it/ListaAppelliOfferta.do;jsessionid=?TIPO_FORM=1&fac_id=$dipartimento&cds_id=$code&ad_id=&docente_id=&data=&btnSubmit=Avvia+Ricerca");
    foreach($html->find('th[colspan]') as $th) {
      $th->outertext = '';
    };
    $html->load($html->save());
    foreach($html->find('form[method]') as $form) {
      $form->outertext = '';
    };
    $html->load($html->save());
    foreach($html->find('td[class="tplTitolo"]') as $tpl) {
      $tpl->outertext = '';
    };
    $html->load($html->save());
    $search = mysql_query("SELECT * FROM `Esami` WHERE `Corso` LIKE '$corso' AND `Code` LIKE '$materia'");
    $Row = mysql_fetch_assoc($search);
    $nomemateria = $Row["Esame"];
    $nomemateria = strtoupper($nomemateria);
    $increment = 0;
    foreach($html->find('tr') as $row) {
      $attivita_didattica = $row->find('td',0)->plaintext;
      $attivita_didattica = explode("] ",$attivita_didattica);
      $attivita_didattica = $attivita_didattica[1];
      $attivita_didattica = html_entity_decode($attivita_didattica);
      $periodo_iscrizioni = $row->find('td',1)->plaintext;
      $periodo_iscrizioni = html_entity_decode($periodo_iscrizioni);
      $intervallo_iscrizione = explode("-",$periodo_iscrizioni);
      $fine_iscrizione = $intervallo_iscrizione[1];
      $fine_iscrizione = str_replace("/","-",$fine_iscrizione);
      $fine_iscrizione = strtotime($fine_iscrizione);
      $data_e_turno = $row->find('td',2)->plaintext;
      $data_e_turno = html_entity_decode($data_e_turno);
      $data_e_turno = str_replace(" ", "", $data_e_turno);
      $vuoto_1 = $row->find('td',3)->plaintext;
      $docente = $row->find('td',4)->plaintext;
      if(empty($docente)){
        $docente = "non assegnato";
      }
      $iscritti = $row->find('td',5)->plaintext;
      if(!is_numeric($iscritti)){
        $iscritti = $row->find('td',6)->plaintext;
        if(!is_numeric($iscritti)){
        $iscritti = $row->find('td',7)->plaintext;
          if(!is_numeric($iscritti)){
          $iscritti = $row->find('td',8)->plaintext;
            if(!is_numeric($iscritti)){
            $iscritti = $row->find('td',9)->plaintext;
              if(!is_numeric($iscritti)){
              $iscritti = $row->find('td',10)->plaintext;
      }}}}}
      $val = similar_text($attivita_didattica, $nomemateria, $percent);
      if($percent > 95){
        if($day_1 <= $fine_iscrizione){
        sendMessage($chatID,"true", "Attività Didattiva: $attivita_didattica\nPeriodo Iscrizioni: $periodo_iscrizioni\nData e turno: $data_e_turno\nDocente: $docente\nIscritti: $iscritti");
        $increment ++;
      }}};
      if($increment != '0'){
      }else{
        sendMessage($chatID,"true","Mi dispiace non ho trovato date d'esame per $nomemateria");
      }
    }else{
      sendMessage($chatID,"true","Non hai ancora impostato il tuo corso e la materia di studio");
    }}else{
      sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
    };
break;



// Friend Finder
case '/trovacompagno':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$username = $Row["Username"];
$corso = $Row["Corso"];
$materia = $Row["Materia"];
if(!empty($username)){
  if(isset($corso)){
    if(!empty($materia)){
      $search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` != $id AND `Corso` LIKE '$corso' AND `Materia` LIKE '$materia'");
      while($Row = mysql_fetch_assoc($search)){
        $friend = $Row["Username"];
        if(!empty($friend)){
          $friends[] = "@$friend";
        }};
        if(isset($friends)){
          $fcount = count($friends);
          $friends = implode("\n\n", $friends);
          $search = mysql_query("SELECT * FROM `Esami` WHERE `Corso` LIKE '$corso' AND `Code` LIKE '$materia'");
          $Row = mysql_fetch_assoc($search);
          $nomemateria = $Row["Esame"];
          sendMessage($chatID,"true","Risultato della ricerca: $fcount\nEcco l'elenco di chi sta studiando $nomemateria:\n\n$friends");
        }else{
          $search = mysql_query("SELECT * FROM `Esami` WHERE `Corso` LIKE '$corso' AND `Code` LIKE '$materia'");
          $Row = mysql_fetch_assoc($search);
          $nomemateria = $Row["Esame"];
          sendMessage($chatID,"true","Sembra che non ci sia nessuno che stia studiando $nomemateria");
        }}else{
          sendMessage($chatID,"true","Ops, non hai impostato quale materia stai studiando");
        }}else{
          sendMessage($chatID,"true","Ops, non hai impostato il tuo corso di studi");
        }}else{
          sendMessage($chatID,"true","Ops, per utilizzare questa funzione devi impostare un tuo username su telegram");
        };
break;



// Teacher Finder
case '/cercadocente':
mysql_query("UPDATE `Utenti` SET `State`='docente',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok, scrivi Nome e Cognome del docente che vuoi cercare</b>");
break;



// Biblio
case '/biblio':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='biblio',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
  sendMessage($chatID,"true","<b>Ok, ora puoi cercare nel Catalogo di ateneo</b>");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','biblio',NULL,NULL,'$date $time')");
  sendMessage($chatID,"true","<b>Ok, ora puoi cercare nel Catalogo di ateneo</b>");
};
break;



// Utility
case '/utility':
sendMessage($chatID,"true","<b>Utility</b>",$utilitymenu,'inline');
break;



/* ----------------------
Default
---------------------- */
default:



// Database Variables
mysql_query("SET NAMES 'utf8'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
$corso = $Row["Corso"];
$ingegneria = $Row["Ingegneria"];



if(isset($id)){



// State Teacher
  if($state == 'docente'){
  $increment = 0;
  $_text = explode(" ",$text);
  $nome = $_text[0];
  $cognome = $_text[1];
  $search = mysql_query("SELECT * FROM `Docenti` WHERE (`Docente` LIKE '%$nome%' AND `Docente` LIKE '%$cognome%')");
  while($Row = mysql_fetch_assoc($search))
  {
    $docente = $Row["Docente"];
    if(isset($docente)){
      $increment++;
    }
    $link = $Row["Link"];
    $html = file_get_html("$link");
    foreach($html->find('td[class="icon"]') as $icon) {
    $icon->outertext = '';
  };
  $html->load($html->save());
  foreach($html->find('td>a[class="btn btn-mail visible-xs visible-sm"]') as $mail) {
    $mail->outertext = '';
  };
  $html->load($html->save());
  foreach($html->find('td') as $item){
    $informazione = $item->plaintext;
    $informazioni[] = $informazione;
  };
  if(!empty($informazioni)){
    $informazioni = implode("\n", $informazioni);
    $informazioni = html_entity_decode($informazioni);
    sendMessage($chatID,"true", "$informazioni");
    unset($informazioni);
  }}
  if($increment == '0'){
    sendMessage($chatID,"true","Non ho trovato nessun docente con questo nome");
    unset($increment);
}}



// State Backend Message
else if($state == "code"){
  $search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID`");
  while($Row = mysql_fetch_assoc($search))
  {
    $id = $Row["ChatID"];
    if($id != $chatID){
      sendMessage($id,"true","$text");
    }};
    sendMessage($chatID,"true","Invio Messaggio Completato");
    mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
  }



// State Biblio
else if($state == 'biblio'){
  sendMessage($chatID,"true","<b>Ok, dammi un secondo</b>  &#128269;");
  $_text = str_replace(' ', '%20', $text);
  $_url = "https://catalogo.share-cat.unina.it/sharecat/search?&s=50&o=score&h=adv&q=any_bc:$_text&f=library:%22UNISA%22&&dls=true&v=l";
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_URL, $_url);
  curl_setopt($curl, CURLOPT_REFERER, $_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  $str = curl_exec($curl);
  curl_close($curl);
  $html = new simple_html_dom();
  $html->load($str);
  foreach($html->find('div[class="item clear"]>table>tbody>tr>td>span>a[title="Visualizza persona"]') as $_icon) {
    $_icon->outertext = '';
  };
  $html->load($html->save());
  foreach($html->find('div[class="item clear"]') as $risultato) {
    $item['<i>Titolo</i>'] = $risultato->find('span>a', 0)->plaintext;
    $item['<i>Link</i>'] = $risultato->find('td[class="alignLeft"]>a', 0)->href;
    $ricerca_biblio[] = $item;
  };
  $html->clear();
  unset($html);
  if(empty($ricerca_biblio)){
    sendMessage($chatID,"true","Mi dispiace, non ho trovato risultati alla tua ricerca");
    unset($ricerca_biblio);
    break;
  }else{
    sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',"    ,","        "), '', json_encode($ricerca_biblio, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
    unset($ricerca_biblio);
  };
  sendMessage($chatID,"true","<b>Ricerca completata!</b>");
}



// State Mod Subject
else if($state == 'modsubject'){
  mysql_query("UPDATE `Utenti` SET `State`='modsubject1',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
  $search = mysql_query("SELECT * FROM `Esami` WHERE `Esame` LIKE '%$text%' AND `Corso` LIKE '$corso'");
  while($Row = mysql_fetch_assoc($search)){
    $esame = $Row["Esame"];
    $code = $Row["Code"];
    $subjects[] = array($esame,$code);
  };
  if(isset($subjects)){
    sendMessage($chatID,"true","Scrivi il codice della materia che stai studiando");
    sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',',','        '), '', json_encode($subjects, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
  }else{
    mysql_query("UPDATE `Utenti` SET `State`='modsubject',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","Mi dispiace, non ho trovato materie con questo nome inerente al tuo corso, riprova");
  }}



// State Mod Subject 1
else if($state == 'modsubject1'){
mysql_query("UPDATE `Utenti` SET `State`='0',`Materia`='$text',`Log`='$date $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Perfetto, ho impostato la tua materia di studio</b>");
}



// Init State
else if($state == '0'){
  sendMessage($chatID,"true","Seleziona una funzione per iniziare");
}}



// Error
else{
  sendMessage($chatID,"true","Ops, Qualcosa è andato storto\nRiparti con /start");
};
break;
};



?>
