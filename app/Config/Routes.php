<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultController('Login');
$routes->setAutoRoute(false);

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');

$routes->add('no_access/index/(:segment)', 'No_access::index/$1');
$routes->add('no_access/index/(:segment)/(:segment)', 'No_access::index/$1/$2');

// Guardrails: coleta de erros de JS do browser (sem autenticacao)
$routes->post('guardrail/js-error', 'Guardrail::jsError');

// ═══════════════════════════════════════════════════════════
//  HOME
// ═══════════════════════════════════════════════════════════
$routes->get('home', 'Home::getIndex');
$routes->get('home/logout', 'Home::getLogout');
$routes->get('home/changePassword/(:num)', 'Home::getChangePassword/$1');
$routes->post('home/save/(:num)', 'Home::postSave/$1');

$routes->add('reports/summary_(:any)/(:any)/(:any)', 'Reports::Summary_$1/$2/$3/$4');
$routes->add('reports/summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/summary_payments', 'Reports::date_input_only');
$routes->add('reports/summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/summary_(:any)', 'Reports::date_input');

$routes->add('reports/graphical_(:any)/(:any)/(:any)', 'Reports::Graphical_$1/$2/$3/$4');
$routes->add('reports/graphical_summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/graphical_summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/graphical_(:any)', 'Reports::date_input');

$routes->add('reports/inventory_(:any)/(:any)', 'Reports::Inventory_$1/$2');
$routes->add('reports/inventory_low', 'Reports::inventory_low');
$routes->add('reports/inventory_summary', 'Reports::inventory_summary_input');
$routes->add('reports/inventory_summary/(:any)/(:any)/(:any)', 'Reports::inventory_summary/$1/$2/$3');

$routes->add('reports/detailed_(:any)/(:any)/(:any)/(:any)', 'Reports::Detailed_$1/$2/$3/$4');
$routes->add('reports/detailed_sales', 'Reports::date_input_sales');
$routes->add('reports/detailed_receivings', 'Reports::date_input_recv');

$routes->add('reports/specific_(:any)/(:any)/(:any)/(:any)', 'Reports::Specific_$1/$2/$3/$4');
$routes->add('reports/specific_customers', 'Reports::specific_customer_input');
$routes->add('reports/specific_employees', 'Reports::specific_employee_input');
$routes->add('reports/specific_discounts', 'Reports::specific_discount_input');
$routes->add('reports/specific_suppliers', 'Reports::specific_supplier_input');

// ═══════════════════════════════════════════════════════════
//  SALES
// ═══════════════════════════════════════════════════════════
$routes->get('sales', 'Sales::getIndex');
$routes->get('sales/add', 'Sales::getIndex');
$routes->get('sales/manage', 'Sales::getManage');
$routes->get('sales/search', 'Sales::getSearch');
$routes->get('sales/getSaleItems', 'Sales::getSaleItems');
$routes->get('sales/getPaymentSummary', 'Sales::getPaymentSummary');
$routes->get('sales/itemSearch', 'Sales::itemSearch');
$routes->get('sales/editForm/(:num)', 'Sales::getEditForm/$1');
$routes->get('sales/row/(:num)', 'Sales::getRow/$1');
$routes->get('sales/edit/(:num)', 'Sales::getEdit/$1');
$routes->get('sales/receipt/(:num)', 'Sales::getReceipt/$1');
$routes->get('sales/deleteItem/(:num)', 'Sales::getDeleteItem/$1');
$routes->get('sales/deletePayment/(:segment)', 'Sales::getDeletePayment/$1');
$routes->get('sales/suspended', 'Sales::getSuspended');
$routes->get('sales/discardSuspendedSale', 'Sales::getDiscardSuspendedSale');
$routes->get('sales/removeCustomer', 'Sales::getRemoveCustomer');
$routes->get('sales/sendPdf/(:num)', 'Sales::getSendPdf/$1');
$routes->get('sales/sendPdf/(:num)/(:segment)', 'Sales::getSendPdf/$1/$2');
$routes->get('sales/sendReceipt/(:num)', 'Sales::getSendReceipt/$1');
$routes->get('sales/invoice/(:num)', 'Sales::getInvoice/$1');
$routes->get('sales/salesKeyboardHelp', 'Sales::getSalesKeyboardHelp');
$routes->get('sales/getPaymentSummary/(:num)', 'Sales::getPaymentSummary/$1');

