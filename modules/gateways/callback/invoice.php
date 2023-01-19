<?php
if (file_exists("../../../init.php")) {
    include_once("../../../init.php");
} else {
    include_once("../../../dbconnect.php");
    include_once("../../../includes/functions.php");
};
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "invoice";
$GATEWAY = getGatewayVariables($gatewaymodule);

$postData = file_get_contents('php://input');
$notification = json_decode($postData, true);

$type = $notification["notification_type"];
$id = strstr($notification["order"]["id"], "-", true);

$signature = $notification["signature"];

if ($signature != md5($notification["id"] . $notification["status"] . $GATEWAY['ApiKey'])) {
    die('wrong signature');
}

if ($type == "pay") {

    if ($notification["status"] == "successful") {
        addInvoicePayment($id, $notification['id'], $notification['order']['amount'], 0, $gatewaymodule);
        logTransaction($GATEWAY["name"], $_REQUEST['params'], "Successful");
        die('successful');
    }
    if ($notification["status"] == "error") {
        logTransaction($GATEWAY["name"], $_REQUEST['params'], "Successful");
        die('failed');
    }
}

die('null');
