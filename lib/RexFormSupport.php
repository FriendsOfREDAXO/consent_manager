<?php

namespace FriendsOfRedaxo\ConsentManager;

use rex_extension_point;
use rex_form;
use rex_sql;
use rex_string;
use rex_view;
use rex_yaml_parse_exception;

use function in_array;

use const FILTER_FLAG_HOSTNAME;
use const FILTER_VALIDATE_DOMAIN;

class RexFormSupport
{
    /**
     * @api
     *
     * TODO: korrekter Weise müsste das wohl mit dem Fragment core/form/form.php gelöst werden.
     */
    public static function getFakeText(string $label, string $value): string
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake">';
        $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        $html .= '<dd><input disabled class="form-control" type="text" value="' . $value . '"></dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * @api
     * TODO: korrekter Weise müsste das wohl mit dem Fragment core/form/form.php gelöst werden.
     */
    public static function getFakeTextarea(string $label, string $value): string
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake">';
        $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        $html .= '<dd><textarea disabled class="form-control" rows="6">' . $value . '</textarea></dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * @api
     * @param array<string, string> $checkboxes
     * TODO: korrekter Weise müsste das wohl mit dem Fragment core/form/checkbox.php gelöst werden.
     */
    public static function getFakeCheckbox(string $label, array $checkboxes): string
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
     * @api
     * @param non-empty-string $table
     */
    public static function getId(rex_form $form, string $table): void
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
     * @api
     * @param rex_extension_point<array<string,string>> $ep
     */
    public static function removeDeleteButton(rex_extension_point $ep): void
    {
        /** @var rex_form $form */
        $form = $ep->getParam('form');
        $formTable = $form->getTableName();
        if (in_array($formTable, Config::getTables(), true)) {
            $subject = $ep->getSubject();
            $subject['delete'] = '';
            $ep->setSubject($subject);
        }
    }

    /**
     * @api
     */
    public static function showInfo(string $msg): string
    {
        return rex_view::info('<i class="fa fa-info-circle"></i> ' . htmlspecialchars($msg), 'consent_manager-rex-form-info');
    }

    /**
     * @api
     * @param string $hostname
     */
    public static function validateHostname(string $hostname): bool|string
    {
        $host = parse_url('https://' . $hostname);
        if (isset($host['scheme']) && isset($host['host']) && !isset($host['path'])) {
            return filter_var($host['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        }
        return false;
    }

    /**
     * Prüft ob der Wert nur Kleinbuchstaben enthält
     *
     * @api
     */
    public static function validateLowercase(string $value): bool
    {
        return $value === strtolower($value);
    }

    /**
     * @api
     */
    public static function validateYaml(string $yaml): bool
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