$routes->post('sales/add', 'Sales::postAdd');
$routes->post('sales/addDiversos', 'Sales::addDiversos');
$routes->post('sales/save/(:num)', 'Sales::postSave/$1');
$routes->post('sales/editItem/(:num)', 'Sales::postEditItem/$1');
$routes->post('sales/delete', 'Sales::delete');
$routes->post('sales/quickFinish', 'Sales::postQuickFinish');
$routes->post('sales/suspend', 'Sales::postSuspend');
$routes->post('sales/unsuspend', 'Sales::postUnsuspend');
$routes->post('sales/cancel', 'Sales::postCancel');
$routes->post('sales/selectCustomer', 'Sales::postSelectCustomer');
$routes->post('sales/setPrintAfterSale', 'Sales::postSetPrintAfterSale');
$routes->post('sales/setPriceWorkOrders', 'Sales::postSetPriceWorkOrders');
$routes->post('sales/setEmailReceipt', 'Sales::postSetEmailReceipt');
$routes->post('sales/addPayment', 'Sales::postAddPayment');
$routes->post('sales/complete', 'Sales::postComplete');
$routes->post('sales/checkInvoiceNumber', 'Sales::postCheckInvoiceNumber');
$routes->post('sales/changeItemNumber', 'Sales::postChangeItemNumber');
$routes->post('sales/changeItemName', 'Sales::postChangeItemName');
$routes->post('sales/changeItemDescription', 'Sales::postChangeItemDescription');
$routes->post('sales/restore', 'Sales::restore');
$routes->post('sales/restore/(:num)', 'Sales::restore/$1');

// ═══════════════════════════════════════════════════════════
//  ITEMS
// ═══════════════════════════════════════════════════════════
$routes->get('items', 'Items::getIndex');
$routes->get('items/search', 'Items::getSearch');
$routes->get('items/row/(:segment)', 'Items::getRow/$1');
$routes->get('items/view', 'Items::getView');
$routes->get('items/view/(:num)', 'Items::getView/$1');
$routes->get('items/bulkEdit', 'Items::getBulkEdit');
$routes->get('items/suggest', 'Items::getSuggest');
$routes->get('items/suggestLowSell', 'Items::getSuggestLowSell');
$routes->get('items/suggestKits', 'Items::getSuggestKits');
$routes->get('items/suggestCategory', 'Items::getSuggestCategory');
$routes->get('items/suggestLocation', 'Items::getSuggestLocation');
$routes->get('items/inventory/(:num)', 'Items::getInventory/$1');
$routes->get('items/countDetails/(:num)', 'Items::getCountDetails/$1');
$routes->get('items/attributes/(:num)', 'Items::getAttributes/$1');
$routes->get('items/removeLogo/(:segment)', 'Items::getRemoveLogo/$1');
$routes->get('items/generateBarcodes/(:segment)', 'Items::getGenerateBarcodes/$1');
$routes->get('items/generateCsvFile', 'Items::getGenerateCsvFile');
$routes->get('items/csvImport', 'Items::getCsvImport');
$routes->get('items/getItemKits', 'Items::getIndex');
$routes->get('items/getInventory', 'Items::getInventory');
$routes->get('items/checkNumeric', 'Items::getCheckNumeric');

$routes->post('items/save', 'Items::postSave');
$routes->post('items/save/(:num)', 'Items::postSave/$1');
$routes->post('items/delete', 'Items::postDelete');
$routes->post('items/bulkUpdate', 'Items::postBulkUpdate');
$routes->post('items/suggest_search', 'Items::suggest_search');
$routes->post('items/attributes/(:num)', 'Items::postAttributes/$1');
$routes->post('items/checkItemNumber', 'Items::postCheckItemNumber');
$routes->post('items/check_kit_exists', 'Items::check_kit_exists');
$routes->post('items/saveInventory/(:num)', 'Items::postSaveInventory/$1');
$routes->post('items/importCsvFile', 'Items::postImportCsvFile');

