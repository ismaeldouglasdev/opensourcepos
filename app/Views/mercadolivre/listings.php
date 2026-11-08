<div class="content-wrapper">
    <section class="content-header">
        <div class="content-header-left">
            <h1>
                Mercado Livre
                <small>Anúncios publicados</small>
            </h1>
        </div>
        <div class="content-header-right">
            <a href="items" class="btn btn-default btn-sm">
                <span class="glyphicon glyphicon-arrow-left"></span> Voltar aos produtos
            </a>
        </div>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Anúncios no Mercado Livre (<span id="ml_count"><?= (int) $count ?></span>)</h3>
                <p class="text-muted pull-right">
                    Os anúncios abrem em uma nova janela. Apenas o dono da loja deve publicar ou alterar anúncios.
                </p>
            </div>
            <div class="box-body table-responsive">
                <?php if (empty($listings)): ?>
                    <div class="alert alert-info">
                        Nenhum produto está publicado no Mercado Livre ainda.
                    </div>
                <?php else: ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Foto</th>
                                <th>Produto</th>
                                <th style="width: 120px;">SKU</th>
                                <th style="width: 110px;">Preço</th>
                                <th style="width: 100px;">Estoque</th>
                                <th style="width: 110px;">Status</th>
                                <th style="width: 150px;">Anúncio</th>
                                <th style="width: 140px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <?php
                                $status = strtolower($listing['status'] ?? 'unknown');
                                $statusLabels = [
                                    'active' => ['Ativo', 'success'],
                                    'paused' => ['Pausado', 'warning'],
                                    'closed' => ['Encerrado', 'danger'],
                                    'inactive' => ['Inativo', 'default'],
                                    'under_review' => ['Em análise', 'info'],
                                ];
                                $statusLabel = $statusLabels[$status][0] ?? ucfirst($status);
                                $statusClass = $statusLabels[$status][1] ?? 'default';
                                $thumb = !empty($listing['pic_filename'])
                                    ? base_url('uploads/item_pics/' . $listing['pic_filename'])
                                    : base_url('assets/img/no_image_available.png');
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="<?= esc($thumb) ?>" alt="Foto"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                                             onerror="this.src='<?= base_url('assets/img/no_image_available.png') ?>'">
                                    </td>
                                    <td><?= esc($listing['name'] ?? '') ?></td>
                                    <td class="text-muted"><code><?= esc($listing['sku'] ?? '') ?></code></td>
                                    <td>R$ <?= number_format((float) ($listing['unit_price'] ?? 0), 2, ',', '.') ?></td>
                                    <td><?= (int) ($listing['stock_quantity'] ?? 0) ?></td>
                                    <td>
                                        <span class="label label-<?= $statusClass ?>"><?= esc($statusLabel) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($listing['external_url'])): ?>
                                            <a href="<?= esc($listing['external_url']) ?>" target="_blank"
                                               class="btn btn-primary btn-xs"
                                               onclick="return confirm('Isso abrirá o Mercado Livre em uma nova aba. Deseja continuar?');">
                                                <span class="glyphicon glyphicon-share"></span> Abrir anúncio
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status === 'active'): ?>
                                            <form method="post" action="mercadolivre/pause"
                                                  style="display:inline"
                                              onsubmit="return confirm('Tem certeza que deseja PAUSAR o anúncio "<?= esc($listing['sku'] ?? '') ?>"?')">
                                                <input type="hidden" name="sku" value="<?= esc($listing['sku'] ?? '') ?>">
                                                <input type="hidden" name="external_id" value="<?= esc($listing['external_id'] ?? '') ?>">
                                                <input type="hidden" name="dry_run" value="0">
                                                <button type="submit" class="btn btn-warning btn-xs"
                                                        title="Pausar anúncio">
                                                    <span class="glyphicon glyphicon-pause"></span> Pausar
                                                </button>
                                            </form>
                                        <?php elseif ($status === 'paused'): ?>
                                            <form method="post" action="mercadolivre/activate"
                                                  style="display:inline"
                                              onsubmit="return confirm('Tem certeza que deseja REATIVAR o anúncio "<?= esc($listing['sku'] ?? '') ?>"?')">
                                                <input type="hidden" name="sku" value="<?= esc($listing['sku'] ?? '') ?>">
                                                <input type="hidden" name="external_id" value="<?= esc($listing['external_id'] ?? '') ?>">
                                                <input type="hidden" name="dry_run" value="0">
                                                <button type="submit" class="btn btn-success btn-xs"
                                                        title="Reativar anúncio">
                                                    <span class="glyphicon glyphicon-play"></span> Reativar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<style>
    .ml-listings-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    .ml-listings-subtitle {
        font-size: 13px;
        color: #777;
    }
</style>

<script>
    $(function() {
        // Confirmação extra para ações destrutivas (pausar/reativar)
        $('form[action*="mercadolivre/pause"], form[action*="mercadolivre/activate"]').on('submit', function(e) {
            if (!confirm('Tem certeza que deseja fazer isso?')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
