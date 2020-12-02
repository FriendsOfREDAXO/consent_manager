<?php
$addon = rex_addon::get('consent_manager');
$addon->includeFile(__DIR__.'/install.php');
$this->setConfig('forceCache', true);