// ═══════════════════════════════════════════════════════════
//  CUSTOMERS
// ═══════════════════════════════════════════════════════════
$routes->get('customers', 'Customers::getIndex');
$routes->get('customers/search', 'Customers::getSearch');
$routes->get('customers/row/(:num)', 'Customers::getRow/$1');
$routes->get('customers/view', 'Customers::getView');
$routes->get('customers/view/(:num)', 'Customers::getView/$1');
$routes->get('customers/suggest', 'Customers::getSuggest');
$routes->get('customers/csv', 'Customers::getCsv');
$routes->get('customers/csvImport', 'Customers::getCsvImport');

$routes->post('customers/save', 'Customers::postSave');
$routes->post('customers/save/(:num)', 'Customers::postSave/$1');
$routes->post('customers/delete', 'Customers::postDelete');
$routes->post('customers/suggest_search', 'Customers::suggest_search');
$routes->post('customers/checkEmail', 'Customers::postCheckEmail');
$routes->post('customers/checkAccountNumber', 'Customers::postCheckAccountNumber');
$routes->post('customers/importCsvFile', 'Customers::postImportCsvFile');

// ═══════════════════════════════════════════════════════════
//  EMPLOYEES
// ═══════════════════════════════════════════════════════════
$routes->get('employees', 'Employees::getIndex');
$routes->get('employees/search', 'Employees::getSearch');
$routes->get('employees/row/(:num)', 'Employees::getRow/$1');
$routes->get('employees/view', 'Employees::getView');
$routes->get('employees/view/(:num)', 'Employees::getView/$1');
$routes->get('employees/suggest', 'Employees::getSuggest');
$routes->get('employees/checkUsername/(:segment)', 'Employees::getCheckUsername/$1');

$routes->post('employees/save', 'Employees::postSave');
$routes->post('employees/save/(:num)', 'Employees::postSave/$1');
$routes->post('employees/delete', 'Employees::postDelete');
$routes->post('employees/suggest_search', 'Employees::suggest_search');

// ═══════════════════════════════════════════════════════════
//  SUPPLIERS
// ═══════════════════════════════════════════════════════════
$routes->get('suppliers', 'Suppliers::getIndex');
$routes->get('suppliers/search', 'Suppliers::getSearch');
$routes->get('suppliers/row/(:num)', 'Suppliers::getRow/$1');
$routes->get('suppliers/view', 'Suppliers::getView');
$routes->get('suppliers/view/(:num)', 'Suppliers::getView/$1');
$routes->get('suppliers/suggest', 'Suppliers::getSuggest');

$routes->post('suppliers/save', 'Suppliers::postSave');
$routes->post('suppliers/save/(:num)', 'Suppliers::postSave/$1');
$routes->post('suppliers/delete', 'Suppliers::postDelete');
$routes->post('suppliers/suggest_search', 'Suppliers::suggest_search');

// ═══════════════════════════════════════════════════════════
//  RECEIVINGS
// ═══════════════════════════════════════════════════════════
$routes->get('receivings', 'Receivings::getIndex');
$routes->get('receivings/index', 'Receivings::getIndex');
$routes->get('receivings/manage', 'Receivings::getIndex');
$routes->get('receivings/itemSearch', 'Receivings::getItemSearch');
$routes->get('receivings/stockItemSearch', 'Receivings::getStockItemSearch');
$routes->get('receivings/edit/(:num)', 'Receivings::getEdit/$1');
$routes->get('receivings/deleteItem/(:segment)', 'Receivings::getDeleteItem/$1');
$routes->get('receivings/removeSupplier', 'Receivings::getRemoveSupplier');
$routes->get('receivings/receipt/(:num)', 'Receivings::getReceipt/$1');
$routes->get('receivings/search', 'Receivings::getIndex');

$routes->post('receivings/selectSupplier', 'Receivings::postSelectSupplier');
$routes->post('receivings/changeMode', 'Receivings::postChangeMode');
$routes->post('receivings/setComment', 'Receivings::postSetComment');
$routes->post('receivings/setPrintAfterSale', 'Receivings::postSetPrintAfterSale');
$routes->post('receivings/setReference', 'Receivings::postSetReference');
$routes->post('receivings/add', 'Receivings::postAdd');
$routes->post('receivings/editItem/(:segment)', 'Receivings::postEditItem/$1');
$routes->post('receivings/delete', 'Receivings::postDelete');
$routes->post('receivings/complete', 'Receivings::postComplete');
$routes->post('receivings/requisitionComplete', 'Receivings::postRequisitionComplete');
$routes->post('receivings/save', 'Receivings::postSave');
$routes->post('receivings/save/(:num)', 'Receivings::postSave/$1');
$routes->post('receivings/cancelReceiving', 'Receivings::postCancelReceiving');

