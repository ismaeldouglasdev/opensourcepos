<?php
namespace Config;
use CodeIgniter\Database\Config;
class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';
    public array $default = [
        'DSN'          => '',
        'hostname'     => env('database.default.hostname', 'localhost'),
        'username'     => env('database.default.username', 'admin'),
        'password'     => env('database.default.password', 'Arroz123@'),
        'database'     => env('database.default.database', 'ospos_test'),
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => 'ospos_',
        'pConnect'     => false,
        'DBDebug'      => (ENVIRONMENT !== 'production'),
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'    => [],
        'port'         => 3306,
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
