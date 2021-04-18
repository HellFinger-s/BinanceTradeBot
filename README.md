# Telegrams bot for trading cryptocurrency on Binance
### **Description**
   This bot was developed by me as a replacement for sites with Ready-made Homework.
   Most of these web pages contain a lot of advertising, some of them even provide services for a permanent subscription of 30 rubles / day.
   I think that this price is unaffordable for the average student. Also search through the name of the subject/author of the book
   can be simplified. I developed a bot that used the book's ISBN (unique book item number) to navigate through the answers.
   The user pays 1 ruble once a day only for the days on which he uses the bot.
### **Used technologies**
   In developing I use:
   * [Telegram Database Library](https://github.com/tdlib/td)
   * Programming language PHP
   * [PHP extention, which allows to work with TDLib](https://github.com/yaroslavche/phptdlib)
### [Link on demostration video](https://radikal.ru/video/8atWkSLbFbg)



# Телеграм бот для торговли криптовалютой на Бинанс
### **Описание**
   Данный бот был разработан мною для автоматизации процесса торговли криптовалютами на бирже. На текущий момент реализована самая простая стратегия: дешевле купи, дороже продай.    Если цена ниже минимальной за 24 часа, то бот покупает монету. Если же цена выше цены покупки на определенное значение, то продает. Данная стратегия хорошо себя ведет на       скачущих между определенными ценами монетах и плох при постоянном падении/повышении цены монеты. 
### **История торговли**

### **Используемые технологии**
   При разработке бота я использовал:
   * [Расширение для PHP, позволяющее торговать на Binance](https://github.com/jaggedsoft/php-binance-api)
   * Язык программирования PHP
