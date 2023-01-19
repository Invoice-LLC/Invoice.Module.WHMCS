<?php
require "InvoiceSDK/RestClient.php";
require "InvoiceSDK/common/SETTINGS.php";
require "InvoiceSDK/common/ORDER.php";
require "InvoiceSDK/CREATE_TERMINAL.php";
require "InvoiceSDK/CREATE_PAYMENT.php";
require "InvoiceSDK/GET_TERMINAL.php";

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

    $request = new CREATE_PAYMENT();
    $request->order = getOrder($params['amount'], $params['invoiceid']);
    $request->settings = getSettings($tid);
    $request->receipt = getReceipt();

    $response = (new RestClient($params['Login'], $params['ApiKey']))->CreatePayment($request);

    if ($response == null or isset($response->error)) return '<h1>Возникла ошибка! Обратитесь к администратору</h1>';

    $code = '<form method="post" action="' . $response->payment_url . '">
		<input type="submit" value="' . $_LANG["invoicespaynow"] . '" />
		</form>';
    return $code;
}

/**
 * @return INVOICE_ORDER
 */
function getOrder($amount, $id)
{
    $order = new INVOICE_ORDER();
    $order->amount = $amount;
    $order->id = "$id" . "-" . bin2hex(random_bytes(5));
    $order->currency = "RUB";

    return $order;
}

/**
 * @return SETTINGS
 */
function getSettings($terminalId)
{
    $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

    $settings = new SETTINGS();
    $settings->terminal_id = $terminalId;
    $settings->success_url = $url;
    $settings->fail_url = $url;

    return $settings;
}

/**
 * @return ITEM
 */
function getReceipt()
{
    $receipt = array();

    return $receipt;
}

function getTerminal($params)
{
    if (!file_exists('invoice_tid')) file_put_contents('invoice_tid', '');
    $tid = file_get_contents('invoice_tid');
    $terminal = new GET_TERMINAL();
    $terminal->alias =  $tid;
    $info = (new RestClient($params['Login'], $params['ApiKey']))->GetTerminal($terminal);

    if ($tid == null or empty($tid) || $info->id == null || $info->id != $terminal->alias) {
        $request = new CREATE_TERMINAL();
        $request->name = $params['DefaultTerminalName'];
        $request->type = "dynamical";
        $request->description = "WHMCS module terminal";
        $request->defaultPrice = 0;
        $response = (new RestClient($params['Login'], $params['ApiKey']))->CreateTerminal($request);

        if ($response == null or isset($response->error)) throw new Exception('Terminal error');

        $tid = $response->id;
        file_put_contents('invoice_tid', $tid);
    }

    return $tid;
}
