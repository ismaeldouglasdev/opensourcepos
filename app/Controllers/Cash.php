<?php

namespace App\Controllers;

use App\Models\Cash_flow;
use Config\OSPOS;

/**
 * Movimentações de caixa (sangria / suprimento)
 */
class Cash extends Secure_Controller
{
    private Cash_flow $cash_flow;

    public function __construct()
    {
        parent::__construct('cash');

        $this->cash_flow = model(Cash_flow::class);
    }

    /**
     * @return void
     */
    public function getIndex(): void
    {
        $data['table_headers'] = get_cash_flow_manage_table_headers();
        $data['today_summary'] = $this->cash_flow->get_today_summary();

        echo view('cash/manage', $data);
    }

    /**
     * @return void
     */
    public function getSearch(): void
    {
        $search = $this->request->getGet('search') ?? '';
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(cash_flow_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'cash_flow_id');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $filters = [
            'start_date' => $this->request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'end_date'   => $this->request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'is_deleted' => false
        ];

        $request_filters = array_fill_keys($this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [], true);
        $filters = array_merge($filters, $request_filters);
        $cash_flows = $this->cash_flow->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->cash_flow->get_found_rows($search, $filters);
        $data_rows = [];

        foreach ($cash_flows->getResult() as $cash_flow) {
            $data_rows[] = get_cash_flow_data_row($cash_flow);
        }

        $this->response->setContentType('application/json');
        echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * @param int $row_id
     * @return void
     */
    public function getRow(int $row_id): void
    {
        $cash_flow_info = $this->cash_flow->get_info($row_id);
        $data_row = get_cash_flow_data_row($cash_flow_info);

        $this->response->setContentType('application/json');
        echo json_encode($data_row);
    }

    /**
     * @param int $cash_flow_id
     * @return void
     */
    public function getView(int $cash_flow_id = NEW_ENTRY): void
    {
        $data['employees'] = [];
        foreach ($this->employee->get_all()->getResult() as $employee) {
            foreach (get_object_vars($employee) as $property => $value) {
                $employee->$property = $value;
            }

            $data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
        }

        $cash_flow_info = $this->cash_flow->get_info($cash_flow_id);

        if ($cash_flow_info->cash_flow_id == NEW_ENTRY) {
            $cash_flow_info->created_at = date('Y-m-d H:i:s');
            $cash_flow_info->employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        }

        $data['cash_flow_info'] = $cash_flow_info;

        echo view('cash/form', $data);
    }

    /**
     * @param int $cash_flow_id
     * @return void
     */
    public function postSave(int $cash_flow_id = NEW_ENTRY): void
    {
        $amount = parse_decimals($this->request->getPost('amount'));

        if ($amount <= 0) {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => false, 'message' => lang('Cash.amount_required'), 'id' => NEW_ENTRY]);
            return;
        }

        $cash_flow_data = [
            'employee_id' => $this->request->getPost('employee_id', FILTER_SANITIZE_NUMBER_INT),
            'type'        => $this->request->getPost('type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'amount'      => $amount,
            'note'        => $this->request->getPost('note', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'deleted'     => $this->request->getPost('deleted') != null
        ];

        if (!in_array($cash_flow_data['type'], [Cash_flow::TYPE_SANGRIA, Cash_flow::TYPE_SUPRIMENTO])) {
            $cash_flow_data['type'] = Cash_flow::TYPE_SANGRIA;
        }

        if ($this->cash_flow->save_value($cash_flow_data, $cash_flow_id)) {
            $this->response->setContentType('application/json');
            if ($cash_flow_id == NEW_ENTRY) {
                echo json_encode(['success' => true, 'message' => lang('Cash.successful_adding'), 'id' => $cash_flow_data['cash_flow_id']]);
            } else {
                echo json_encode(['success' => true, 'message' => lang('Cash.successful_updating'), 'id' => $cash_flow_id]);
            }
        } else {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => false, 'message' => lang('Cash.error_adding_updating'), 'id' => NEW_ENTRY]);
        }
    }

    /**
     * @return void
     */
    public function postDelete(): void
    {
        $cash_flows_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($this->cash_flow->delete_list($cash_flows_to_delete)) {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => true, 'message' => lang('Cash.successful_deleted') . ' ' . count($cash_flows_to_delete) . ' ' . lang('Cash.one_or_multiple'), 'ids' => $cash_flows_to_delete]);
        } else {
            $this->response->setContentType('application/json');
            echo json_encode(['success' => false, 'message' => lang('Cash.cannot_be_deleted'), 'ids' => $cash_flows_to_delete]);
        }
    }
}