// ═══════════════════════════════════════════════════════════
//  CONFIG
// ═══════════════════════════════════════════════════════════
$routes->get('config', 'Config::getIndex');
$routes->get('config/stockLocations', 'Config::getStockLocations');
$routes->get('config/dinnerTables', 'Config::getDinnerTables');
$routes->get('config/ajax_tax_categories', 'Config::ajax_tax_categories');
$routes->get('config/customerRewards', 'Config::getCustomerRewards');
$routes->get('config/checkNumeric', 'Config::getCheckNumeric');

$routes->post('config/saveInfo', 'Config::postSaveInfo');
$routes->post('config/saveGeneral', 'Config::postSaveGeneral');
$routes->post('config/checkNumberLocale', 'Config::postCheckNumberLocale');
$routes->post('config/saveLocale', 'Config::postSaveLocale');
$routes->post('config/saveEmail', 'Config::postSaveEmail');
$routes->post('config/saveMessage', 'Config::postSaveMessage');
$routes->post('config/checkMailchimpApiKey', 'Config::postCheckMailchimpApiKey');
$routes->post('config/saveMailchimp', 'Config::postSaveMailchimp');
$routes->post('config/saveLocations', 'Config::postSaveLocations');
$routes->post('config/saveTables', 'Config::postSaveTables');
$routes->post('config/saveTax', 'Config::postSaveTax');
$routes->post('config/saveRewards', 'Config::postSaveRewards');
$routes->post('config/saveBarcode', 'Config::postSaveBarcode');
$routes->post('config/saveReceipt', 'Config::postSaveReceipt');
$routes->post('config/saveInvoice', 'Config::postSaveInvoice');
$routes->post('config/removeLogo', 'Config::postRemoveLogo');

// ═══════════════════════════════════════════════════════════
//  GIFTCARDS
// ═══════════════════════════════════════════════════════════
$routes->get('giftcards', 'Giftcards::getIndex');
$routes->get('giftcards/search', 'Giftcards::getSearch');
$routes->get('giftcards/row/(:num)', 'Giftcards::getRow/$1');
$routes->get('giftcards/view', 'Giftcards::getView');
$routes->get('giftcards/view/(:num)', 'Giftcards::getView/$1');
$routes->get('giftcards/suggest', 'Giftcards::getSuggest');

$routes->post('giftcards/save', 'Giftcards::postSave');
$routes->post('giftcards/save/(:num)', 'Giftcards::postSave/$1');
$routes->post('giftcards/delete', 'Giftcards::postDelete');
$routes->post('giftcards/suggest_search', 'Giftcards::suggest_search');
$routes->post('giftcards/checkNumberGiftcard', 'Giftcards::postCheckNumberGiftcard');

// ═══════════════════════════════════════════════════════════
//  MESSAGES
// ═══════════════════════════════════════════════════════════
$routes->get('messages', 'Messages::getIndex');
$routes->get('messages/view', 'Messages::getView');
$routes->get('messages/view/(:num)', 'Messages::getView/$1');

$routes->post('messages/send', 'Messages::send');
$routes->post('messages/send_form', 'Messages::send_form');
$routes->post('messages/send_form/(:num)', 'Messages::send_form/$1');

// ═══════════════════════════════════════════════════════════
//  ATTRIBUTES
// ═══════════════════════════════════════════════════════════
$routes->get('attributes', 'Attributes::getIndex');
$routes->get('attributes/search', 'Attributes::getSearch');
$routes->get('attributes/row/(:num)', 'Attributes::getRow/$1');
$routes->get('attributes/view', 'Attributes::getView');
$routes->get('attributes/view/(:num)', 'Attributes::getView/$1');
$routes->get('attributes/suggestAttribute/(:num)', 'Attributes::getSuggestAttribute/$1');

