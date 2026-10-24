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
    protected $table = 'ospos_item_variations';
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
        $builder = $this->db->table('ospos_item_variations AS v');
        $builder->select('v.variation_item_id AS item_id');
        $builder->select('v.attribute_name, v.attribute_value, v.position');
        $builder->select('i.name, i.item_number, i.unit_price, i.pic_filename');
        $builder->select('COALESCE(q.quantity, 0) AS stock_qty');
        $builder->join('ospos_items AS i', 'i.item_id = v.variation_item_id');
        $builder->join('ospos_item_quantities AS q', 'q.item_id = v.variation_item_id AND q.location_id = ' . (int) $location_id, 'left');
        $builder->where('v.item_id', $parent_id);
        $builder->where('i.deleted', 0);
        $builder->orderBy('v.position', 'ASC');

        $result = $builder->get();
        if ($result === false) {
            return [];
        }

        return $result->getResultArray();
    }

    /**
     * Children already linked to a parent, each with its own stock.
     *
     * Same shape as get_variations_for_parent(), but without requiring a
     * location id: the admin panel shows the child's own row (name, barcode,
     * price) while the sale modal is the one that needs the per-location stock.
     * Stock here is the sum over every location, which is what the umbrella
     * total shows — the umbrella itself is a derived view, never edited.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_children_for_parent(int $parent_id): array
    {
        $builder = $this->db->table('ospos_item_variations AS v');
        $builder->select('v.item_variation_id, v.variation_item_id, v.attribute_name, v.attribute_value, v.position');
        $builder->select('i.name, i.item_number, i.unit_price, i.pic_filename');
        $builder->select('COALESCE((SELECT SUM(q.quantity) FROM ospos_item_quantities AS q WHERE q.item_id = v.variation_item_id), 0) AS stock_qty');
        $builder->join('ospos_items AS i', 'i.item_id = v.variation_item_id');
        $builder->where('v.item_id', $parent_id);
        $builder->where('i.deleted', 0);
        $builder->orderBy('v.position', 'ASC');
        $builder->orderBy('v.item_variation_id', 'ASC');

        $result = $builder->get();
        if ($result === false) {
            return [];
        }

        return $result->getResultArray();
    }

    /**
     * Link a child item to a parent (umbrella) row.
     *
     * Two guards run before the insert: the same item cannot be its own child,
     * and a child cannot be claimed by a second umbrella. The second guard is
     * the reason this is not a plain insert — the DB has a UNIQUE on
     * variation_item_id, so a naive insert would surface as a raw SQL error
     * instead of a message the operator can act on.
     *
     * Returns the new row id, or false when the link is refused.
     */
    public function add_variation(
        int $parent_id,
        int $child_item_id,
        string $attribute_name = 'PRODUTO',
        string $attribute_value = '',
        ?int $position = null
    ): int|false {
        if ($parent_id <= 0 || $child_item_id <= 0 || $parent_id === $child_item_id) {
            return false;
        }

        // A child belongs to at most one umbrella. Re-linking the same pair is
        // a no-op success so a double click does not look like a failure.
        $existing = $this->db->table('ospos_item_variations')
            ->select('item_variation_id, item_id')
            ->where('variation_item_id', $child_item_id)
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            return ((int) $existing['item_id'] === $parent_id)
                ? (int) $existing['item_variation_id']
                : false;
        }

        // Both ends must be live items; linking a deleted row would create an
        // umbrella whose child never renders (the read path filters deleted).
        $alive = $this->db->table('ospos_items')
            ->select('item_id')
            ->whereIn('item_id', [$parent_id, $child_item_id])
            ->where('deleted', 0)
            ->countAllResults();

        if ($alive !== 2) {
            return false;
        }

        if ($position === null) {
            $position = (int) $this->db->table('ospos_item_variations')
                ->selectMax('position', 'max_pos')
                ->where('item_id', $parent_id)
                ->get()
                ->getRowArray()['max_pos'] ?? 0;
            $position++;
        }

        $builder = $this->db->table('ospos_item_variations');
        $ok = $builder->insert([
            'item_id'          => $parent_id,
            'variation_item_id' => $child_item_id,
            'attribute_name'   => $attribute_name !== '' ? $attribute_name : 'PRODUTO',
            'attribute_value'  => $attribute_value,
            'position'         => $position,
        ]);

        return $ok ? (int) $this->db->insertID() : false;
    }

    /**
     * Remove the umbrella link for a child.
     *
     * Only the link is deleted. The child item row, its stock and its history
     * survive untouched — ungrouping is never a delete, so a misclick cannot
     * destroy a product (the merge flow follows the same rule: hide, never drop).
     */
    public function remove_variation(int $parent_id, int $child_item_id): bool
    {
        return $this->db->table('ospos_item_variations')
            ->where('item_id', $parent_id)
            ->where('variation_item_id', $child_item_id)
            ->delete();
    }

    /**
     * Umbrella that owns a child, if any.
     *
     * Used by the admin panel to refuse adding an item that already lives in
     * another umbrella, and to tell the operator which one it is.
     *
     * @return array{item_id: int, variation_item_id: int}|null
     */
    public function get_parent_for_child(int $child_item_id): ?array
    {
        $row = $this->db->table('ospos_item_variations')
            ->select('item_id, variation_item_id')
            ->where('variation_item_id', $child_item_id)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        return [
            'item_id'           => (int) $row['item_id'],
            'variation_item_id' => (int) $row['variation_item_id'],
        ];
    }
}
