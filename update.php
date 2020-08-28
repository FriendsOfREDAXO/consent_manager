<?php
$addon = rex_addon::get('iwcc');
$addon->includeFile(__DIR__.'/install.php');
iwcc_cache::forceWrite();
