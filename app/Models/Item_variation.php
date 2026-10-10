<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Parent/child link for items that offer size (or any attribute) variations.
 *
 * A variation parent keeps the barcode and the name, and is what the operator
 * scans, but is never sold on its own (its stock is forced to zero). Each child
 * row in `ospos_items` carries the real stock for one attribute value, so the
 * sale decrements the child's quantity and reports the child's name/price.
 */
class Item_variation extends Model
{
    protected $table = 'item_variations';
    protected $primaryKey = 'item_variation_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['item_id', 'variation_item_id', 'attribute_name', 'attribute_value', 'position'];

    /**
     * Variations of a parent, with each child's stock at the given location.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_variations_for_parent(int $parent_id, int $location_id): array
    {
        $builder = $this->db->table('item_variations AS v');
        $builder->select('v.variation_item_id AS item_id');
        $builder->select('v.attribute_name, v.attribute_value, v.position');
        $builder->select('i.name, i.item_number, i.unit_price, i.pic_filename');
        $builder->select('COALESCE(q.quantity, 0) AS stock_qty');
        $builder->join('items AS i', 'i.item_id = v.variation_item_id');
        $builder->join('item_quantities AS q', 'q.item_id = v.variation_item_id AND q.location_id = ' . (int) $location_id, 'left');
        $builder->where('v.item_id', $parent_id);
        $builder->where('i.deleted', 0);
        $builder->orderBy('v.position', 'ASC');

        return $builder->get()->getResultArray();
    }
}
