<?php

//kindly refer to the https://karthikbhat.net/woocommerce-api-custom-endpoint/
class PluginWebhookMyfatoorahWoocommerce {

//-----------------------------------------------------------------------------------------------------------------------------

    private $logger;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('woocommerce_api_myfatoorah_webhook', array($this, 'checkEventType'));

        //v1
        add_action('myfatoorah_woocommerce_webhook_TransactionsStatusChanged', array($this, 'TransactionsStatusChanged'));
        add_action('myfatoorah_woocommerce_webhook_RefundStatusChanged', array($this, 'RefundStatusChanged'));

        //v2
        add_action('myfatoorah_woocommerce_webhook_PAYMENT_STATUS_CHANGED', array($this, 'PAYMENT_STATUS_CHANGED'));

        $this->logger = WC_LOG_DIR . 'myfatoorah_webhook.log';
    }

//-----------------------------------------------------------------------------------------------------------------------------

    function checkEventType() {
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        $secretKey = $v2Options['webhookSecretKey'] ?? null;

        try {
            $request = MyFatoorahWebhook::processWebhookRequest($secretKey, $this->logger);
        } catch (Exception $ex) {
            die($ex->getMessage());
        }


        if (is_string($request['Event'])) {
            do_action('myfatoorah_woocommerce_webhook_' . $request['Event'], $request['Data']);
        } else if ($request['Event']['Code'] == 1) {
            do_action('myfatoorah_woocommerce_webhook_PAYMENT_STATUS_CHANGED', $request['Data']);
        } else if ($request['Event']['Code'] == 2) {
            $data = [
                'InvoiceId'       => $request['Data']['ReferencedInvoice']['Id'],
                'RefundId'        => $request['Data']['Refund']['Id'],
                'RefundStatus'    => $request['Data']['Refund']['Status'],
                'RefundReference' => $request['Data']['Refund']['Reference'],
                'Amount'          => $request['Data']['Amount']['ValueInBaseCurrency'],
                'CreatedDate'     => $request['Data']['Refund']['CreationDate'],
                'Comments'        => $request['Data']['Refund']['Comment'],
                'version'         => 'v2',
            ];
            do_action('myfatoorah_woocommerce_webhook_RefundStatusChanged', $data);
        }
        die('Done');
    }

