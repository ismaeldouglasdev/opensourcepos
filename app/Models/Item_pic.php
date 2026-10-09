<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Extra (non-primary) photos of an item.
 *
 * The item's main photo keeps living in `ospos_items.pic_filename` for backward
 * compatibility with the cart, grid, receipts, store sync and inventory-service.
 * This table only stores the ADDITIONAL photos (gallery).
 */
class Item_pic extends Model
{
    protected $table = 'item_pics';
    protected $primaryKey = 'item_pic_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = ['item_id', 'filename', 'sort_order'];

    /**
     * Returns the extra photos of an item, ordered for display.
     *
     * @return array
     */
    public function get_for_item(int $item_id): array
    {
        return $this->where('item_id', $item_id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('item_pic_id', 'ASC')
            ->findAll();
    }

    public function get_info(int $item_pic_id, int $item_id): ?object
    {
        return $this->where('item_pic_id', $item_pic_id)
            ->where('item_id', $item_id)
            ->first();
    }

    public function next_sort_order(int $item_id): int
    {
        $query = $this->selectMax('sort_order')->where('item_id', $item_id)->get();
        $row = $query->getRow();
        return ($row !== null && $row->sort_order !== null) ? intval($row->sort_order) + 1 : 1;
    }

    public function add(int $item_id, string $filename): int
    {
        $data = [
            'item_id'    => $item_id,
            'filename'   => $filename,
            'sort_order' => $this->next_sort_order($item_id)
        ];

        return intval($this->insert($data, true));
    }

    public function remove(int $item_pic_id, int $item_id): bool
    {
        return $this->where('item_pic_id', $item_pic_id)
            ->where('item_id', $item_id)
            ->delete();
    }
}