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
$website="https://api.telegram.org/bot".$token;
/* ----------------------
Updates
---------------------- */
$updates=file_get_contents("php://input");
$updates=json_decode($updates,TRUE);
/* ----------------------
Variables
---------------------- */
$agg = json_encode($updates, JSON_PRETTY_PRINT);
$text=$updates["message"]["text"];
$chatID=$updates["message"]["from"]["id"];
$name=$updates["message"]["from"]["first_name"];
$username=$updates["message"]["from"]["username"];
$userid=$updates["message"]["message_id"];
$query=$updates["callback_query"];
$queryUserID=$query["from"]["id"];
$queryname=$query["from"]["first_name"];
$queryusername=$query["from"]["username"];
$querydata=$query["data"];
$querymsid=$query["message"]["message_id"];
$querytext=$query["message"]["text"];
$queryreply=$query["message"]["reply_markup"]["inline_keyboard"];
/* ----------------------
Timezone
---------------------- */
date_default_timezone_set("Europe/Rome");
$oldLocale = setlocale(LC_TIME, 'it_IT');
$date=strftime("%a %d %b %Y");
$datazione=date("d/m/Y");
setlocale(LC_TIME, $oldLocale);
$time=date("H:i");
$day=strftime("%a");
$timestamp = time();
/* ----------------------
Menus
---------------------- */
$orari = '[{"text":"08:30%20-%2009:30","callback_data":"1"}],[{"text":"09:30%20-%2010:30","callback_data":"2"}],[{"text":"10:30%20-%2011:30","callback_data":"3"}],[{"text":"11:30%20-%2012:30","callback_data":"4"}],[{"text":"12:30%20-%2013:30","callback_data":"5"}],[{"text":"13:30%20-%2014:30","callback_data":"6"}],[{"text":"14:30%20-%2015:30","callback_data":"7"}],[{"text":"15:30%20-%2016:30","callback_data":"8"}],[{"text":"16:30%20-%2017:30","callback_data":"9"}],[{"text":"17:30%20-%2018:30","callback_data":"10"}],[{"text":"Esci","callback_data":"exit"}]';
$iniziamo ='[{"text":"Iniziamo","callback_data":"start"}]';
$menuanno = '[{"text":"1","callback_data":"1"}],[{"text":"2","callback_data":"2"}],[{"text":"3","callback_data":"3"}],[{"text":"4","callback_data":"4"}],[{"text":"5","callback_data":"5"}]';
$menuannoprofilo = '[{"text":"1","callback_data":"1"}],[{"text":"2","callback_data":"2"}],[{"text":"3","callback_data":"3"}],[{"text":"4","callback_data":"4"}],[{"text":"5","callback_data":"5"}],[{"text":"Torna%20indietro","callback_data":"goback"}]';
$profilo = '[{"text":"Modifica%20Cartella","callback_data":"modfolder"},{"text":"Modifica%20Anno","callback_data":"modanno"}],[{"text":"Modifica%20Materia","callback_data":"modsubject"},{"text":"Cancella%20Profilo","callback_data":"cancprofilo"}]';
$menuskip = '[{"text":"Salta%20questo%20passaggio","callback_data":"skip"}]';
$menuesami = '[{"text":"Esami%20Unisa","url":"https://kutt.it/esamiunisa"}]';
$materia = '[{"text":"Insegnamenti%20Unisa","url":"https://kutt.it/insegnamentiunisa"}]';
$orariopdf = '[{"text":"Insegnamenti%20Unisa","url":"https://kutt.it/insegnamentiunisa"}],[{"text":"Orario%20in%20PDF","callback_data":"orariopdf"}]';
$eliminafeeback = '[{"text":"Elimina%20feeback","callback_data":"deletefeed"}]';
$exit ='[{"text":"","callback_data":""}]';
$condizioni = '[{"text":"Termini%20e%20Condizioni","url":"https://github.com/gistru/ing-unisa-bot/blob/master/terms.md"}]';
/* ----------------------
Query Data
---------------------- */
// Torna indietro
if($querydata=="goback"){
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
$Row = mysql_fetch_assoc($search);
$corso = $Row["Corso"];
$anno = $Row["Anno"];
$cartella = $Row["Cartella"];
editMessage($queryUserID,$querymsid,"true","<b>Corso di studi:</b> $corso\n<b>Anno:</b> $anno\n<b>Cartella:</b> $cartella",$profilo,'inline');
break;
};
// Menu Anno
if($querydata=="modanno"){
editMessage($queryUserID,$querymsid,"true","Che anno frequenti?",$menuannoprofilo,'inline');
break;
};
// Modifica Anno
if($querydata=="1"||$querydata=="2"||$querydata=="3"||$querydata=="4"||$querydata=="5"){
mysql_query("UPDATE `Utenti` SET `Anno`='$querydata',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Hai modificato l'anno di studi</b>");
break;
};
// Modifica Cartella
if($querydata=="modfolder"){
mysql_query("UPDATE `Utenti` SET `State`='modfolder',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Ok, inviami il nuovo TOKEN</b>");
break;
};
// Modifica Materia
if($querydata=="modsubject"){
mysql_query("UPDATE `Utenti` SET `State`='modsubject',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Ok, quale materia stai studiando?</b>\n<i>Inserisci un insegnamento</i>",$materia,'inline');
break;
};
// Salta passaggio cartella
if($querydata=="skip"){
mysql_query("UPDATE `Utenti` SET `State`='config4',`Cartella`='',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Hai saltato questo passaggio</b>");
sendMessage($queryUserID,$querymsid,"<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n\nSe vuoi cancellare il profilo ti basterà utilizzare il comando /cancellaprofilo");
break;
};
// Orario PDF
if($querydata=="orariopdf"){
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$queryUserID'");
$Row = mysql_fetch_assoc($search);
$corso = $Row["Corso"];
if($corso=="06121"){
$link="Ingegneriacivile_triennale_";
}else if($corso=="06123"){
$link="Ingegneriameccanica_triennale_";
}else if($corso=="06122"){
$link="Ingegneriachimica_triennale_";
}else if($corso=="06127"){
$link="Ingegneriainformatica_triennale_";
}else if($corso=="06124"){
$link="Ingegneriaelettronica_triennale_";
}else if($corso=="06126"){
$link="Ingegneriagestionale_triennale_";
}else if($corso=="06125"){
$link="Ingegneriacivileperlambienteeilterritorio_triennale_";
}else if($corso=="06601"){
$link="Ingegneriaedile-architettura_quinquennale_";
}else if($corso=="06225"){
$link="Ingegneriaperlambienteeilterritorio_Magistrale_";
}else if($corso=="06223"){
$link="Ingegneriameccanica_Magistrale_";
}else if($corso=="06227"){
$link="Ingegneriainformatica_Magistrale_";
}else if($corso=="06226"){
$link="Ingegneriagestionale_Magistrale_";
}else if($corso=="06224"){
$link="Ingegneriaelettronica_Magistrale_";
}else if($corso=="06221"){
$link="Ingegneriacivile_Magistrale_";
}else if($corso=="06222"){
$link="Ingegneriachimica_Magistrale_";
}else if($corso=="06228"){
$link="Foodengineering_Magistrale_";
}else if($corso=="06229"){
$link="Digitalhealthandbioinformaticengineering_Magistrale_";
$corso="DH";
};
editMessage($queryUserID,$querymsid,"true","<b>Ti ho inviato il file</b>");
sendDocument($queryUserID,"https://easycourse.unisa.it/EasyCourse/Orario/Facolta_di_Ingegneria/2019-2020/356/Curricula/$link$corso.pdf?$timestamp");
break;
};
// Cancella Feedback
if($querydata=="deletefeed"){
mysql_query("DELETE FROM `Feedback` WHERE `Status`= -1");
unset($randomString);
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Hai eliminato il tuo Feedback!</b>");
break;
};
// Cancella Profilo
if($querydata=="cancprofilo"){
mysql_query("DELETE FROM `Utenti` WHERE `ChatID`='$queryUserID'");
editMessage($queryUserID,$querymsid,"true","<b>Profilo cancellato!</b>");
break;
};
/* ----------------------
Switch
---------------------- */
switch ($text){
//start
case '/start':
sendMessage($chatID,"true","Ciao <b>$name!</b>\nBenvenuto su <b>IngegneriaUnisaBot</b>.\n\nPer iniziare subito a cercare gli orari dei corsi e le cartelle condivise ho bisogno che imposti un tuo profilo, ma se non sei interessato puoi tranquillamente saltare questo step. Se dovessi cambiare idea, puoi configurarlo in un secondo momento.\nDi seguito puoi trovare tutti i comandi del Bot\n\n/start - avvia bot\n/config - configura il tuo profilo\n/profilo - informazioni del tuo profilo\n/cancellaprofilo - cancella il tuo profilo\n/orari - cerca gli orari delle lezioni\n/esami - trova le date dei prossimi appelli\n/studentingegneria - cerca su StudentIngegneria\n/r0x - cerca su r0x\n/aulelibere - aule libere oggi\n/aulario - trova la posizione di un'aula\n/cartellecondivise - cerco cartelle condivise\n/cercadocente - ricerca dei docenti\n/biblioteca - cerca nel catalogo di ateneo\n/trovacompagno - cerca un compagno di studio\n/mensa - menu del giorno\n/membri - quanti utenti attivi ci sono\n/webcam - webcam unisa\n/feedback - inviami un feedback\n/annulla - interrompe l'operazione corrente\n/info - informazioni sul bot\n\n<b>Seleziona una funzione per iniziare</b>");
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','0',NULL,NULL,'','','il $date alle $time')");
break;
// Modulo Feedback
case '/token':
mysql_query("UPDATE `Utenti` SET `State`='token',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Sei nel modulo segreto feedback. Premi un tasto");
break;
// Modulo Invio
case '/token':
mysql_query("UPDATE `Utenti` SET `State`='token',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Sei nel modulo segreto contatti. Puoi mandare un messaggio a tutti i membri");
break;
// Config
case '/config':
mysql_query("SET NAMES 'utf8'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
mysql_query("UPDATE `Utenti` SET `State`='config',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
}else{
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','config',NULL,NULL,'','','il $date alle $time')");
};
sendMessage($chatID,"true","Quando vuoi iniziamo",$iniziamo,'real');
break;
// Membri
case '/membri':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$users = mysql_query("SELECT * FROM `Utenti` WHERE ChatID");
$count = mysql_num_rows($users);
sendMessage($chatID,"true","<b>Utenti attivi:</b> $count");
break;
// Info
case '/info':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Per info, termini e condizioni di utilizzo clicca qui",$condizioni,'inline');
break;
// Profilo
case '/profilo':
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$corso = $Row["Corso"];
$anno = $Row["Anno"];
$cartella = $Row["Cartella"];
$materia = $Row["Materia"];
if(isset($anno)){
sendMessage($chatID,"true","<b>Corso di studi:</b> $corso\n<b>Anno:</b> $anno\n<b>Cartella:</b> $cartella\n<b>Materia:</b> $materia",$profilo,'inline');
}else{
sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
}
break;
// Esci
case 'Esci':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0', `Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;
// Annulla
case '/annulla':
sendMessage($chatID,"true","<b>Operazione Annullata</b>",$exit,"hide");
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
exit();
break;
// Cancella Profilo
case '/cancellaprofilo':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
mysql_query("DELETE FROM `Utenti` WHERE `ChatID`='$chatID'");
sendMessage($chatID,"true","<b>Profilo cancellato!</b>");
break;
// Aule libere
case '/aulelibere':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
if($day!=="sab" && $day!=="dom"){
mysql_query("UPDATE `Utenti` SET `State`='aulelibere',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare le aule libere di oggi</b>",$orari,'real');
}else{
sendMessage($chatID,"true","Non hai bisogno di cercare aule libere oggi");
}}else{
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','aulelibere',NULL,NULL,'','','il $date alle $time')");
if($day!=="sab" && $day!=="dom"){
sendMessage($chatID,"true","<b>Ok ora puoi cercare le aule libere di oggi</b>",$orari,'real');
}else{
sendMessage($chatID,"true","Non hai bisogno di cercare aule libere oggi");
}};
break;
// Orari
case '/orari':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
if(isset($id)){
if(isset($corso)){
$url="https://corsi.unisa.it/$corso/didattica/orari";
mysql_query("UPDATE `Utenti` SET `State`='orari',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok quale insegnamento vuoi cercare?</b>\n\n<i>Clicca qui per l'agenda completa del tuo corso di studi:</i> $url\n\n<i>Vuoi l'orario del tuo corso di studi in pdf?\nClicca sul bottone qui sotto</i>",$orariopdf,'inline');
}else{
sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
}}else{
sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
};
break;
// Esami
case '/esami':
mysql_query("UPDATE `Utenti` SET `State`='esami',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare gli esami</b>\n<i>Aggiornato alla sessione f.c. I semestre 2019/2020</i>\n\nPosso cercare tra più di 400 esami\nPer questo motivo ho bisogno di sapere il nome esatto dell'esame\n\n<i>Non ricordi il nome dell'esame?</i>","$menuesami","inline");
break;
// R0X
case '/r0x':
mysql_query("SET NAMES 'utf8'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
mysql_query("UPDATE `Utenti` SET `State`='r0x',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare su r0x.it</b>");
}else{
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','r0x',NULL,NULL,'','','il $date alle $time')");
sendMessage($chatID,"true","<b>Ok ora puoi cercare su r0x.it</b>");
};
break;
// Cartelle Condivise
case '/cartellecondivise':
mysql_query("SET NAMES 'utf8'");
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$corso = $Row["Corso"];
$anno = $Row["Anno"];
if(isset($id)){
if(isset($corso)){
if(isset($anno)){
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` != $id AND `Corso` LIKE '$corso' AND `Anno` LIKE '$anno'");
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
}}else{
sendMessage($chatID,"true","Non hai ancora impostato il tuo profilo o è incompleto");
};
break;
// StudentIngegneria
case '/studentingegneria':
mysql_query("UPDATE `Utenti` SET `State`='SI',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare sul sito StudentIngengeria</b>");
break;
// Trova Compagno
case '/trovacompagno':
mysql_query("SET NAMES 'utf8'");
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
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
sendMessage($chatID,"true","Sembra che non ci sia nessuno che stia studiando $materia");
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
if($day!=="sab" && $day!=="dom"){
if($time>="00:01" && $time<="10:00"){
sendMessage($chatID,"true","Torna più tardi, aggiorno questa funzione alle 10:00");
}else if($time>="10:00" && $time<="15:00"){
sendMessage($chatID,"true","Ok, ti invio il menu della mensa di oggi");
$xml = simplexml_load_file('http://ammensa-unisa.appspot.com');
$menuUrl= $xml->menu->menuUrl;
sendDocument($chatID,"$menuUrl");
}else{
sendMessage($chatID,"true","Sono le $time, torna domani per il nuovo menu");
}}else{
sendMessage($chatID,"true","Il menu della mensa non verrà pubblicato oggi");
};
break;
// Ricerca Docente
case '/cercadocente':
mysql_query("UPDATE `Utenti` SET `State`='docente',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare un docente</b>");
break;
// Trova Aula
case '/aulario':
mysql_query("SET NAMES 'utf8'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
mysql_query("UPDATE `Utenti` SET `State`='aulario',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare la tua aula</b>");
}else{
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','aululario',NULL,NULL,'','','il $date alle $time')");
sendMessage($chatID,"true","<b>Ok ora puoi cercare la tua aula</b>");
};
break;
// Biblioteca
case '/biblioteca':
mysql_query("SET NAMES 'utf8'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
if(isset($id)){
mysql_query("UPDATE `Utenti` SET `State`='biblio',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
}else{
mysql_query("INSERT INTO `Utenti`(`ChatID`, `Name`, `Username`, `State`, `Corso`, `Anno`, `Cartella`, `Materia`, `Log`) VALUES ('$chatID','$name','$username','biblio',NULL,NULL,'','','il $date alle $time')");
sendMessage($chatID,"true","<b>Ok ora puoi cercare nel Catalogo di ateneo</b>");
};
break;
// Webcam
case '/webcam':
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ok ti sto per inviare le immagini delle webcam</b>");
sendPhoto($chatID,"https://web.unisa.it/uploads/stazionemeteo/webcam/stecca9.jpg?$timestamp");
sendPhoto($chatID,"http://www.campanialive.it/webcam/unisa/piazzacampus.jpg?$timestamp");
break;
// Feedback
case '/feedback':
mysql_query("UPDATE `Utenti` SET `State`='feedback',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Problemi\nErrori\nCommenti\nSuggerimenti\nRichieste di nuove funzioni\n</b>\n<i>Scrivimi un Feedback affinchè possa migliorare</i>");
break;
// Default
default:
mysql_query("SET NAMES 'utf8'");
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID` LIKE '$chatID'");
$Row = mysql_fetch_assoc($search);
$id = $Row["ChatID"];
$state = $Row["State"];
$corso = $Row["Corso"];
$ingegneria = $Row["Ingegneria"];
$anno = $Row["Anno"];
if(isset($id)){
if($state=="config"){
mysql_query("UPDATE `Utenti` SET `State`='config1',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Qual è il tuo corso di studi?</b>\n\nDigital health and bioinformatic engineering - Magistrale [06229]\nFood engineering - Magistrale [06228]\nIngegneria chimica - Magistrale [06222]\nIngegneria civile - Magistrale [06221]\nIngegneria elettronica - Magistrale [06224]\nIngegneria gestionale - Magistrale [06226]\nIngegneria informatica - Magistrale [06227]\nIngegneria meccanica - Magistrale [06223]\nIngegneria per l'ambiente e il territorio - Magistrale [06225]\nIngegneria chimica - triennale [06122]\nIngegneria civile - triennale [06121]\nIngegneria civile per l'ambiente e il territorio - triennale [06125]\nIngegneria elettronica - triennale [06124]\nIngegneria gestionale - triennale [06126]\nIngegneria informatica - triennale [06127]\nIngegneria meccanica - triennale [06123]\nIngegneria edile-architettura - quinquennale [06601]\n\n<i>Inserisci il codice corrispondente posto tra parentesi quadre</i>",$iniziamo,'hide');
}else if($state=="config1"){
if($text=="06229"||$text=="06228"||$text=="06222"||$text=="06221"||$text=="06224"||$text=="06226"||$text=="06227"||$text=="06223"||$text=="06225"||$text=="06122"||$text=="06121"||$text=="06125"||$text=="06124"||$text=="06126"||$text=="06127"||$text=="06123"||$text=="06601"){
mysql_query("UPDATE `Utenti` SET `State`='config2',`Corso`='$text',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Che anno frequenti?",$menuanno,'real');
}else{
sendMessage($chatID,"true","Non hai inserito un codice di corso valido\nPer favore reinseriscilo");
}}else if($state=="config2"){
if($text=="1"||$text=="2"||$text=="3"||$text=="4"||$text=="5"){
mysql_query("UPDATE `Utenti` SET `State`='config3',`Anno`='$text',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Con G Suite gli studenti Unisa hanno spazio illimitato in cloud\n\nTi piacerebbe condividere una cartella con altri studenti del tuo corso?\n\nCon la funziona /cartellecondivise in seguito posso individuare tutte le cartelle di altri studenti del tuo corso di studi che hanno fatto lo stesso\n\n<b>Scrivi qui il TOKEN al collegamento</b>\n\n<i>https://drive.google.com/drive/folders/TOKEN</i>",$menuanno,'hide');
sendMessage($chatID,"true","Non ora?",$menuskip,'inline');
}else{
sendMessage($chatID,"true","Non hai inserito un anno di corso valido\nPer favore reinseriscilo");
}}else if($state=="config3"){
$_text=preg_replace('/[^A-Za-z0-9\-_]/', '', $text);
$url = "https://drive.google.com/drive/folders/$_text";
$url=str_replace(' ', '', $url);
$handle = curl_init($url);
curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
$response = curl_exec($handle);
$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
if($httpCode == 404) {
sendMessage($chatID,"true","Non hai inserito un link valido");
curl_close($handle);
}else{
mysql_query("UPDATE `Utenti` SET `State`='config4',`Cartella`='$url',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n\nSe vuoi cancellare il profilo ti basterà utilizzare il comando /cancellaprofilo");
curl_close($handle);
}
curl_close($handle);
}else if($state=="config4"){
sendMessage($chatID,"true","<b>Il tuo profilo è completato!</b>\nSeleziona una funzione");
}else if($state=="esami"){
$_text = mysql_real_escape_string($text);
$search=mysql_query("SELECT DISTINCT  * FROM `Esami` WHERE `Insegnamento` LIKE '$_text'");
while($Row = mysql_fetch_assoc($search)){
$insegnamento=$Row["Insegnamento"];
$docente=$Row["Docente"];
$esame = $Row["Esami"];
$esami[]=array($insegnamento,$docente,$esame);
};
$esami=str_replace(array('[',']','{','}','"',',','        '), '', json_encode($esami, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE));
$esami=str_replace(array('\n'), ' ', $esami);
if($esami != "null"){
sendMessage($chatID,"true","$esami");
}else{
sendMessage($chatID,"true","Mi dispiace ma non ho trovato nessun insegnamento con questo nome");
}}else if($state=="orari"){
$_text = mysql_real_escape_string($text);
$search=mysql_query("SELECT * FROM `Insegnamenti` WHERE `Insegnamento` LIKE '%$_text%'");
while($Row = mysql_fetch_assoc($search)){
$insegnamento = $Row["Insegnamento"];
$cds = $Row["Corso di studi"];
$crediti = $Row["Crediti"];
$docente = $Row["Docente"];
$lezioni = $Row["Lezioni"];
$insegnamenti[]=array($insegnamento,$cds,$crediti,$docente,$lezioni);
};
$insegnamenti=str_replace(array('[',']','{','}','"',',','        '), '', json_encode($insegnamenti, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE));
$insegnamenti=str_replace(array('\n'), ' ', $insegnamenti);
$insegnamenti=str_replace(array('--------------'), '|', $insegnamenti);
if($insegnamenti != "null"){
sendMessage($chatID,"true",$insegnamenti);
}else{
sendMessage($chatID,"true","Mi dispiace, non ho trovato nessun insegnamento con questo nome nel I Semestre");
}}else if($state=="aulelibere"){
if($day!=="sab" && $day!=="dom"){
$search=mysql_query("SELECT `Aula`, `$text` FROM `Aule_$day` WHERE `$text` = ''");
sendMessage($chatID,"true", "Il $day nel range $text sono disponibili queste aule");
while($Row = mysql_fetch_assoc($search)){
$aule = $Row["Aula"];
$aulearray[]=$aule;
};
$aulearray=str_replace(array('"',',','[',']',',','    '), '', json_encode($aulearray, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE));
$aulearray=str_replace(array('\r\n'), ' - ', $aulearray);
sendMessage($chatID,"true",$aulearray);
}else{
sendMessage($chatID,"true","Non hai bisogno di cercare aule libere oggi");
}}else if($state=="r0x"){
sendMessage($chatID,"true","Ok, dammi un secondo &#128269;");
$i=0;
$tot_pages=25;
while ($i <= $tot_pages){
$_text=str_replace(' ', '%20', $text);
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
$i=$i+25;
unset($ricerca);
}};
sendMessage($chatID,"true","<b>Ricerca completata!</b>");
}else if($state=='docente'){
$_text=str_replace(' ', '%20', $text);
$html = file_get_html("https://rubrica.unisa.it/persone?nome=$_text");
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
sendMessage($chatID,"true", str_replace(array('[',']','{','}','"',',','    ','\r\n'), '', json_encode($informazioni, JSON_PRETTY_PRINT | ENT_NOQUOTES | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE)));
}else{
sendMessage($chatID,"true","Non ho trovato nessun docente con questo nome");
}}else if($state=='SI'){
sendMessage($chatID,"true","Ok, dammi un secondo &#128269;");
$_text=str_replace(' ', '%20', $text);
$html = file_get_html("http://www.studentingegneria.it/?s=$_text");
foreach($html->find('h2') as $risultato) {
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
unset($ricerca);
}
sendMessage($chatID,"true","<b>Ricerca completata!</b>");
}else if($state=="token"){
$search=mysql_query("SELECT * FROM `Utenti` WHERE `ChatID`");
while($Row = mysql_fetch_assoc($search))
{
$id = $Row["ChatID"];
if($id!=$chatID){
sendMessage($id,"true","$text");
};
};
sendMessage($chatID,"true","Invio Completato");
}else if($state=="token"){
$search=mysql_query("SELECT * FROM `Feedback`");
while($Row = mysql_fetch_assoc($search))
{
$feedback = $Row["Feedback"];
$feedbacks[] = $feedback;
};
$feedbacks = implode("\n\n", $feedbacks);
sendMessage($chatID,"true", "$feedbacks");
sendMessage($chatID,"true","Ricerca Feedback Completato");
}else if($state=="modfolder"){
$_text=preg_replace('/[^A-Za-z0-9\-_]/', '', $text);
$url = "https://drive.google.com/drive/folders/$_text";
$url=str_replace(' ', '', $url);
$handle = curl_init($url);
curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
$response = curl_exec($handle);
$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
if($httpCode == 404) {
sendMessage($chatID,"true","Non hai inserito un link valido");
curl_close($handle);
}else{
mysql_query("UPDATE `Utenti` SET `State`='config4',`Cartella`='$url',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Perfetto, abbiamo finito!</b>\n\nEcco il tuo /profilo\n\nSe vuoi cancellare il profilo ti basterà utilizzare il comando /cancellaprofilo");
curl_close($handle);
}
curl_close($handle);
}else if($state=='feedback'){
mysql_query("INSERT INTO `Feedback`(`Feedback`, `Captcha`, `Status`, `Log`) VALUES ('$text','0','-1','il $date alle $time')");
$characters = '0123456789';
$randomString = '';
$lunghezza=5;
for ($i = 0; $i < $lunghezza; $i++) {
$index = rand(0, strlen($characters) - 1);
$randomString .= $characters[$index];
}
mysql_query("UPDATE `Utenti` SET `State`='fbv',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
mysql_query("UPDATE `Feedback` SET `Captcha`='$randomString'  WHERE `Feedback` LIKE '$text'");
sendMessage($chatID,"true","Per confermare l'invio del messaggio scrivi il seguente captcha altrimenti eliminalo:\n\n$randomString",$eliminafeeback,'inline');
}else if($state=="fbv"){
$search=mysql_query("SELECT * FROM `Feedback` WHERE `Captcha` LIKE '$text'");
$Row = mysql_fetch_assoc($search);
$captcha = $Row["Captcha"];
if($text=="$captcha"){
mysql_query("UPDATE `Feedback` SET `Status`='1'  WHERE `Captcha` LIKE '$text'");
unset($randomString);
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Messaggio inviato correttamente!</b>");
}else{
mysql_query("DELETE FROM `Feedback` WHERE `Status`= -1 ");
unset($randomString);
mysql_query("UPDATE `Utenti` SET `State`='0',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","Errore nella sintassi del captcha. Il messaggio è stato eliminato");
}}else if($state=='biblio'){
sendMessage($chatID,"true","Ok, dammi un secondo &#128269;");
$_text=str_replace(' ', '%20', $text);
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
}else if($state=='aulario'){
$search=mysql_query("SELECT * FROM `TrovaAule` WHERE `Aula` LIKE '$text'");
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
}}else if($state=='modsubject'){
mysql_query("UPDATE `Utenti` SET `State`='0',`Materia`='$text',`Log`='il $date alle $time' WHERE `ChatID` LIKE '$chatID'");
sendMessage($chatID,"true","<b>Ottimo! Ho aggiornato la tua materia di studio</b>");
}else if($state=='0'){
sendMessage($chatID,"true","Seleziona una funzione per iniziare");
}}else{
sendMessage($chatID,"true","Ops, Qualcosa è andato storto\nRiparti con /start");
};
break;
};
?>

