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
$date = strftime("%a %d %b %Y");
$dating = date("d/m/Y");
$month = date("m");
$time = date("H:i");
$day = strftime("%a");
$timestamp = time();



/* ----------------------
Menus
---------------------- */
$start ='[{"text":"Iniziamo","callback_data":"start"}]';
$profile = '[{"text":"Modifica%20Cartella","callback_data":"modfolder"}],[{"text":"Modifica%20Materia","callback_data":"modsubject"}],[{"text":"Cancella%20Profilo","callback_data":"delprofile"}]';
$skipmenu = '[{"text":"Salta%20questo%20passaggio","callback_data":"skip"}]';
$eliminafeeback = '[{"text":"Elimina%20feeback","callback_data":"deletefeed"}]';
$utilitymenu = '[{"text":"Visualizza%20Webcam","callback_data":"viewwebcam"},{"text":"Aulario","callback_data":"roomfinder"}],[{"text":"Cerca%20in%20Biblioteca","callback_data":"biblio"},{"text":"Menu%20Mensa","callback_data":"menumensa"}]';
$menuwebcam = '[{"text":"Piazza%20del%20Rettorato","callback_data":"webcamrettorato"}],[{"text":"Piazza%20del%20Sapere","callback_data":"webcamsapere"}],[{"text":"Veduta%20verso%20Ovest","callback_data":"webcamovest"}]';
$info = '[{"text":"Termini%20e%20Condizioni","url":"https://github.com/gistru/ing-unisa-bot/blob/master/terms.md"}],[{"text":"Utenti%20Attivi","callback_data":"activeusers"}],[{"text":"Invia%20Feedback","callback_data":"sendfeedback"}]';
$exit = '[{"text":"","callback_data":""}]';



/* ----------------------
Query Data
---------------------- */
// Mod Folder
if($querydata == "modfolder"){
mysql_query("UPDATE `Utenti` SET `State`='modfolder',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Ok, scrivi qui il nome della cartella seguito dal TOKEN</b>\n\n<i>https://drive.google.com/drive/folders/TOKEN</i>\n\n<i>Esempio</i>\nInformatica, 1jq5wZm47zukLO0pGeswsJ1do2fb2dVzZ");
break;
};



// Mod Subject
if($querydata == "modsubject"){
mysql_query("UPDATE `Utenti` SET `State`='modsubject',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Ok, quale materia stai studiando?</b>\n<i>Inserisci un insegnamento</i>");
break;
};



// Skip Folder
if($querydata == "skip"){
mysql_query("UPDATE `Utenti` SET `State`='config4',`Cartella`='',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Hai saltato questo passaggio</b>");
sendMessage($queryUserID,$querymsid,"<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n");
break;
};



// Del Feedback
if($querydata == "deletefeed"){
mysql_query("DELETE FROM `Feedback` WHERE `Status`= -1");
unset($randomString);
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Hai eliminato il tuo Feedback!</b>");
break;
};



// Del Profile
if($querydata == "delprofile"){
mysql_query("DELETE FROM `Utenti` WHERE `ChatID`='$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Profilo cancellato!</b>");
break;
};