$routes->post('attributes/save', 'Attributes::postSave');
$routes->post('attributes/save/(:num)', 'Attributes::postSave/$1');
$routes->post('attributes/delete', 'Attributes::postDelete');
$routes->post('attributes/saveAttributeValue', 'Attributes::postSaveAttributeValue');
$routes->post('attributes/deleteDropdownAttributeValue', 'Attributes::postDeleteDropdownAttributeValue');

// ═══════════════════════════════════════════════════════════
//  CASHUPS
// ═══════════════════════════════════════════════════════════
$routes->get('cashups', 'Cashups::getIndex');
$routes->get('cashups/search', 'Cashups::getSearch');
$routes->get('cashups/row/(:num)', 'Cashups::getRow/$1');
$routes->get('cashups/view', 'Cashups::getView');
$routes->get('cashups/view/(:num)', 'Cashups::getView/$1');

$routes->post('cashups/save', 'Cashups::postSave');
$routes->post('cashups/save/(:num)', 'Cashups::postSave/$1');
$routes->post('cashups/delete', 'Cashups::postDelete');
$routes->post('cashups/ajax_cashup_total', 'Cashups::postAjax_cashup_total');

// ═══════════════════════════════════════════════════════════
//  EXPENSES
// ═══════════════════════════════════════════════════════════
$routes->get('expenses', 'Expenses::getIndex');
$routes->get('expenses/search', 'Expenses::getSearch');
$routes->get('expenses/row/(:num)', 'Expenses::getRow/$1');
$routes->get('expenses/view', 'Expenses::getView');
$routes->get('expenses/view/(:num)', 'Expenses::getView/$1');
$routes->get('expenses/checkNumeric', 'Expenses::getCheckNumeric');

$routes->post('expenses/save', 'Expenses::postSave');
$routes->post('expenses/save/(:num)', 'Expenses::postSave/$1');
$routes->post('expenses/delete', 'Expenses::postDelete');

// ═══════════════════════════════════════════════════════════
//  EXPENSES CATEGORIES
// ═══════════════════════════════════════════════════════════
$routes->get('expenses_categories', 'Expenses_categories::getIndex');
$routes->get('expenses_categories/search', 'Expenses_categories::getSearch');
$routes->get('expenses_categories/row/(:num)', 'Expenses_categories::getRow/$1');
$routes->get('expenses_categories/view', 'Expenses_categories::getView');
$routes->get('expenses_categories/view/(:num)', 'Expenses_categories::getView/$1');

$routes->post('expenses_categories/save', 'Expenses_categories::postSave');
$routes->post('expenses_categories/save/(:num)', 'Expenses_categories::postSave/$1');
$routes->post('expenses_categories/delete', 'Expenses_categories::postDelete');

// ═══════════════════════════════════════════════════════════
//  ITEM KITS
// ═══════════════════════════════════════════════════════════
$routes->get('item_kits', 'Item_kits::getIndex');
$routes->get('item_kits/search', 'Item_kits::getSearch');
$routes->get('item_kits/row/(:num)', 'Item_kits::getRow/$1');
$routes->get('item_kits/view', 'Item_kits::getView');
$routes->get('item_kits/view/(:num)', 'Item_kits::getView/$1');
$routes->get('item_kits/generateBarcodes/(:segment)', 'Item_kits::getGenerateBarcodes/$1');

$routes->post('item_kits/save', 'Item_kits::postSave');
$routes->post('item_kits/save/(:num)', 'Item_kits::postSave/$1');
$routes->post('item_kits/delete', 'Item_kits::postDelete');
$routes->post('item_kits/suggest_search', 'Item_kits::suggest_search');
$routes->post('item_kits/checkItemNumber', 'Item_kits::postCheckItemNumber');

