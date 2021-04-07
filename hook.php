<?php
require 'vendor/autoload.php';
$data = file_get_contents('php://input');
$data = json_decode($data, true);
$api = new Binance\API("lBmye1phscDHMWNiQycfDi5n8ENfjMWFZAe8fwgtUtwuqidFsaPe5qi1KtouGcdg","gA5s7pq4ubYCRiAyD6DidtCguhOyD1I8vJtmDKqKAzYjHWEmhLSFf2BfmorAAiaV");
$condition = false;
$count = 0;
$pares = ["BNBUSDT", "ETHUSDT", "BCHUSDT"];// пары, с которыми я работаю | 
if (empty($data['message']['chat']['id'])) {
	exit();
}
$message = $data["message"];
$chat_id = 435322357;

$text = $data['message']['text'];
 
switch($text) {
    case "/test":
        sendMessageToUser($chat_id, "test");
        break;
    case "/startWork":
        while(true) {
            foreach($pares as &$values) { // работаем с каждой парой | 
                work($values, $chat_id, $api);
                sleep(5);
            }
                
        }
        break;
            
    case "/stopWork":
        $condition = true;
        break;
    default:
}

function sendMessage($chat_id, $text) {// функция отправки сообщения пользователю | 
    $response = array
    (
		'chat_id' => $chat_id,
		'text' => $text
	);
	$ch = curl_init('https://api.telegram.org/bot1184926146:AAEgmCHlMRVn-2tXy765OkIKxj-8OcB-PBQ/sendMessage');  
	curl_setopt($ch, CURLOPT_POST, 1);  
	curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
function work(string $pareName, $chat_id, $api) {
    $old_price = file_get_contents(__DIR__ . '/'.$pareName.'price.txt');// получаем старую цену пары
    $moving = file_get_contents(__DIR__ . '/'.$pareName.'moveType.txt');// получаем состояние(покупка/продажа)
    $accuracy = file_get_contents(__DIR__ . '/'.$pareName.'accuracy.txt');// точность, с которой мы округлим количество приобретаемых монет
    $price = ($api->price($pareName));// получаем текущую цену пары
    $prevDay = $api->prevDay($pareName);// получаем предыдущие цены за 24 часа
    $min_price = $prevDay["lowPrice"];// получаем самую низкую цену за 24 часа
    $min_multiplier = file_get_contents(__DIR__ . '/'.$pareName.'minmultiplier.txt');//
    $max_multiplier = file_get_contents(__DIR__ . '/'.$pareName.'maxmultiplier.txt');// множитель, на который умножается цена закупки, для получения желаемой цены монеты, при которой будет выгодно продать
    $saved_min_price = file_get_contents(__DIR__ . '/'.$pareName.'minsavedprice.txt');// сохраненная минимальная сумма пары
    $ticker = $api->prices();
    $balances = $api->balances($ticker);// получаем балансы всех своих монет
    if($min_price > $saved_min_price)
    {
        $min_price = $saved_min_price;
    }
    if($moving == "sell")// пытаемся продать монету
    {
        if($price > $old_price*$max_multiplier)// если текущая цена выше желаемой 
        {
            $quantity = $balances[substr($pareName, 0, -4)]["available"];
            sendMessage($chat_id, "__________");
            sendMessage($chat_id, "___success sell".$pareName);
            sendMessage($chat_id, "___buy price ".$old_price." sell price ". $price);
            sendMessage($chat_id, "___balance ".$quantity*$price);
            $order = $api->marketSell($pareName, $quantity);
            file_put_contents(__DIR__ . '/'.$pareName.'minsavedprice.txt', $min_price);
            file_put_contents(__DIR__ . '/'.$pareName. 'moveType.txt', "buy");
        }
        else
        {
            sendMessage($chat_id, $pareName." failed sell, now price: ".$price." < wanted price: ". $old_price*$max_multiplier);
        }
    }
    else// пытаемся купить монету 
    {
        if($price < $min_price*$min_multiplier && $balances["USDT"]["available"] >= 12)
        {
            $quantity = round(12/$price, $accuracy);
            
            sendMessage($chat_id, "__________");
            sendMessage($chat_id,"___success buy".$pareName);
            sendMessage($chat_id,"___buy price ". $price." minim ".$min_price);
            sendMessage($chat_id,"___quantity ". $quantity);
            $order = $api->marketBuy($pareName, $quantity);
            file_put_contents(__DIR__ . '/'.$pareName.'price.txt', $price);
            file_put_contents(__DIR__ . '/'.$pareName. 'moveType.txt', "sell");
        }
        else
        {
            sendMessage($chat_id, $pareName." failed buy, now price: ".$price." > wanted price: ". $min_price*$min_multiplier." balance ".$balances["USDT"]["available"]." minim ".$min_price. " saved minim ".$saved_min_price);
        }
    }
}