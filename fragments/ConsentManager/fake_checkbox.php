<?php

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$items = $this->getVar('items', []);

foreach ($items as $item) {
    $checked = !empty($item['checked']) ? ' checked="checked"' : '';
    $label = (string) ($item['label'] ?? '');
    ?>
    <div class="checkbox"><label class="control-label"><input type="checkbox" disabled="disabled"<?= $checked ?>><?= rex_escape($label) ?></label></div>
    <?php
}
