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
     * Resolve a barcode to the umbrella that owns it, dead or alive.
     *
     * A family barcode may live on a deactivated placeholder row (the old
     * "[SEM COR]" umbrella): the code must still open the picker, otherwise
     * scanning it adds a zero-stock item or falls through to "unknown code".
     * Resolution order: live item_number, live extra code, then the same two
     * sources ignoring the deleted flag. Lowest id wins, matching the keeper
     * convention used elsewhere.
     *
     * @return array<int, array<string, mixed>> umbrella info, or []
     */
    public function resolve_umbrella(string $code): array
    {
        $code = trim($code);

        if ($code === '') {
            return [];
        }

        $items = $this->db->DBPrefix . 'items';

        // item_number first (primary barcode), then the extra codes table.
        $builder = $this->db->table($items);
        $builder->select('item_id, name, item_number, pic_filename, deleted');
        $builder->where('item_number', $code);
        $builder->orderBy('item_id', 'ASC');
        $builder->limit(1);
        $row = $builder->get()->getRowArray();

        if ($row === null) {
            $codes = $this->db->DBPrefix . 'item_codes';
            $builder = $this->db->table($items . ' AS i');
            $builder->select('i.item_id, i.name, i.item_number, i.pic_filename, i.deleted');
            $builder->join($codes . ' AS c', 'c.item_id = i.item_id');
            $builder->where('c.code', $code);
            $builder->orderBy('i.item_id', 'ASC');
            $builder->limit(1);
            $row = $builder->get()->getRowArray();
        }

        // A plain numeric barcode can be the umbrella's own id (the operator
        // typing it), which is how the existing funnel treats it. Applies to
        // both live and dead rows: a dead umbrella is still a valid pick target.
        if ($row === null && ctype_digit($code) && !str_starts_with($code, '0')) {
            $builder = $this->db->table($items);
            $builder->select('item_id, name, item_number, pic_filename, deleted');
            $builder->where('item_id', (int) $code);
            $row = $builder->get()->getRowArray();
        }

        if ($row === null) {
            return [];
        }

        return [
            'item_id'     => (int) $row['item_id'],
            'name'        => (string) $row['name'],
            'item_number' => $row['item_number'],
            'deleted'     => (bool) $row['deleted'],
        ];
    }

    /**
     * Every variation under an umbrella, tagged with its group.
     *
     * Serves both shapes the catalogue has today with one query:
     *   - one level (color -> size): every row carries the same group_id;
     *   - two levels (family -> color -> size): group_id tells which color
     *     each size belongs to, so the client can nest without guessing.
     *
     * The walk is recursive because a two-level tree is exactly the case that
     * matters for the family barcode: stopping after one level would show the
     * four colors — all with zero stock, since the color row is only a label —
     * and no sellable size at all.
     *
     * Only leaves come back: an umbrella has no stock of its own, so a picker
     * that lists it would offer a product that can never be sold. group_id is
     * the immediate parent (the color), which is what the client groups on.
     *
     * $parent_id may be any node: a leaf groups its own children, a root
     * returns the whole tree. A node with no children returns [] — the caller
     * then treats it as a plain item instead of opening an empty picker.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_variation_groups(int $parent_id, int $location_id): array
    {
        $items    = $this->db->DBPrefix . 'items';
        $quantity = $this->db->DBPrefix . 'item_quantities';

        $sql = "
            WITH RECURSIVE tree AS (
                SELECT variation_item_id AS leaf_id, item_id AS parent_id, 1 AS depth
                FROM {$this->db->DBPrefix}item_variations
                WHERE item_id = {$parent_id}
                UNION ALL
                SELECT v.variation_item_id, v.item_id, t.depth + 1
                FROM {$this->db->DBPrefix}item_variations v
                JOIN tree t ON v.item_id = t.leaf_id
                WHERE t.depth < 5
            )
            SELECT t.parent_id AS group_id, t.leaf_id AS item_id, t.depth,
                   v.attribute_name, v.attribute_value, v.position,
                   i.name, i.item_number, i.unit_price, i.pic_filename,
                   g.name AS group_name,
                   g.pic_filename AS group_pic,
                   COALESCE(q.quantity, 0) AS stock_qty
            FROM tree t
            JOIN {$this->db->DBPrefix}item_variations v
              ON v.item_id = t.parent_id AND v.variation_item_id = t.leaf_id
            JOIN {$items} i ON i.item_id = t.leaf_id
            LEFT JOIN {$items} g ON g.item_id = t.parent_id
            LEFT JOIN {$quantity} q
              ON q.item_id = t.leaf_id AND q.location_id = {$location_id}
            WHERE i.deleted = 0
              AND NOT EXISTS (
                  SELECT 1 FROM {$this->db->DBPrefix}item_variations c
                  WHERE c.item_id = t.leaf_id
              )
            ORDER BY t.depth ASC, t.parent_id ASC, v.position ASC, v.item_variation_id ASC
        ";

        $rows = $this->db->query($sql)->getResultArray();

        // The recursive CTE needs MySQL 8+/MariaDB 10.2+. A query error would
        // otherwise bubble into the sale screen, so it degrades to "no
        // variations" (the caller then sells the item as a plain product).
        if ($this->db->error()['code'] ?? 0) {
            return [];
        }

        return $rows;
    }

    /**
     * Variations of a parent, with each child's stock at the given location.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_variations_for_parent(int $parent_id, int $location_id): array
    {
        $item_quantities = $this->db->DBPrefix . 'item_quantities';
        $builder         = $this->db->table('ospos_item_variations AS v');
        $builder->select('v.variation_item_id AS item_id');
        $builder->select('v.attribute_name, v.attribute_value, v.position');
        $builder->select('i.name, i.item_number, i.unit_price, i.pic_filename');
        $builder->select('COALESCE(q.quantity, 0) AS stock_qty');
        $builder->join('ospos_items AS i', 'i.item_id = v.variation_item_id');
        $builder->join($item_quantities . ' AS q', 'q.item_id = v.variation_item_id AND q.location_id = ' . (int) $location_id, 'left');
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
        $item_quantities = $this->db->DBPrefix . 'item_quantities';
        $builder         = $this->db->table('ospos_item_variations AS v');
        $builder->select('v.item_variation_id, v.variation_item_id, v.attribute_name, v.attribute_value, v.position');
        $builder->select('i.name, i.item_number, i.unit_price, i.pic_filename');
        $builder->select('COALESCE((SELECT SUM(q.quantity) FROM ' . $item_quantities . ' AS q WHERE q.item_id = v.variation_item_id), 0) AS stock_qty');
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