// ═══════════════════════════════════════════════════════════
//  TAXES
// ═══════════════════════════════════════════════════════════
$routes->get('taxes', 'Taxes::getIndex');
$routes->get('taxes/search', 'Taxes::getSearch');
$routes->get('taxes/row/(:num)', 'Taxes::getRow/$1');
$routes->get('taxes/view', 'Taxes::getView');
$routes->get('taxes/view/(:num)', 'Taxes::getView/$1');
$routes->get('taxes/view_tax_codes', 'Taxes::getView_tax_codes');
$routes->get('taxes/view_tax_codes/(:num)', 'Taxes::getView_tax_codes/$1');
$routes->get('taxes/view_tax_categories', 'Taxes::getView_tax_categories');
$routes->get('taxes/view_tax_categories/(:num)', 'Taxes::getView_tax_categories/$1');
$routes->get('taxes/view_tax_jurisdictions', 'Taxes::getView_tax_jurisdictions');
$routes->get('taxes/view_tax_jurisdictions/(:num)', 'Taxes::getView_tax_jurisdictions/$1');
$routes->get('taxes/suggestTaxCodes', 'Taxes::getSuggestTaxCodes');
$routes->get('taxes/ajax_tax_codes', 'Taxes::ajax_tax_codes');
$routes->get('taxes/ajax_tax_categories', 'Taxes::ajax_tax_categories');
$routes->get('taxes/ajax_tax_jurisdictions', 'Taxes::ajax_tax_jurisdictions');

$routes->post('taxes/save', 'Taxes::postSave');
$routes->post('taxes/save/(:num)', 'Taxes::postSave/$1');
$routes->post('taxes/delete', 'Taxes::postDelete');
$routes->post('taxes/suggest_search', 'Taxes::suggest_search');
$routes->post('taxes/suggest_tax_categories', 'Taxes::suggest_tax_categories');
$routes->post('taxes/save_tax_codes', 'Taxes::save_tax_codes');
$routes->post('taxes/save_tax_jurisdictions', 'Taxes::save_tax_jurisdictions');
$routes->post('taxes/save_tax_categories', 'Taxes::save_tax_categories');

// ═══════════════════════════════════════════════════════════
//  TAX CATEGORIES
// ═══════════════════════════════════════════════════════════
$routes->get('tax_categories', 'Tax_categories::getIndex');
$routes->get('tax_categories/search', 'Tax_categories::getSearch');
$routes->get('tax_categories/row/(:num)', 'Tax_categories::getRow/$1');
$routes->get('tax_categories/view', 'Tax_categories::getView');
$routes->get('tax_categories/view/(:num)', 'Tax_categories::getView/$1');

$routes->post('tax_categories/save', 'Tax_categories::postSave');
$routes->post('tax_categories/save/(:num)', 'Tax_categories::postSave/$1');
$routes->post('tax_categories/delete', 'Tax_categories::postDelete');

// ═══════════════════════════════════════════════════════════
//  TAX CODES
// ═══════════════════════════════════════════════════════════
$routes->get('tax_codes', 'Tax_codes::getIndex');
$routes->get('tax_codes/search', 'Tax_codes::getSearch');
$routes->get('tax_codes/row/(:num)', 'Tax_codes::getRow/$1');
$routes->get('tax_codes/view', 'Tax_codes::getView');
$routes->get('tax_codes/view/(:num)', 'Tax_codes::getView/$1');

$routes->post('tax_codes/save', 'Tax_codes::postSave');
$routes->post('tax_codes/save/(:num)', 'Tax_codes::postSave/$1');
$routes->post('tax_codes/delete', 'Tax_codes::postDelete');

// ═══════════════════════════════════════════════════════════
//  TAX JURISDICTIONS
// ═══════════════════════════════════════════════════════════
$routes->get('tax_jurisdictions', 'Tax_jurisdictions::getIndex');
$routes->get('tax_jurisdictions/search', 'Tax_jurisdictions::getSearch');
$routes->get('tax_jurisdictions/row/(:num)', 'Tax_jurisdictions::getRow/$1');
$routes->get('tax_jurisdictions/view', 'Tax_jurisdictions::getView');
$routes->get('tax_jurisdictions/view/(:num)', 'Tax_jurisdictions::getView/$1');

$routes->post('tax_jurisdictions/save', 'Tax_jurisdictions::postSave');
$routes->post('tax_jurisdictions/save/(:num)', 'Tax_jurisdictions::postSave/$1');
$routes->post('tax_jurisdictions/delete', 'Tax_jurisdictions::postDelete');

