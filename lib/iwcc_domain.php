<?php
class iwcc_domain {

    public static function checkYrewrite () {
        $yrewrite = rex_addon::get('yrewrite');
        return
            rex_addon::exists('yrewrite') &&
            //rex_string::versionCompare($yrewrite->getVersion(), self::YREWRITE_VERSION_MIN, '>=') &&
            $yrewrite->isInstalled() &&
            $yrewrite->isAvailable();
    }

}