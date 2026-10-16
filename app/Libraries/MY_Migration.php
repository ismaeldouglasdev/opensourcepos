<?php

namespace App\Libraries;

use CodeIgniter\Database\MigrationRunner;
use Config\Database;
use Config\Services;
use stdClass;

class MY_Migration extends MigrationRunner
{
    /**
     * @return bool
     */
    public function is_latest(): bool
    {
        $latest_version = $this->get_latest_migration();
        $current_version = $this->get_current_version();

        return $latest_version === $current_version;
    }

    /**
     * Gets the latest migration version available in the filesystem.
     *
     * findMigrations() walks every autoloader namespace and, for each migration
     * file, reads and tokenizes its contents just to extract the class name.
     * On this host that is ~5 ms of filesystem work on every request, and it is
     * re-done for the same answer because the set of migrations only changes
     * when a file is added, removed or renamed.
     *
     * The result is cached and invalidated by a fingerprint of the migrations
     * directory mtime and the composer autoload map mtime (the latter changes
     * when a namespace providing migrations is installed). The version comes
     * from the filename, so file *contents* never change it.
     *
     * If the cache is unavailable or stale the value is recomputed, so a stale
     * entry can only ever report an older latest version — which makes
     * is_latest() return false and destroys the session (the safe direction).
     *
     * @return int
     */
    public function get_latest_migration(): int
    {
        $cache_key   = 'migration_latest_' . md5(ENVIRONMENT . '|' . $this->group);
        $fingerprint = $this->get_migrations_fingerprint();

        $cache  = Services::cache();
        $cached = $cache->get($cache_key);

        if (is_array($cached) && ($cached['fingerprint'] ?? null) === $fingerprint) {
            return (int) $cached['version'];
        }

        $migrations = $this->findMigrations();
        $version    = (int) basename(end($migrations)->version);

        $cache->save($cache_key, ['fingerprint' => $fingerprint, 'version' => $version], 3600);

        return $version;
    }

    /**
     * Fingerprint that changes when the available migrations change: the
     * migrations directory mtime (file added/removed/renamed) and the composer
     * autoload map mtime (new namespace installed).
     *
     * @return string
     */
    private function get_migrations_fingerprint(): string
    {
        $migrations_dir = APPPATH . 'Database/Migrations';
        $autoload_map   = ROOTPATH . 'vendor/composer/autoload_psr4.php';

        return md5(
            (@filemtime($migrations_dir) ?: 0) . '|' .
            (@filemtime($autoload_map) ?: 0)
        );
    }

    /**
     * Gets the database version number
     *
     * @return int The version number of the last successfully run database migration.
     */
    public static function get_current_version(): int
    {
        $db = Database::connect();
        if ($db->tableExists('migrations')) {
            $builder = $db->table('migrations');
            $builder->select('version')->orderBy('version', 'DESC')->limit(1);
            return $builder->get()->getRow()->version;
        }

        return 0;
    }

    /**
     * @return void
     */
    public function migrate_to_ci4(): void
    {
        $ci3_migrations_version = $this->ci3_migrations_exists();
        if ($ci3_migrations_version) {
            $this->migrate_table($ci3_migrations_version);
        }
    }

    /**
     * Checks to see if a ci3 version of the migrations table exists
     *
     * @return bool|string The version number of the last CI3 migration to run or false if the table is CI4 or doesn't exist
     */
    private function ci3_migrations_exists(): bool|string
    {
        if ($this->db->tableExists('migrations') && !$this->db->fieldExists('id', 'migrations')) {
            $builder = $this->db->table('migrations');
            $builder->select('version');
            return $builder->get()->getRow()->version;
        }

        return false;
    }

    /**
     * @param string $ci3_migrations_version
     * @return void
     */
    private function migrate_table(string $ci3_migrations_version): void
    {
        $this->convert_table();

        $available_migrations = $this->get_available_migrations();

        foreach ($available_migrations as $version => $path) {
            if ($version > (int)$ci3_migrations_version) {
                break;
            }

            $migration = new stdClass();
            $migration->version = $version;
            $migration->class = $path;
            $migration->namespace = 'App';

            $this->addHistory($migration, 1);
        }
    }

    /**
     * @return void
     */
    public function up(): void
    {
        // TODO: Implement up() method.
    }

    /**
     * @return void
     */
    public function down(): void
    {
        // TODO: Implement down() method.
    }

    /**
     * @return array
     */
    private function get_available_migrations(): array
    {
        $migrations = $this->findMigrations();
        $exploded_migrations = [];

        foreach ($migrations as $migration) {
            $version = substr($migration->uid, 0, 14);
            $path = substr($migration->uid, 14);

            $exploded_migrations[$version] = $path;
        }

        ksort($exploded_migrations);

        return $exploded_migrations;
    }

    /**
     * Converts the CI3 migrations database to CI4
     * @return void
     */
    public function convert_table(): void
    {
        $forge = Database::forge();
        $forge->dropTable('migrations');

        $this->ensureTable();
    }
}
