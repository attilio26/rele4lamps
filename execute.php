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
	/on_on -> outlet ON  heater ON
	/plon_htof -> outlet ON   heater OFF  
	/plof_hton -> outlet OFF  heater ON 
	/off_off -> outlet OFF  heater OFF 
	/heatplug  -> Lettura stazione6 ... su bus RS485  \n/verbose -> parametri del messaggio";
}

//<-- Comandi ai rele
elseif($text=="on_on"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/6/3");
}
elseif(strpos($text,"plon_htof")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/6/2");
}
elseif(strpos($text,"plof_hton")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/6/1");
}
elseif(strpos($text,"off_off")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/6/0");
}
//<-- Lettura parametri slave5
elseif(strpos($text,"heatplug")){
	$response = file_get_contents("http://dario95.ddns.net:8083/heatplug");
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
$parameters["reply_markup"] = '{ "keyboard": [["/on_on \ud83d\udd34", "/plon_htof \ud83d\udd0c"],["/plof_hton \ud83d\udd04", "/off_off \ud83d\udd35"],["/heatplug \u2753"]], "resize_keyboard": true, "one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>