//-----------------------------------------------------------------------------------------------------------------------------

    function TransactionsStatusChanged($data) {
        $orderId       = $data['CustomerReference'];
        $paymentId     = $data['PaymentId'];
        $paymentStatus = $data['TransactionStatus'];
        $this->processWebhook($orderId, $paymentId, $paymentStatus, $data);
    }

    function PAYMENT_STATUS_CHANGED($data) {
        $data['Transaction']['Status'] = MyFatoorahWebhook::mapWebhook2Status($data['Transaction']['Status']);

        $orderId       = $data['Invoice']['ExternalIdentifier'];
        $paymentId     = $data['Transaction']['PaymentId'];
        $paymentStatus = $data['Transaction']['Status'];
        $this->processWebhook($orderId, $paymentId, $paymentStatus, $data, 2);
    }

    function processWebhook($orderId, $paymentId, $paymentStatus, $data, $version = 1) {
        $order = wc_get_order($orderId);
        if (!$order) {
            die('Order not found.');
        }

        $paymentMethod = $order->get_payment_method();
        if (!str_contains($paymentMethod, 'myfatoorah_')) {
            die('Wrong Payment Method.');
        }

        //don't process because the Paid is a final status
        if ($order->get_meta('myfatoorah_status', true) == 'Paid') {
            die('Order already Paid');
        }

        //don't process for the same payment id and the status is not SUCCESS
        if ($order->get_meta('PaymentId', true) == $paymentId) {
            die("Transaction already $paymentStatus.");
        }

        $class   = 'WC_Gateway_' . ucfirst($paymentMethod);
        $gateway = new $class;
        try {
            if ($version == 1) {
                $gateway->checkStatus($data['InvoiceId'], 'InvoiceId', $order, ' - WebHook');
            } else {
                $this->checkStatusWebhook2($data, $order, $gateway->orderStatus);
            }

            $msg = 'Status: ' . $order->get_status();
        } catch (Exception $ex) {
            $msg = 'Error: ' . $ex->getMessage();
        }
        MyFatoorah::$loggerObj = $this->logger;
        MyFatoorah::log("MyFatoorah WebHook TransactionsStatusChanged: Order #$orderId ----- $msg");
        die($msg);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param type $data
     * @param type $order
     * @param type $gateway
     * @return type
     */
    function RefundStatusChanged($data) {

        MyFatoorah::$loggerObj = $this->logger;

        //get order
        $orderList = wc_get_orders(array(
            'limit'      => -1, // Query all orders
            'meta_key'   => 'InvoiceId', // The postmeta key field
            'meta_value' => $data['InvoiceId'], // The comparison argument
        ));
        $order     = $orderList[0] ?? die('no order');
        $orderId   = $order->get_id();

        //check order status
        $status = $order->get_status();
        if ($status != 'processing' && $status != 'completed') {
            $msg = "Order #$orderId ----- Can't complete the refund because the order status is $status";
            MyFatoorah::log("MyFatoorah WebHook RefundStatusChanged: $msg");
            die($msg);
        }

        //get RefundStatus array
        $refundData = $order->get_meta('RefundData', true) ?: die('no refund data');
        $refundObj  = $refundData[$data['RefundId']] ?? die('no refun object');

        $isRefundProcessed = $order->get_meta('refund_processed_' . $data['RefundId'], false);
        if ($isRefundProcessed) {
            $msg = "Order #$orderId ----- This Refund Id " . $data['RefundId'] . ' is marked as Processed already';
            MyFatoorah::log("MyFatoorah WebHook RefundStatusChanged: $msg");
            die($msg);
        }
        //Mark as processed to prevent repeated refunds
        $order->update_meta_data('refund_processed_' . $data['RefundId'], true);
        $order->save(); // 🔥 save هنا

        $displayAmount   = $refundObj->DisplayAmount;
        $displayCurrency = $refundObj->DisplayCurrency;

        $orderCurrency = $order->get_currency();
        if ($displayCurrency != $orderCurrency) {
            $msg = "Order #$orderId ----- Can't complete the refund because the refund currency status is $displayCurrency and the order currency is $orderCurrency";
            MyFatoorah::log("MyFatoorah WebHook RefundStatusChanged: $msg");
            die($msg);
        }

        $noteTitle = '<b>MyFatoorah Refund Details:</b><br>';
        $note      = $noteTitle;

        //update
        if ($data['RefundStatus'] == 'CANCELED') {
            $noteColor = 'brown';
        } else if ($data['RefundStatus'] == 'REFUNDED' && $displayAmount == $order->get_remaining_refund_amount()) {
            $noteColor = 'green';
            $note      .= '<font color="green"><b>Fully Refunded</b></font><br>';
            $order->update_status('refunded', $noteTitle);
        } else {
            $noteColor = 'chocolate';
            $note      .= '<font color="chocolate"><b>Partial Refunded</b></font><br>';

            $default_args = array(
                'amount'   => $displayAmount,
                'reason'   => $data['Comments'],
                'order_id' => $orderId
            );
            wc_create_refund($default_args);
        }

        $note .= 'RefundStatus: <font color="' . $noteColor . '">' . $data['RefundStatus'] . '</font><br>';
        $note .= 'RefundId: ' . $data['RefundId'] . '<br>';
        $note .= 'RefundReference: ' . $data['RefundReference'] . '<br>';

        $createdDate = isset($data['version']) ? new DateTime($data['CreatedDate']) : DateTime::createFromFormat('dmYHis', $data['CreatedDate']);
        $note        .= 'CreatedDate: ' . date_format($createdDate, 'Y-m-d H:i:s') . '<br>';

        $baseCurrency = $order->get_meta('InvoiceBaseCurrency', true) ?: '';
        $note         .= 'BaseAmount: ' . $data['Amount'] . ' ' . $baseCurrency . '<br>';
        $note         .= 'DisplayAmount: ' . $displayAmount . ' ' . $displayCurrency . '<br>';

        $note .= 'Comment: ' . $data['Comments'] . '<br>';

        $order->add_order_note($note);

        $msg = "MyFatoorah WebHook RefundStatusChanged: Order #$orderId ----- Status is " . $data['RefundStatus'] . ' for RefundId: ' . $data['RefundId'];

        MyFatoorah::$loggerObj = $this->logger;
        MyFatoorah::log($msg);
        echo($msg);
    }

//-----------------------------------------------------------------------------------------------------------------------------


    public function checkStatusWebhook2($data, $order, $configStatus) {

        //update meta data
        $this->updatePostMetaWebhook2($order, $data);

        //add notes
        $this->addOrderNoteWebhook2($order, $data);

        //update status
        $wooStatus = ($data['Invoice']['Status'] == 'PAID') ? $configStatus : 'failed';
        $order->update_status($wooStatus, "<b>MyFatoorah Webhook:</b><br/>", true);

        $order->set_transaction_id($data['Transaction']['PaymentId']);

        //Calling the save() method is a relatively expensive operation, so you may wish to avoid calling it more times than necessary (for example, if you know it will be called later in the same flow, you may wish to avoid additional earlier calls when operating on the same object).
        $order->save();
    }

    //-----------------------------------------------------------------------------------------------------------------------------

    public function updatePostMetaWebhook2(&$order, $data) {
        $order->update_meta_data('InvoiceId', $data['Invoice']['Id']);
        $order->update_meta_data('InvoiceReference', $data['Invoice']['Reference']);
        $order->update_meta_data('InvoiceDisplayCurrencyValue', $data['Amount']['ValueInDisplayCurrency'] . ' ' . $data['Amount']['DisplayCurrency']);
        $order->update_meta_data('InvoiceBaseValue', $data['Amount']['ValueInBaseCurrency']);

        //focusTransaction
        $order->update_meta_data('InvoiceBaseCurrency', $data['Amount']['BaseCurrency']);
        $order->update_meta_data('PaymentGateway', $data['Transaction']['PaymentMethod']);
        $order->update_meta_data('PaymentId', $data['Transaction']['PaymentId']);
        $order->update_meta_data('ReferenceId', $data['Transaction']['ReferenceId']);
        $order->update_meta_data('TransactionId', $data['Transaction']['Id']);

        $order->update_meta_data('myfatoorah_status', $data['Transaction']['Status']);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function addOrderNoteWebhook2(&$order, $data) {
        $note = "<b>MyFatoorah Webhook Payment Details:</b><br>";

        $note .= 'InvoiceStatus: ' . $data['Transaction']['Status'] . '<br>';
        if ($data['Transaction']['Status'] !== 'Paid') {
            $note .= 'InvoiceError: ' . $data['Transaction']['Error']['Message'] . '<br>';
        }

        $note .= 'InvoiceId: ' . $data['Invoice']['Id'] . '<br>';
        $note .= 'InvoiceReference: ' . $data['Invoice']['Reference'] . '<br>';
        $note .= 'InvoiceDisplayValue: ' . $data['Amount']['ValueInDisplayCurrency'] . ' ' . $data['Amount']['DisplayCurrency'] . '<br>';
        $note .= 'InvoiceBaseValue: ' . $data['Amount']['ValueInBaseCurrency'] . '<br>';

        //focusTransaction
        $note .= 'InvoiceBaseCurrency: ' . $data['Amount']['BaseCurrency'] . '<br>';
        $note .= 'PaymentGateway: ' . $data['Transaction']['PaymentMethod'] . '<br>';
        $note .= 'PaymentId: ' . $data['Transaction']['PaymentId'] . '<br>';
        $note .= 'ReferenceId: ' . $data['Transaction']['ReferenceId'] . '<br>';
        $note .= 'TransactionId: ' . $data['Transaction']['Id'] . '<br>';

        $order->add_order_note($note);
    }

//-----------------------------------------------------------------------------------------------------------------------------
}
