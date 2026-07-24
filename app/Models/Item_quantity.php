<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

/**
 * Item_quantity class
 */
class Item_quantity extends Model
{
    protected $table = 'item_quantities';
    protected $primaryKey = 'item_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'quantity',
        'stock_status'
    ];

    const STOCK_OK = 0;
    const STOCK_ZERADO = 1;
    const STOCK_IRREGULAR = 2;

    public static $stock_status_labels = [
        0 => '',
        1 => 'ZERADO',
        2 => 'IRREGULAR',
    ];

    protected $item_id;
    protected $location_id;
    protected $quantity;

    /**
     * @param int $item_id
     * @param int $location_id
     * @return bool
     */
    public function exists(int $item_id, int $location_id): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);

        return ($builder->get()->getNumRows() == 1);    // TODO: ===
    }

    /**
     * @param array $location_detail
     * @param int $item_id
     * @param int $location_id
     * @return bool
     */
    public function save_value(array $location_detail, int $item_id, int $location_id): bool
    {
        if (!$this->exists($item_id, $location_id)) {
            $builder = $this->db->table('item_quantities');
            return $builder->insert($location_detail);
        }

        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);

        return $builder->update($location_detail);
    }

    /**
     * @param int $item_id
     * @param int $location_id
     * @return array|Item_quantity|stdClass|null
     */
    public function get_item_quantity(int $item_id, int $location_id): array|Item_quantity|StdClass|null
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);
        $result = $builder->get()->getRow();

        if (empty($result)) {
            // Get empty base parent object, as $item_id is NOT an item
            $result = model(Item_quantity::class);

            // Get all the fields from items table (TODO: to be reviewed)
            foreach ($this->db->getFieldNames('item_quantities') as $field) {
                $result->$field = '';
            }

            $result->quantity = 0;
        }

        return $result;
    }

    /**
     * changes to quantity of an item according to the given amount.
     * if $quantity_change is negative, it will be subtracted,
     * if it is positive, it will be added to the current quantity
     */
    public function change_quantity(int $item_id, int $location_id, int $quantity_change): bool
    {
        $quantity_old = $this->get_item_quantity($item_id, $location_id);
        $quantity_new = $quantity_old->quantity + $quantity_change;

        // Determine stock status after change
        if ($quantity_new < 0) {
            $quantity_new = 0;
            $new_status = self::STOCK_IRREGULAR;
        } elseif ($quantity_new == 0) {
            $new_status = self::STOCK_ZERADO;
        } else {
            $new_status = self::STOCK_OK;
        }

        $location_detail = [
            'item_id'       => $item_id,
            'location_id'   => $location_id,
            'quantity'      => $quantity_new,
            'stock_status'  => $new_status
        ];

        return $this->save_value($location_detail, $item_id, $location_id);
    }

    /**
     * Set to 0 all quantity in the given item
     */
    public function reset_quantity(int $item_id): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);

        return $builder->update(['quantity' => 0]);
    }

    /**
     * Set to 0 all quantity in the given list of items
     */
    public function reset_quantity_list(array $item_ids): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->whereIn('item_id', $item_ids);

        return $builder->update(['quantity' => 0]);
    }

    /**
     * Get stock status label for display
     */
    public static function get_stock_status_label(int $status): string
    {
        return self::$stock_status_labels[$status] ?? '';
    }

    /**
     * Update stock status for an item at a location
     */
    public function set_stock_status(int $item_id, int $location_id, int $status): bool
    {
        $builder = $this->db->table('item_quantities');
        $builder->where('item_id', $item_id);
        $builder->where('location_id', $location_id);

        return $builder->update(['stock_status' => $status]);
    }

    /**
     * Get count of items with stock alerts (ZERADO or IRREGULAR)
     */
    public function get_stock_alert_count(): int
    {
        $builder = $this->db->table('item_quantities');
        $builder->join('items', 'items.item_id = item_quantities.item_id');
        $builder->where('item_quantities.stock_status IN', [self::STOCK_ZERADO, self::STOCK_IRREGULAR]);
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', HAS_STOCK);

        return (int)$builder->countAllResults();
    }
}
