<?php
$addon = rex_addon::get('iwcc');
$addon->includeFile(__DIR__.'/install.php');
$this->setConfig('forceCache', true);
