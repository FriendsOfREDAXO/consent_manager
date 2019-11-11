<?php

class rex_var_iwcc extends rex_var
{
    protected function getOutput()
    {
        $fragment = new rex_fragment();
        $fragment->setVar('forceCache', $this->getArg('forceCache', 0, false));
        $fragment->setVar('debug', $this->getArg('debug', 0, false));
        return self::quote($fragment->parse('iwcc_box.php'));
    }
}