<?php

class consent_manager_rex_form
{
    /**
     * @param string $label
     * @param string $value
     * @return string
     */
    public static function getFakeText($label, $value)
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake">';
        $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        $html .= '<dd><input disabled class="form-control" type="text" value="' . $value . '"></dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * @param string $label
     * @param string $value
     * @return string
     */
    public static function getFakeTextarea($label, $value)
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake">';
        $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        $html .= '<dd><textarea disabled class="form-control" rows="6">' . $value . '</textarea></dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * @param string $label
     * @param array<string, string> $checkboxes
     * @return string
     */
    public static function getFakeCheckbox($label, $checkboxes)
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake">';
        if ('' !== $label) {
            $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        }
        $html .= '<dd>';
        foreach ($checkboxes as $v) {
            $checked = '|1|' === $v[0] ? 'checked' : '';
            $html .= '<div class="checkbox"><label class="control-label"><input type="checkbox" disabled ' . $checked . '>' . $v[1] . '</label></div>';
        }
        $html .= '</dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * @param rex_form $form
     * @param string $table
     * @return void
     */
    public static function getId(&$form, $table)
    {
        if (!$form->isEditMode()) {
            $db = rex_sql::factory();
            $db->setTable($table);
            $form->addHiddenField('id', $db->setNewId('id', 1));
        } else {
            $form->addHiddenField('id', $form->getSql()->getValue('id'));
        }
    }

    /**
     * @param rex_extension_point<object> $ep
     * @return void
     * @api
     */
    public static function removeDeleteButton(rex_extension_point $ep)
    {
        $formTable = $ep->getParams()['form']->getTableName();
        if (in_array($formTable, consent_manager_config::getTables(), true)) {
            $subject = $ep->getSubject();
            $subject['delete'] = ''; /** @phpstan-ignore-line */
            $ep->setSubject($subject);
        }
    }

    /**
     * @param string $msg
     * @return string
     */
    public static function showInfo($msg)
    {
        return '<div class="consent_manager-rex-form-info"><i class="fa fa-info-circle"></i>' . $msg . '</div>';
    }

    /**
     * @param string $hostname
     * @return bool|string
     * @api
     */
    public static function validateHostname($hostname)
    {
        $host = parse_url('https://' . $hostname);
        if (isset($host['scheme']) && isset($host['host']) && !isset($host['path'])) {
            return filter_var($host['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        }
        return false;
    }

    /**
     * @param string $yaml
     * @return bool
     * @api
     */
    public static function validateYaml($yaml)
    {
        $valid = true;
        try {
            rex_string::yamlDecode($yaml);
        } catch (rex_yaml_parse_exception $exception) {
            $valid = false;
        }
        return $valid;
    }
}
