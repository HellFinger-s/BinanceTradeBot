<?php
require 'vendor/autoload.php';
$data = file_get_contents('php://input');
$data = json_decode($data, true);
$api = new Binance\API();// ключи API | API keys
$condition = false;
$count = 0;
$couples = ["BNBUSDT", "ETHUSDT", "BCHUSDT"];// пары, с которыми я работаю | couples I work with
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
            foreach($couples as &$values) { // работаем с каждой парой | work with each couple
                work($values, $chat_id, $api);
                sleep(5);
            }
                
        }
        break;
    default:
}

function sendMessage($chat_id, $text) {// функция отправки сообщения пользователю | message sending function
  $response = array
  (
    'chat_id' => $chat_id,
    'text' => $text
  );
  $ch = curl_init('https://api.telegram.org/botBotToken/sendMessage');  
  curl_setopt($ch, CURLOPT_POST, 1);  
  curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $res = curl_exec($ch);
  curl_close($ch);
  return $res;
}
function work(string $coupleName, $chat_id, $api) {
    $old_price = file_get_contents(__DIR__ . '/'.$coupleName.'price.txt');// получаем старую цену пары | get old price of couple
    $moving = file_get_contents(__DIR__ . '/'.$coupleName.'moveType.txt');// получаем состояние(покупка/продажа) | get condition (buy/sell)
    $accuracy = file_get_contents(__DIR__ . '/'.$coupleName.'accuracy.txt');// точность, с которой мы округлим количество приобретаемых монет | the accuracy with which we round the number of coins purchased
    $price = $api->price($coupleName);// получаем текущую цену пары | get current price
    $prevDay = $api->prevDay($coupleName);// получаем предыдущие цены за 24 часа | old prices for last 24 hours
    $min_price = $prevDay["lowPrice"];// получаем самую низкую цену за 24 часа | lowest price for last 24 hours
    $min_multiplier = file_get_contents(__DIR__ . '/'.$coupleName.'minmultiplier.txt');//
    $max_multiplier = file_get_contents(__DIR__ . '/'.$coupleName.'maxmultiplier.txt');// множитель, на который умножается цена закупки, для получения желаемой цены монеты, при которой будет выгодно продать | the multiplier by which the purchase price is multiplied to get the desired price of the coin, at which it will be profitable to sell
    $saved_min_price = file_get_contents(__DIR__ . '/'.$coupleName.'minsavedprice.txt');// сохраненная минимальная сумма пары | saved minimal couple price
    $ticker = $api->prices();
    $balances = $api->balances($ticker);// получаем балансы всех своих монет | get all coins balances
    if($min_price > $saved_min_price)
    {
        $min_price = $saved_min_price;
    }
    if($moving == "sell")// пытаемся продать монету | try to sell coin
    {
        if($price > $old_price*$max_multiplier)// если текущая цена выше желаемой | if now price more than wanted
        {
            $quantity = $balances[substr($coupleName, 0, -4)]["available"];// получаем количество монет для продажи | get coin quantity for sell
            sendMessage($chat_id, "__________");// сообщение об удачной продаже | send message of success sell
            sendMessage($chat_id, "___success sell".$coupleName);
            sendMessage($chat_id, "___buy price ".$old_price." sell price ". $price);
            sendMessage($chat_id, "___balance ".$quantity*$price);
            $order = $api->marketSell($coupleName, $quantity);// продаем | sell
            file_put_contents(__DIR__ . '/'.$coupleName.'minsavedprice.txt', $min_price);
            file_put_contents(__DIR__ . '/'.$coupleName. 'moveType.txt', "buy");
        }
        else
        {
            sendMessage($chat_id, $coupleName." failed sell, now price: ".$price." < wanted price: ". $old_price*$max_multiplier);// сообщение о неудачной продаже | send message of unsuccess sell
        }
    }
    else// пытаемся купить монету | try to buy coin
    {
        if($price < $min_price*$min_multiplier && $balances["USDT"]["available"] >= 12)// если цена ниже желаемой и на балансе есть минимальная сумма для покупки | if price is less than wanted and enough money on the balance
        {
            $quantity = round(12/$price, $accuracy);// получаем количество монет для покупки | get coin quantity for buy
            
            sendMessage($chat_id, "__________");// сообщение об успешной покупке | send message of success buy
            sendMessage($chat_id,"___success buy".$coupleName);
            sendMessage($chat_id,"___buy price ". $price." minim ".$min_price);
            sendMessage($chat_id,"___quantity ". $quantity);
            $order = $api->marketBuy($coupleName, $quantity);// покупаем | buy
            file_put_contents(__DIR__ . '/'.$coupleName.'price.txt', $price);
            file_put_contents(__DIR__ . '/'.$coupleName. 'moveType.txt', "sell");
        }
        else
        {
            sendMessage($chat_id, $coupleName." failed buy, now price: ".$price." > wanted price: ". $min_price*$min_multiplier." balance ".$balances["USDT"]["available"]." minim ".$min_price. " saved minim ".$saved_min_price);// сообщение о неудачной покупке | send message of unsuccess buy
        }
    }
}
