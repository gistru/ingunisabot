<?php
// Send Message
function sendMessage($chatID,$preview,$message,$menu,$type){
  if(isset($menu)){
    if($type=="real"){
      $button='&reply_markup={"keyboard":['.$menu.'],"resize_keyboard":true}';
    }else{
      $button='&reply_markup={"inline_keyboard":['.$menu.'],"resize_keyboard":true}';
    }};
  if(isset($menu)){
    if($type=="hide"){
      $button='&reply_markup={"remove_keyboard":true,"resize_keyboard":true}';
    }};
    $url=$GLOBALS[website]."/sendMessage?chat_id=$chatID&disable_web_page_preview=$preview&parse_mode=HTML&text=".urlencode($message).$button;
    file_get_contents($url);
  };
// Edit Message
function editMessage($chatID,$message_id,$preview,$newText,$menu,$type){
  if(isset($menu)){
    if($type=="real"){
      $button='&reply_markup={"keyboard":['.$menu.'],"resize_keyboard":true}';
    }else{
      $button='&reply_markup={"inline_keyboard":['.$menu.'],"resize_keyboard":true}';
    }};
    $url=$GLOBALS[website]."/editMessageText?chat_id=$chatID&message_id=$message_id&disable_web_page_preview=$preview&parse_mode=HTML&text=".urlencode($newText).$button;
    file_get_contents($url);
  };
// Send Photo
function sendPhoto($chatID,$photo){
    $url=$GLOBALS[website]."/sendPhoto?chat_id=$chatID&photo=$photo";
    file_get_contents($url);
    };
// Send Document
function sendDocument($chatID,$document){
    $url=$GLOBALS[website]."/sendDocument?chat_id=$chatID&document=$document";
    file_get_contents($url);
    };
// Send Location
function sendLocation($chatID,$latitude,$longitude){
    $url=$GLOBALS[website]."/sendLocation?chat_id=$chatID&latitude=$latitude&longitude=$longitude";
    file_get_contents($url);
    };
?>
