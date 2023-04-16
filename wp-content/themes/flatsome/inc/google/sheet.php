<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

function sync_order_to_google_sheet($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);
    
    // Get the order data
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_address = $order->get_formatted_billing_address();
    $customer_email = $order->get_billing_email();
    $order_date = $order->get_date_created()->date_i18n('Y-m-d H:i:s');
    $order_status = $order->get_status();
    
    // Authenticate with Google Sheets API
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->setAccessType('offline');
    $service = new Google_Service_Sheets($client);
    
    // Get the spreadsheet ID and worksheet name
    $spreadsheet_id = 'YOUR_SPREADSHEET_ID';
    $worksheet_name = 'YOUR_WORKSHEET_NAME';
    
    // Get the worksheet object
    $spreadsheet = $service->spreadsheets->get($spreadsheet_id);
    $worksheet_list = $spreadsheet->getSheets();
    $worksheet = null;
    foreach ($worksheet_list as $worksheet_item) {
        if ($worksheet_item->properties->title == $worksheet_name) {
            $worksheet = $worksheet_item;
            break;
        }
    }
    
    // Create a new row in the worksheet
    $values = [$customer_name, $customer_address, $customer_email, $order_date, $order_status];
    $request_body = new Google_Service_Sheets_ValueRange([
        'values' => [$values]
    ]);
    $options = array('valueInputOption' => 'USER_ENTERED');
    $service->spreadsheets_values->append(
        $spreadsheet_id,
        $worksheet->properties->title . '!A1',
        $request_body,
        $options
    );
}

// Hook into the WooCommerce order status changed event
add_action('woocommerce_order_status_changed', 'sync_order_to_google_sheet', 10, 1);
