<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Module;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use Config\OSPOS;
use Config\Services;

/**
 * Controllers that are considered secure extend Secure_Controller, optionally a $module_id can
 * be set to also check if a user can access a particular module in the system.
 *
 * @property employee employee
 * @property module module
 * @property array global_view_data
 * @property session session
 *
 */
class Secure_Controller extends BaseController
{
    public array $global_view_data;
    protected Employee $employee;
    protected Module $module;
    protected Session $session;

    /**
     * Cache do flag de Modo Simples dentro do request atual.
     *
     * @var bool|null null = ainda nao consultado nesta request
     */
    protected ?bool $simpleModeCache = null;

    /**
     * @param string $module_id
     * @param string|null $submodule_id
     * @param string|null $menu_group
     */
    public function __construct(string $module_id = '', ?string $submodule_id = null, ?string $menu_group = null)
    {
        $this->employee = model(Employee::class);
        $this->module = model(Module::class);
        $config = config(OSPOS::class)->settings;
        $validation = Services::validation();

        if (!$this->employee->is_logged_in()) {
            header("Location:" . base_url('login'));
            exit();
        }

        $logged_in_employee_info = $this->employee->get_logged_in_employee_info();
        if (
            !$this->employee->has_module_grant($module_id, $logged_in_employee_info->person_id)
            || (isset($submodule_id) && !$this->employee->has_module_grant($submodule_id, $logged_in_employee_info->person_id))
        ) {
            header("Location:" . base_url("no_access/index/$module_id/$submodule_id"));
            exit();
        }

        // Load up global global_view_data visible to all the loaded views
        $this->session = session();
        if ($menu_group == null) {
            $menu_group = $this->session->get('menu_group');
        } else {
            $this->session->set('menu_group', $menu_group);
        }

        $allowed_modules = $menu_group == 'home'
            ? $this->module->get_allowed_home_modules($logged_in_employee_info->person_id)
            : $this->module->get_allowed_office_modules($logged_in_employee_info->person_id);

        $this->global_view_data = [];
        foreach ($allowed_modules->getResult() as $module) {
            $this->global_view_data['allowed_modules'][] = $module;
        }

        $this->global_view_data += [
            'user_info'       => $logged_in_employee_info,
            'controller_name' => $module_id,
            'config'          => $config,
            // Tarefa 5 do plano simplificar-fluxo-venda-pdv: o header e
            // renderizado em todas as paginas, entao e daqui que o botao de
            // troca rapida do topo descobre em que modo o employee esta.
            'simple_mode'     => $this->isSimpleMode()
        ];
        view('viewData', $this->global_view_data);
    }

    /**
     * O employee logado esta no Modo Simples?
     *
     * Tarefa 3 do plano simplificar-fluxo-venda-pdv. O modo e porfuncionario,
     * nao por loja: o dono atende sozinho no mesmo terminal, entao um flag
     * global obrigaria a ligar e desligar todo dia.
     *
     * Seguranca: qualquer falha (migration nao aplicada, banco indisponivel)
     * devolve false, que e o Modo Completo. Falhar para o lado seguro importa
     * aqui: um erro nao pode trancar quem esta vendendo no caixa.
     *
     * @return bool true = Modo Simples, false = Modo Completo
     */
    protected function isSimpleMode(): bool
    {
        // cache por request: varias views e fragmentos AJAX perguntam no mesmo
        // request, e a resposta nao muda no meio de uma request.
        if (isset($this->simpleModeCache)) {
            return $this->simpleModeCache;
        }

        try {
            $person_id = (int) $this->employee->get_logged_in_employee_info()->person_id;
            $this->simpleModeCache = $this->employee->get_simple_mode($person_id) === 1;
        } catch (\Throwable $e) {
            $this->simpleModeCache = false;
        }

        return $this->simpleModeCache;
    }


    public function sanitizeSortColumn($headers, $field, $default): string
    {
        return $field != null && in_array($field, array_keys(array_merge(...$headers))) ? $field : $default;
    }

    /**
     * AJAX function used to confirm whether values sent in the request are numeric
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getCheckNumeric(): ResponseInterface
    {
        foreach ($this->request->getGet() as $value) {
            if (parse_decimals($value) === false) {
                return $this->response->setJSON('false');
            }
        }

        return $this->response->setJSON('true');
    }

    /**
     * @param $key
     * @return mixed|void
     */
    public function getConfig($key)
    {
        if (isset($config[$key])) {
            return $config[$key];
        }
    }

    /**
     * @return false
     */
    public function getIndex()
    {
        return false;
    }

    /**
     * @return false
     */
    public function getSearch()
    {
        return false;
    }

    /**
     * @return false
     */
    public function suggest_search()
    {
        return false;
    }

    /**
     * @param int $data_item_id
     * @return false
     */
    public function getView(int $data_item_id = -1)
    {
        return false;
    }

    /**
     * @param int $data_item_id
     * @return false
     */
    public function postSave(int $data_item_id = -1)
    {
        return false;
    }

    /**
     * @return false
     */
    public function postDelete()
    {
        return false;
    }
}
