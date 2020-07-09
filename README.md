<h1>Invoice Payment Module</h1>

<h3>Установка</h3>

1. Скачайте [плагин](https://github.com/Invoice-LLC/Invoice.Module.WHMCS/archive/master.zip) и разархивируйте его в корневую директорию вашего сайта
2. В админ-панели перейдите во вкладку **Setup->Payments->Payment Gateways**, отредактируйте модуль "Invoice"
3. Добавьте уведомление в личном кабинете Invoice(Вкладка Настройки->Уведомления->Добавить)
      с типом **WebHook** и адресом: **%URL сайта%/modules/gateways/callback/invoice.php**<br>
      ![Imgur](https://imgur.com/lMmKhj1.png)