// ═══════════════════════════════════════════════════════════
//  REPORTS
// ═══════════════════════════════════════════════════════════
$routes->get('reports', 'Reports::getIndex');
$routes->get('reports/index', 'Reports::getIndex');

$routes->get('reports/summary_(:any)/(:any)/(:any)', 'Reports::Summary_$1/$2/$3/$4');
$routes->get('reports/summary_expenses_categories', 'Reports::date_input_only');
$routes->get('reports/summary_payments', 'Reports::date_input_only');
$routes->get('reports/summary_discounts', 'Reports::summary_discounts_input');
$routes->get('reports/summary_(:any)', 'Reports::date_input');

$routes->get('reports/graphical_(:any)/(:any)/(:any)', 'Reports::Graphical_$1/$2/$3/$4');
$routes->get('reports/graphical_summary_expenses_categories', 'Reports::date_input_only');
$routes->get('reports/graphical_summary_discounts', 'Reports::summary_discounts_input');
$routes->get('reports/graphical_(:any)', 'Reports::date_input');

$routes->get('reports/inventory_(:any)/(:any)', 'Reports::Inventory_$1/$2');
$routes->get('reports/inventory_low', 'Reports::inventory_low');
$routes->get('reports/inventory_summary', 'Reports::inventory_summary_input');
$routes->get('reports/inventory_summary/(:any)/(:any)/(:any)', 'Reports::inventory_summary/$1/$2/$3');

$routes->get('reports/detailed_(:any)/(:any)/(:any)/(:any)', 'Reports::Detailed_$1/$2/$3/$4');
$routes->get('reports/detailed_sales', 'Reports::date_input_sales');
$routes->get('reports/detailed_receivings', 'Reports::date_input_recv');

$routes->get('reports/specific_(:any)/(:any)/(:any)/(:any)', 'Reports::Specific_$1/$2/$3/$4');
$routes->get('reports/specific_customers', 'Reports::specific_customer_input');
$routes->get('reports/specific_employees', 'Reports::specific_employee_input');
$routes->get('reports/specific_discounts', 'Reports::specific_discount_input');
$routes->get('reports/specific_suppliers', 'Reports::specific_supplier_input');

$routes->get('reports/get_detailed_sales_row/(:segment)', 'Reports::getGet_detailed_sales_row/$1');
$routes->get('reports/get_detailed_receivings_row/(:segment)', 'Reports::getGet_detailed_receivings_row/$1');

// ═══════════════════════════════════════════════════════════
//  OFFICE
// ═══════════════════════════════════════════════════════════
$routes->get('office', 'Office::getIndex');
$routes->get('office/logout', 'Office::logout');

// ═══════════════════════════════════════════════════════════
//  PRINTER
// ═══════════════════════════════════════════════════════════
$routes->get('printer/test', 'Printer::getTest');
$routes->get('printer/printReceipt/(:num)', 'Printer::getPrintReceipt/$1');
$routes->post('printer/quickPrint', 'Printer::postQuickPrint');

// ═══════════════════════════════════════════════════════════
//  REST API
// ═══════════════════════════════════════════════════════════
$routes->group('api', function ($routes) {
    $routes->post('auth/login', 'Api\Auth::login');

    $routes->get('items', 'Api\Items::index');
    $routes->get('items/(:num)', 'Api\Items::show/$1');
    $routes->post('items', 'Api\Items::create');
    $routes->put('items/(:num)', 'Api\Items::update/$1');
    $routes->delete('items/(:num)', 'Api\Items::delete/$1');
    $routes->get('items/(:num)/stock', 'Api\Items::stock/$1');

    $routes->get('sales', 'Api\Sales::index');
    $routes->get('sales/(:num)', 'Api\Sales::show/$1');
    $routes->get('sales/(:num)/receipt', 'Api\Sales::receipt/$1');

    $routes->get('customers', 'Api\Customers::index');
    $routes->get('customers/(:num)', 'Api\Customers::show/$1');
    $routes->post('customers', 'Api\Customers::create');
    $routes->put('customers/(:num)', 'Api\Customers::update/$1');

    $routes->get('inventory', 'Api\Inventory::index');
    $routes->get('inventory/alerts', 'Api\Inventory::alerts');
    $routes->get('inventory/low', 'Api\Inventory::low');
});
