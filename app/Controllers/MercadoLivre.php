<?php

namespace App\Controllers;

use App\Models\Item;

class MercadoLivre extends Secure_Controller
{
    private const CONNECT_TIMEOUT_S = 3;
    private const TIMEOUT_S = 20;

    public function __construct()
    {
        parent::__construct('items');
    }

    public function getMappings(): void
    {
        $skus = trim((string) ($this->request->getGet('skus') ?? ''));
        $this->forward('GET', '/v1/mercadolivre/mappings', $skus === '' ? [] : ['skus' => $skus]);
    }

    public function getCategoryAttributes(): void
    {
        $categoryId = strtoupper(trim((string) ($this->request->getGet('category_id') ?? '')));
        if (preg_match('/^MLB[0-9]{1,12}$/', $categoryId) !== 1) {
            $this->emit(400, ['error' => 'Categoria inválida.']);
            return;
        }

        $this->forward('GET', '/v1/mercadolivre/category-attributes', ['category_id' => $categoryId]);
    }

    public function postPause(): void
    {
        $this->listingStatusAction('paused');
    }

    public function postActivate(): void
    {
        $this->listingStatusAction('active');
    }

    public function postPublish(): void
    {
        $itemId = (int) ($this->request->getPost('item_id') ?? 0);
        $item = model(Item::class)->get_info($itemId);
        if ((int) $item->item_id <= 0) {
            $this->emit(404, ['error' => 'Produto não encontrado.']);
            return;
        }

        $dryRun = $this->requestedDryRun();
        $sku = trim((string) $item->item_number) !== '' ? trim((string) $item->item_number) : (string) $item->item_id;

        // Publishing twice creates a second live listing and there is no
        // unpublish: a stale badge in an open tab must not be able to do it.
        if (!$dryRun) {
            $existing = $this->inventoryCall('GET', '/v1/mercadolivre/mappings', ['skus' => $sku]);
            $decoded = json_decode($existing['body'], true);
            if ($existing['status'] === 200 && !empty($decoded['mappings'])) {
                $this->emit(409, [
                    'error'          => 'Este produto já está publicado no Mercado Livre.',
                    'external_id'    => $decoded['mappings'][0]['external_id'] ?? null,
                    'external_url'   => $decoded['mappings'][0]['external_url'] ?? null,
                ]);
                return;
            }
        }

        $payload = [
            'sku'            => $sku,
            'title'          => (string) $item->name,
            'price'          => (float) $item->unit_price,
            'cost_price'     => (float) $item->cost_price,
            'stock_quantity' => max(0, (int) round($this->totalStock($itemId))),
            'description'    => (string) $item->description,
            'pictures'       => $this->picturePaths($item->pic_filename),
            'dry_run'        => $dryRun,
        ];

        $attributes = $this->submittedAttributes();
        if ($attributes !== []) {
            $payload['attributes'] = $attributes;
        }

        $this->forward('POST', '/v1/mercadolivre/publish', [], $payload);
    }

    private function listingStatusAction(string $status): void
    {
        $sku = trim((string) ($this->request->getPost('sku') ?? ''));
        $externalId = trim((string) ($this->request->getPost('external_id') ?? ''));
        if ($sku === '' && $externalId === '') {
            $this->emit(400, ['error' => 'Informe o SKU ou o anúncio.']);
            return;
        }

        $payload = ['dry_run' => $this->requestedDryRun()];
        if ($sku !== '') {
            $payload['sku'] = $sku;
        } else {
            $payload['external_id'] = $externalId;
        }

        $endpoint = $status === 'paused' ? '/v1/mercadolivre/pause' : '/v1/mercadolivre/activate';
        $this->forward('POST', $endpoint, [], $payload);
    }

    private function requestedDryRun(): bool
    {
        $raw = $this->request->getPost('dry_run');
        if ($raw === null) {
            return true;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function totalStock(int $itemId): float
    {
        $row = db_connect()->table('item_quantities')
            ->selectSum('quantity')
            ->where('item_id', $itemId)
            ->get()
            ->getRow();

        return (float) ($row->quantity ?? 0);
    }

    private function picturePaths(?string $picFilename): array
    {
        if ($picFilename === null || trim($picFilename) === '') {
            return [];
        }

        // The file name comes from the database, never from the request: it is
        // passed to the inventory-service as a filesystem path it will upload.
        $base = FCPATH . 'uploads/item_pics/' . basename($picFilename);
        $extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        $candidates = $extension === ''
            ? array_map(static fn (string $ext): string => $base . '.' . $ext, ['jpg', 'jpeg', 'png', 'webp'])
            : [$base];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return [$candidate];
            }
        }

        return [];
    }

    private function submittedAttributes(): array
    {
        $submitted = $this->request->getPost('attributes');
        if (!is_array($submitted)) {
            return [];
        }

        $attributes = [];
        foreach ($submitted as $attribute) {
            if (!is_array($attribute)) {
                continue;
            }

            $id = strtoupper(trim((string) ($attribute['id'] ?? '')));
            $value = trim((string) ($attribute['value_name'] ?? ''));
            if (preg_match('/^[A-Z0-9_]{2,40}$/', $id) === 1 && $value !== '') {
                $attributes[] = ['id' => $id, 'value_name' => $value];
            }
        }

        return $attributes;
    }

    private function forward(string $method, string $path, array $query = [], ?array $payload = null): void
    {
        $result = $this->inventoryCall($method, $path, $query, $payload);
        if ($result['status'] === 0) {
            log_message('error', 'MercadoLivre: falha ao chamar ' . $path . ': ' . $result['error']);
            $this->emit(502, ['error' => 'Serviço de estoque indisponível.']);
            return;
        }

        $this->response->setStatusCode($result['status']);
        $this->response->setContentType('application/json');
        echo $result['body'];
    }

    private function inventoryCall(string $method, string $path, array $query = [], ?array $payload = null): array
    {
        $baseUrl = rtrim((string) env('inventory.baseURL', ''), '/');
        $apiKey = (string) env('inventory.apiKey', '');
        if ($baseUrl === '' || $apiKey === '') {
            log_message('error', 'MercadoLivre: inventory.baseURL ou inventory.apiKey ausente no .env');
            return ['status' => 0, 'body' => '', 'error' => 'integração não configurada no .env'];
        }

        $url = $baseUrl . $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = ['X-API-Key: ' . $apiKey, 'Accept: application/json'];
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_S,
            CURLOPT_TIMEOUT        => self::TIMEOUT_S,
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        if ($payload !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($body === false) {
            return ['status' => 0, 'body' => '', 'error' => $error];
        }

        return ['status' => $status, 'body' => (string) $body, 'error' => ''];
    }

    private function emit(int $status, array $payload): void
    {
        $this->response->setStatusCode($status);
        $this->response->setContentType('application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
