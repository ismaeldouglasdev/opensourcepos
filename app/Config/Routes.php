<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);
$routes->setDefaultController('Login');

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');

$routes->get('no_access/index/(:segment)', 'No_access::index/$1');
$routes->get('no_access/index/(:segment)/(:segment)', 'No_access::index/$1/$2');

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

$routes->post('sales/quickFinish', 'Sales::postQuickFinish');
$routes->get('sales/getSaleItems', 'Sales::getSaleItems');
$routes->get('sales/editForm/(:num)', 'Sales::getEditForm/$1');
$routes->get('sales/edit/(:num)', 'Sales::getEdit/$1');
$routes->post('sales/save/(:num)', 'Sales::postSave/$1');
$routes->get('sales/deleteItem/(:num)', 'Sales::getDeleteItem/$1');
$routes->get('sales/deletePayment/(:num)', 'Sales::getDeletePayment/$1');
$routes->post('sales/editItem/(:num)', 'Sales::postEditItem/$1');
$routes->get('sales/manage', 'Sales::getManage');
$routes->post('sales/delete', 'Sales::delete');
$routes->get('sales/receipt/(:num)', 'Sales::getReceipt/$1');
$routes->get('sales/search', 'Sales::getSearch');
$routes->post('sales/add', 'Sales::postAdd');
$routes->get('sales/add', 'Sales::getIndex');
$routes->get('sales/itemSearch', 'Sales::itemSearch');
$routes->get('sales', 'Sales::getIndex');
$routes->get('sales/getPaymentSummary', 'Sales::getPaymentSummary');
$routes->post('sales/addDiversos', 'Sales::addDiversos');
$routes->get('printer/test', 'Printer::getTest');
$routes->get('printer/printReceipt/(:num)', 'Printer::getPrintReceipt/$1');
$routes->post('printer/quickPrint', 'Printer::postQuickPrint');
$routes->post('sales/suspend', 'Sales::postSuspend');
$routes->post('sales/cancel', 'Sales::postCancel');
$routes->post('sales/selectCustomer', 'Sales::postSelectCustomer');
