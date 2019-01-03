<?php
//03-01-2018
//started on 04-07-2018
// La app di Heroku si puo richiamare da browser con
//			https://rele4lamps.herokuapp.com/


/*API key = 410191340:AAG-7kkwvi6j6ajxuDy7ke1P8tDAh5bjC3U

da browser request ->   https://rele4lamps.herokuapp.com/register.php
           answer  <-   {"ok":true,"result":true,"description":"Webhook was set"}
In questo modo invocheremo lo script register.php che ha lo scopo di comunicare a Telegram
l’indirizzo dell’applicazione web che risponderà alle richieste del bot.

da browser request ->   https://api.telegram.org/bot410191340:AAG-7kkwvi6j6ajxuDy7ke1P8tDAh5bjC3U/getMe
           answer  <-   {"ok":true,"result":{"id":410191340,"is_bot":true,"first_name":"rele4lamps_bot","username":"lamptgbot"}}

riferimenti:
https://gist.github.com/salvatorecordiano/2fd5f4ece35e75ab29b49316e6b6a273
https://www.salvatorecordiano.it/creare-un-bot-telegram-guida-passo-passo/
*/

//------passaggio da getupdates a  WEBHOOK
//da browser request ->   https://api.telegram.org/bot410191340:AAG-7kkwvi6j6ajxuDy7ke1P8tDAh5bjC3U/setWebhook?url=https://rele4lamps.herokuapp.com/execute.php
//					 answer  <-   {"ok":true,"result":true,"description":"Webhook was set"}
//          From now If the bot is using getUpdates, will return an object with the url field empty.
//------passaggio da webhook a  GETUPDATES
//da browser request ->   https://api.telegram.org/bot410191340:AAG-7kkwvi6j6ajxuDy7ke1P8tDAh5bjC3U/setWebhook?url=
//					 answer  <-   {"ok":true,"result":true,"description":"Webhook was deleted"}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";

// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
// converto tutti i caratteri alfanumerici del messaggio in minuscolo
$text = strtolower($text);

header("Content-Type: application/json");

//ATTENZIONE!... Tutti i testi e i COMANDI contengono SOLO lettere minuscole
$response = '';

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto! \n List of commands : 
	/r00 -> GPIO0 LOW  /r01 -> GPIO0 HIGH
	/r10 -> GPIO1 LOW  /r11 -> GPIO1 HIGH 
	/r20 -> GPIO2 LOW  /r21 -> GPIO2 HIGH 
	/r30 -> GPIO3 LOW  /r31 -> GPIO3 HIGH 
	/rele4lamps  -> Lettura     \n/verbose -> parametri del messaggio";
}

//<-- Comandi al rele GPIO0
elseif($text=="r00"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r00");
}
elseif($text=="r01"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r01");
}
//<-- Comandi al rele GPIO1
elseif($text=="r10"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r10");
}
elseif($text=="r11"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r11");
}
//<-- Comandi al rele GPIO2
elseif($text=="r20"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r20");
}
elseif($text=="r21"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r21");
}
//<-- Comandi al rele GPIO3
elseif($text=="r30"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r30");
}
elseif($text=="r31"){
	$response = file_get_contents("http://dario95.ddns.net:20083/r31");
}
//<-- Comando Total OFF
elseif($text=="roff"){
	$response = file_get_contents("http://dario95.ddns.net:20083/rf0");
}
//<-- Comando Total ON
elseif($text=="ron"){
	$response = file_get_contents("http://dario95.ddns.net:20083/rf1");
}

//<-- Lettura stato dei rele
elseif(strpos($text,"status")){
	$response = file_get_contents("http://dario95.ddns.net:20083/r?");
}

//<-- Manda a video la risposta completa
elseif($text=="/verbose"){
	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname ;		
	$response = $response. "\n\n Heroku + dropbox gmail.com";
}


else
{
	$response = "Unknown command!";			//<---Capita quando i comandi contengono lettere maiuscole
}
// Gli EMOTICON sono a:     http://www.charbase.com/block/miscellaneous-symbols-and-pictographs
//													https://unicode.org/emoji/charts/full-emoji-list.html
//													https://apps.timwhitlock.info/emoji/tables/unicode
// la mia risposta è un array JSON composto da chat_id, text, method
// chat_id mi consente di rispondere allo specifico utente che ha scritto al bot
// text è il testo della risposta
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
// imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [
["/r31 \ud83d\udd34", "/r21 \ud83d\udd34", "/r11 \ud83d\udd34", "/r01 \ud83d\udd34"],
["/r30 \ud83d\udd35", "/r20 \ud83d\udd35", "/r10 \ud83d\udd35", "/r00 \ud83d\udd35"],
["/status \u2753"]],
 "resize_keyboard": true, "one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>