// View Webcam
if($querydata == "viewwebcam"){
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
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
if($querydata == "roomfinder"){
  $search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
  $Row = mysql_fetch_assoc($search);
  $id = $Row["ChatID"];
  $state = $Row["State"];
  if(isset($id)){
    mysql_query("UPDATE `Utenti` SET `State`='aulario',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
    editMessage($queryUserID,$querymsid,"true","<b>Ok, ora puoi cercare la tua aula</b>");
  }else{
    mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$queryUserID','$name','$username','aululario',NULL,'',NULL,'il $date alle $time')");
    editMessage($queryUserID,$querymsid,"true","<b>Ok, ora puoi cercare la tua aula</b>");
  };
  break;
};



// Biblio
if($querydata == "biblio"){
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='biblio',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
  editMessage($queryUserID,$querymsid,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$queryUserID','$name','$username','biblio',NULL,'',NULL,'il $date alle $time')");
  editMessage($queryUserID,$querymsid,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
};
break;
};



// Menu Mensa
if($querydata == "menumensa"){
  mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
  if($day !== "sab" && $day !== "dom"){
    if($time >= "00:01" && $time <= "10:00"){
      editMessage($queryUserID,$querymsid,"true","Torna più tardi, aggiorno questa funzione alle 10:00");
    }else if($time >= "10:00" && $time <= "15:00"){
      editMessage($queryUserID,$querymsid,"true","<b>Ok, ti invio il menu della mensa di oggi, attendi...</b>");
      $xml = simplexml_load_file('http://ammensa-unisa.appspot.com');
      $menuUrl = $xml->menu->menuUrl;
      sendDocument($queryUserID,"$menuUrl");
    }else{
      editMessage($queryUserID,$querymsid,"true","<b>Sono le $time, torna domani per il nuovo menu</b>");
    }}else{
      editMessage($queryUserID,$querymsid,"true","<b>Il menu della mensa non verrà pubblicato oggi</b>");
    };
    break;
  };



// Active Users
if($querydata == "activeusers"){
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
$users = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` ");
$count = mysql_num_rows($users);
editMessage($queryUserID,$querymsid,"true","<b>Utenti attivi: </b> $count");
break;
};



// Send Feedback
if($querydata == "sendfeedback"){
mysql_query("UPDATE `Utenti` SET `State`='feedback',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Modulo Feedback</b>\n\n<i>Riporta un problema o suggerisci una funzione</i>");
break;
};



/* ----------------------
Switch
---------------------- */
switch ($text){



// Start
case '/start':
sendMessage($chatID,"true","Ciao <b>$name!</b>\nBenvenuto su <b>IngUnisaBot</b>.\n\nPer utilizzare alcune funzioni devi impostare un tuo profilo.\nPer farlo utilizza il comando /config.\nSe non sei interessato ignora questo step o configuralo più tardi.\n\nDigita /comandi per visualizzare i comandi del bot o seleziona una funzione dal menu");
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','0',NULL,'',NULL,'il $date alle $time')");
break;



// Commands
case '/comandi':
sendMessage($chatID,"true","/start - avvia bot\n/config - configura il tuo profilo\n/profilo - informazioni del tuo profilo\n/orari - cerca gli orari delle lezioni\n/esami - trova le date dei prossimi appelli\n/studentingegneria - cerca su StudentIngegneria\n/r0x - cerca su r0x\n/cartellecondivise - cerco cartelle condivise\n/cercadocente - ricerca dei docenti\n/trovacompagno - cerca un compagno di studio\n/utility - funzioni utili\n/info - informazioni sul bot\n/canc - interrompe l'operazione corrente\n\n<b>Seleziona una funzione per iniziare</b>");
break;



// Modul Backend Feedback
case 'password':
mysql_query("UPDATE `Utenti` SET `State`='password',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Sei nel modulo segreto feedback. Premi un tasto");
break;



// Modul Backend Message
case 'password':
mysql_query("UPDATE `Utenti` SET `State`='password',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Sei nel modulo segreto contatti. Puoi mandare un messaggio a tutti i membri");
break;



// Config
case '/config':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='config',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','config',NULL,'',NULL,'il $date alle $time')");
};
sendMessage($chatID,"true","Quando vuoi iniziamo",$start,'real');
break;



// Info
case '/info':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Per info, termini e condizioni di utilizzo clicca qui",$info,'inline');
break;



// Profile
case '/profilo':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$corso = $Row["Corso"];
$cartella = $Row["Cartella"];
$materia = $Row["Materia"];
if($materia === NULL){
}else{
  $search = mysql_query("SELECT * FROM `Esami` WHERE `Corso` LIKE '$corso' AND `Code` LIKE '$materia'");
  $Row = mysql_fetch_assoc($search);
  $nomemateria = $Row["Esame"];
  if(!isset($nomemateria)){
    $nomemateria="impossibile trovare materia";
  }};
  if(isset($corso)){
    sendMessage($chatID,"true","<b>Corso di studi:</b> $corso\n<b>Cartella:</b> $cartella\n<b>Materia:</b> $nomemateria",$profile,'inline');
  }else{
    sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
  }
break;



// Esc
case 'Esci':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0', `Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;



// Canc
case '/canc':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;



// Lessons
case '/orari':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
if(isset($id)){
  if(isset($corso)){
    $search = mysql_query("SELECT * FROM `Corsi` WHERE `Corso` LIKE '$corso'");
    $Row = mysql_fetch_assoc($search);
    $link = $Row["Link"];
    if($corso == "06229"){
    $corso = "DH";
    };
    sendMessage($chatID,"true","<b>Ok, ti invio il file, attendi...</b>");
    sendDocument($chatID,"https://easycourse.unisa.it/EasyCourse/Orario/Facolta_di_Ingegneria/2019-2020/449/Curricula/$link$corso.pdf?$timestamp");
  }else{
    sendMessage($chatID,"true","<b>Non hai ancora impostato il tuo profilo o è incompleto</b>");
  }}else{
    sendMessage($chatID,"true","<b>Non hai ancora impostato il tuo profilo o è incompleto</b>");
  };
break;



// Exams
case '/esami':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
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
      $attività_didattica = $row->find('td',0)->plaintext;
      $attività_didattica = explode("] ",$attività_didattica);
      $attività_didattica = $attività_didattica[1];
      $periodo_iscrizioni = $row->find('td',1)->plaintext;
      $data_e_turno = $row->find('td',2)->plaintext;
      $vuoto_1 = $row->find('td',3)->plaintext;
      $docente = $row->find('td',4)->plaintext;
      if(empty($docente)){
        $docente = "non assegnato";
      }
      $vuoto_2 = $row->find('td',5)->plaintext;
      $iscritti = $row->find('td',6)->plaintext;
      $val = similar_text($attività_didattica, $nomemateria, $percent);
      if($percent > 99){
        sendMessage($chatID,"true","Attività Didattiva: $attività_didattica\nPeriodo Iscrizioni: $periodo_iscrizioni\nData e turno: $data_e_turno\nDocente: $docente\nIscritti: $iscritti");
        $increment ++;
      }};
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



// R0X
case '/r0x':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='r0x',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
  sendMessage($chatID,"true","<b>Ok, ora puoi cercare su r0x.it</b>");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','r0x',NULL,'',NULL,'il $date alle $time')");
  sendMessage($chatID,"true","<b>Ok, ora puoi cercare su r0x.it</b>");
};
break;



// Sharing Folders
case '/cartellecondivise':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
if(isset($id)){
  if(isset($corso)){
    $search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` != $id AND `Corso` LIKE '$corso'");
    while($Row = mysql_fetch_assoc($search)){
      $cartella = $Row["Cartella"];
      if(!empty($cartella)){
        $cartelle[] = $cartella;
      }};
      if(isset($cartelle)){
        $cartelle = implode("\n\n", $cartelle);
        sendMessage($chatID,"true",$cartelle);
      }else{
        sendMessage($chatID,"true","Non ho trovato cartelle condivise per il tuo corso di studi");
      }}else{
        sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
      }}else{
        sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
      };
break;



// StudentIngegneria
case '/studentingegneria':
mysql_query("UPDATE `Utenti` SET `State`='SI',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok, ora puoi cercare sul sito StudentIngengeria</b>");
break;



// Friend Finder
case '/trovacompagno':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
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
          $friends[] = $friend;
        }};
        if(isset($friends)){
          $friends = implode("\n\n", $friends);
          sendMessage($chatID,"true",$friends);
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



// Mensa
case '/mensa':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
if($day !== "sab" && $day !== "dom"){
  if($time >= "00:01" && $time <= "10:00"){
    sendMessage($chatID,"true","Torna più tardi, aggiorno questa funzione alle 10:00");
  }else if($time >= "10:00" && $time <= "15:00"){
    sendMessage($chatID,"true","<b>Ok, ti invio il menu della mensa di oggi</b>");
    $xml = simplexml_load_file('http://ammensa-unisa.appspot.com');
    $menuUrl = $xml->menu->menuUrl;
    sendDocument($chatID,"$menuUrl");
  }else{
    sendMessage($chatID,"true","<b>Sono le $time, torna domani per il nuovo menu</b>");
  }}else{
    sendMessage($chatID,"true","<b>Il menu della mensa non verrà pubblicato oggi</b>");
  };
break;



// Teacher Finder
case '/cercadocente':
mysql_query("UPDATE `Utenti` SET `State`='docente',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok, ora puoi cercare un docente</b>");
break;



// Biblio
case '/biblio':
$search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
  mysql_query("UPDATE `Utenti` SET `State`='biblio',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
  sendMessage($chatID,"true","<b>Ok, ora puoi cercare nel Catalogo di ateneo</b>");
}else{
  mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','biblio',NULL,'',NULL,'il $date alle $time')");
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
// State Config
if($state == "config"){
mysql_query("UPDATE `Utenti` SET `State`='config1',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Qual è il tuo corso di studi?</b>\n\nDigital health and bioinformatic engineering - Magistrale [06229]\nFood engineering - Magistrale [06228]\nIngegneria chimica - Magistrale [06222]\nIngegneria civile - Magistrale [06221]\nIngegneria elettronica - Magistrale [06224]\nIngegneria gestionale - Magistrale [06226]\nIngegneria informatica - Magistrale [06227]\nIngegneria meccanica - Magistrale [06223]\nIngegneria per l'ambiente e il territorio - Magistrale [06225]\nIngegneria chimica - triennale [06122]\nIngegneria civile - triennale [06121]\nIngegneria civile per l'ambiente e il territorio - triennale [06125]\nIngegneria elettronica - triennale [06124]\nIngegneria gestionale - triennale [06126]\nIngegneria informatica - triennale [06127]\nIngegneria meccanica - triennale [06123]\nIngegneria edile-architettura - quinquennale [06601]\n\n<i>Inserisci il codice corrispondente posto tra parentesi quadre</i>",$start,'hide');
}



// State Config 1
else if($state == "config1"){
  if($text == "06229"||$text == "06228"|| $text == "06222"||$text == "06221"||$text == "06224"||$text == "06226"||$text == "06227"||$text == "06223"||$text == "06225"||$text == "06122"||$text == "06121"||$text == "06125"||$text == "06124"||$text == "06126"||$text == "06127"||$text == "06123"||$text == "06601"){
    mysql_query("UPDATE `Utenti` SET `State`='config2',`Corso`='$text',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","Con G Suite gli studenti Unisa hanno spazio illimitato in cloud\n\nTi piacerebbe condividere una cartella con altri studenti del tuo corso?\n\nCon la funziona /cartellecondivise in seguito posso individuare tutte le cartelle di altri studenti che hanno fatto lo stesso\n\n<b>Scrivi qui il nome della cartella seguito dal TOKEN</b>\n\n<i>https://drive.google.com/drive/folders/TOKEN</i>\n\n<i>Esempio</i>\nInformatica, 1jq5wZm47zukLO0pGeswsJ1do2fb2dVzZ");
    sendMessage($chatID,"true","Non ora?",$skipmenu,'inline');
  }else{
    sendMessage($chatID,"true","Non hai inserito un codice di corso valido\nPer favore reinseriscilo");
  }}



// State Config 2
else if($state == "config2"){
  $_text = explode(", ",$text);
  $nome_cartella = $_text[0];
  $nome_cartella = strtoupper($nome_cartella);
  $google_token = $_text[1];
  $_google_token = preg_replace('/[^A-Za-z0-9\-_]/', '', $google_token);
  $url = "https://drive.google.com/drive/folders/$_google_token";
  $url = str_replace(' ', '', $url);
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
  $response = curl_exec($handle);
  $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  if($httpCode == 404) {
    sendMessage($chatID,"true","Non hai inserito un link valido");
    curl_close($handle);
  }else{
    mysql_query("UPDATE `Utenti` SET `State`='0',`Cartella`='$nome_cartella, $url',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n");
    curl_close($handle);
  }
  curl_close($handle);
}



// State R0X
else if($state == "r0x"){
  sendMessage($chatID,"true","<b>Ok, dammi un secondo</b>  &#128269;");
  $i = 0;
  $tot_pages = 25;
  while ($i <= $tot_pages){
    $_text = str_replace(' ', '%20', $text);
    $url = "http://www.r0x.it/index.php?app=core&module=search&do=search&fromMainBar=1&search_term=$_text&st=$i";
    $html = file_get_html("$url");
    foreach($html->find('td>h4') as $risultato) {
      $item['Titolo'] = $risultato->find('a', 0)->plaintext;
      $item['Link'] = $risultato->find('a', 0)->href;
      $ricerca[] = $item;
    };
    if(empty($ricerca)){
      sendMessage($chatID,"true","Mi dispiace, non ho trovato risultati alla tua ricerca");
      unset($ricerca);
      break;
    }else{
      sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',',','        '), '', json_encode($ricerca, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
      $i = $i+25;
      unset($ricerca);
    }};
    sendMessage($chatID,"true","<b>Ricerca completata!</b>");
  }



// State Teacher
else if($state == 'docente'){
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



// State StudentIngegneria
else if($state == 'SI'){
  sendMessage($chatID,"true","<b>Ok, dammi un secondo</b>  &#128269;");
  $_text = str_replace(' ', '%20', $text);
  $html = file_get_html("http://www.studentingegneria.it/?s=$_text");
  foreach($html->find('h2') as $risultato) {
    $item['Titolo'] = $risultato->find('a', 0)->plaintext;
    $item['Link'] = $risultato->find('a', 0)->href;
    $ricerca[] = $item;
  };
  if(empty($ricerca)){
    sendMessage($chatID,"true","<b>Mi dispiace, non ho trovato risultati alla tua ricerca</b>");
    unset($ricerca);
    break;
  }else{
    sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',',','        '), '', json_encode($ricerca, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
    unset($ricerca);
  }
  sendMessage($chatID,"true","<b>Ricerca completata!</b>");
}



// State Backend Message
else if($state == "password"){
  $search = mysql_query("SELECT * FROM `Utenti` WHERE `ChatID`");
  while($Row = mysql_fetch_assoc($search))
  {
    $id = $Row["ChatID"];
    if($id != $chatID){
      sendMessage($id,"true","$text");
    }};
    sendMessage($chatID,"true","Invio Completato");
  }



// State Backend Feedback
else if($state == "password"){
  $search = mysql_query("SELECT * FROM `Feedback`");
  while($Row = mysql_fetch_assoc($search))
  {
    $feedback = $Row["Feedback"];
    $feedbacks[] = $feedback;
  };
  $feedbacks = implode("\n\n", $feedbacks);
  sendMessage($chatID,"true", "$feedbacks");
  sendMessage($chatID,"true","Ricerca Feedback Completato");
}



// State Mod Folder
else if($state == "modfolder"){
  $_text = explode(", ",$text);
  $nome_cartella = $_text[0];
  $nome_cartella = strtoupper($nome_cartella);
  $google_token = $_text[1];
  $_google_token = preg_replace('/[^A-Za-z0-9\-_]/', '', $google_token);
  $url = "https://drive.google.com/drive/folders/$_google_token";
  $url = str_replace(' ', '', $url);
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
  $response = curl_exec($handle);
  $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  if($httpCode == 404) {
    sendMessage($chatID,"true","Non hai inserito un token valido");
    curl_close($handle);
  }else{
    mysql_query("UPDATE `Utenti` SET `State`='config4',`Cartella`='$nome_cartella, $url',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n\nSe vuoi cancellare il profilo ti basterà utilizzare il comando /cancellaprofilo");
    curl_close($handle);
  }
  curl_close($handle);
}



// State Feedback
else if($state == 'feedback'){
  mysql_query("INSERT INTO `Feedback`(`Feedback`, `Captcha`, `Status`, `Log`) VALUES ('$text','0','-1','il $date alle $time')");
  $characters = '0123456789';
  $randomString = '';
  $lunghezza = 5;
  for ($i = 0; $i < $lunghezza; $i++) {
    $index = rand(0, strlen($characters) - 1);
    $randomString .= $characters[$index];
  }
  mysql_query("UPDATE `Utenti` SET `State`='fbv',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
  mysql_query("UPDATE `Feedback` SET `Captcha`='$randomString'  WHERE `Feedback` LIKE '$text'");
  sendMessage($chatID,"true","Per confermare l'invio del messaggio scrivi il seguente captcha altrimenti eliminalo:\n\n$randomString",$eliminafeeback,'inline');
}



// State Feedback Verify
else if($state == "fbv"){
  $search = mysql_query("SELECT * FROM `Feedback` WHERE `Captcha` LIKE '$text'");
  $Row = mysql_fetch_assoc($search);
  $captcha = $Row["Captcha"];
  if($text == "$captcha"){
    mysql_query("UPDATE `Feedback` SET `Status`='1'  WHERE `Captcha` LIKE '$text'");
    unset($randomString);
    mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","<b>Messaggio inviato correttamente!</b>");
  }else{
    mysql_query("DELETE FROM `Feedback` WHERE `Status`= -1 ");
    unset($randomString);
    mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","Errore nella sintassi del captcha. Il messaggio è stato eliminato");
  }}



// State Biblio
else if($state == 'biblio'){
  sendMessage($chatID,"true","<b>Ok, dammi un secondo</b>  &#128269;");
  $_text = str_replace(' ', '%20', $text);
  $html = file_get_html("http://ariel.unisa.it/primo_library/libweb/action/search.do?fn=search&ct=search&initialSearch=true&mode=Basic&tab=local&indx=1&dum=true&srt=rank&vid=39USA_V1&frbg=&vl%28freeText0%29=$_text&scp.scps=scope%3A%2839USA_ALMA%29%2Cscope%3A%2839USA_ALMA_MARC%29");
  foreach($html->find('span[class=EXLAvailabilityLibraryName]') as $library) {
    $library->plaintext;
    $libreria->outertext = $library;
  };
  $html->load($html->save());
  foreach($html->find('span[class=EXLAvailabilityCollectionName]') as $area) {
    $area->plaintext;
    $zona->outertext = $area;
  };
  $html->load($html->save());
  foreach($html->find('span[class=EXLAvailabilityCallNumber]') as $callnumber) {
    $callnumber->outertext = '';
  };
  $html->load($html->save());
  foreach($html->find('td[class=EXLSummary]') as $risultato) {
    $item['<i>Titolo</i>'] = $risultato->find('h2[class=EXLResultTitle]', 0)->plaintext;
    $item['<i>Autore</i>'] = $risultato->find('h3[class=EXLResultAuthor]', 0)->plaintext;
    $item['<i>Edizione</i>'] = $risultato->find('h3[class=EXLResultFourthLine]', 0)->plaintext;
    $item['<i>Stato</i>'] = $risultato->find('em[class=EXLResultStatusAvailable]', 0)->plaintext;
    $ricerca[] = $item;
  };
  sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',',','        ','      ','null','()','&nbsp;','\t'), '', json_encode($ricerca, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
  unset($ricerca);
  sendMessage($chatID,"true","<b>Ricerca completata!</b>");
}



// State Rooms
else if($state == 'aulario'){
  $search = mysql_query("SELECT * FROM `TrovaAule` WHERE `Aula` LIKE '$text'");
  $Row = mysql_fetch_assoc($search);
  $aula = $Row["Aula"];
  $lat = $Row["Lat"];
  $lon = $Row["Lon"];
  $piano = $Row["Piano"];
  $edificio = $Row["Edificio"];
  if(isset($aula)){
    sendMessage($chatID,"true","L'aula: $aula si trova al piano $piano nell'edificio $edificio");
    sendLocation($chatID,$lat,$lon);
  }else{
    sendMessage($chatID,"true","Mi dispiace, non sono riuscito a trovare l'aula: $text");
  }}



// State Mod Subject
else if($state == 'modsubject'){
  mysql_query("UPDATE `Utenti` SET `State`='modsubject1',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
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
    mysql_query("UPDATE `Utenti` SET `State`='modsubject',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
    sendMessage($chatID,"true","Mi dispiace, non ho trovato materie con questo nome inerente al tuo corso, riprova");
  }}



// State Mod Subject 1
else if($state == 'modsubject1'){
mysql_query("UPDATE `Utenti` SET `State`='0',`Materia`='$text',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
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

