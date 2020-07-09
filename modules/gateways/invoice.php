<?php
require "InvoiceSDK/RestClient.php";
require "InvoiceSDK/common/SETTINGS.php";
require "InvoiceSDK/common/ORDER.php";
require "InvoiceSDK/CREATE_TERMINAL.php";
require "InvoiceSDK/CREATE_PAYMENT.php";

function invoice_config()
{
    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "Invoice"),
        "ApiKey" => array("FriendlyName" => "API Key", "Type" => "text", "Size" => "60"),
        "Login" => array("FriendlyName" => "Login", "Type" => "text", "Size" => "60",),
        "DefaultTerminalName" => array("FriendlyName" => "Имя терминала", "Type" => "text", "Size" => "60", "Description" => "Имя терминала по умолчанию"),
    );
    return $configarray;
}

function invoice_link($params)
{
    global $_LANG;

    try {
        $tid = getTerminal($params);
    } catch (Exception $e) {
        return '<h1>Возникла ошибка! Обратитесь к администратору</h1>';
    }

    $order = new INVOICE_ORDER($params['amount']);
    $order->id = $params['invoiceid'];

    $settings = new SETTINGS($tid);
    $settings->success_url = ( ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

    $request = new CREATE_PAYMENT($order, $settings, []);
    $response = (new RestClient($params['Login'], $params['ApiKey']))->CreatePayment($request);

    if($response == null or isset($response->error)) return '<h1>Возникла ошибка! Обратитесь к администратору</h1>';

    $code = '<form method="post" action="' . $response->payment_url . '">
		<input type="submit" value="' . $_LANG["invoicespaynow"] . '" />
		</form>';
    return $code;
}

function getTerminal($params) {
    if(!file_exists('invoice_tid')) file_put_contents('invoice_tid', '');
    $tid = file_get_contents('invoice_tid');
    if($tid == null or empty($tid)) {
        $request = new CREATE_TERMINAL($params['DefaultTerminalName']);
        $response = (new RestClient($params['Login'], $params['ApiKey']))->CreateTerminal($request);

        if($response == null or isset($response->error)) throw new Exception('Terminal error');

        $tid = $response->id;

        file_put_contents('invoice_tid', $tid);
    }

    return $tid;
}
