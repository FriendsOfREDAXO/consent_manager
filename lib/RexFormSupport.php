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
     * Enhanced service list with toggle switches and config buttons
     * @api
     * @param array<array{0: string, 1: string}> $services Array of [checked, uid] pairs
     */
    public static function getServiceToggleList(string $label, array $services, int $clangId): string
    {
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-fake consent_manager-service-list">';
        if ('' !== $label) {
            $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        }
        $html .= '<dd>';
        $html .= '<style nonce="' . \rex_response::getNonce() . '">
            .consent_manager-service-list .service-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 8px 12px;
                margin-bottom: 8px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            .consent_manager-service-list .service-item:hover {
                background: #e9ecef;
            }
            .consent_manager-service-list .service-left {
                display: flex;
                align-items: center;
                gap: 12px;
                flex: 1;
            }
            .consent_manager-service-list .service-name {
                font-weight: 500;
                font-size: 14px;
                color: #495057;
            }
            .consent_manager-service-list .service-toggle {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }
            .consent_manager-service-list .service-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .consent_manager-service-list .service-slider {
                position: absolute;
                cursor: not-allowed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .3s;
                border-radius: 24px;
            }
            .consent_manager-service-list .service-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .consent_manager-service-list input:checked + .service-slider {
                background-color: #5cb85c;
            }
            .consent_manager-service-list input:checked + .service-slider:before {
                transform: translateX(20px);
            }
            .consent_manager-service-list .btn-config {
                padding: 4px 10px;
                font-size: 12px;
                line-height: 1.5;
                border-radius: 3px;
            }
            
            /* Dark Mode Support */
            body.rex-theme-dark .consent_manager-service-list .service-item {
                background: #2d3748;
                border-color: #4a5568;
            }
            body.rex-theme-dark .consent_manager-service-list .service-item:hover {
                background: #374151;
            }
            body.rex-theme-dark .consent_manager-service-list .service-name {
                color: #e2e8f0;
            }
            @media (prefers-color-scheme: dark) {
                body:not(.rex-theme-light) .consent_manager-service-list .service-item {
                    background: #2d3748;
                    border-color: #4a5568;
                }
                body:not(.rex-theme-light) .consent_manager-service-list .service-item:hover {
                    background: #374151;
                }
                body:not(.rex-theme-light) .consent_manager-service-list .service-name {
                    color: #e2e8f0;
                }
            }
        </style>';
        
        foreach ($services as $service) {
            $checked = '|1|' === $service[0] ? 'checked' : '';
            $uid = \rex_escape($service[1]);
            $configUrl = \rex_url::backendPage('consent_manager/cookie', ['func' => 'edit', 'pid' => self::getCookiePidByUid($uid, $clangId)]);
            
            $html .= '<div class="service-item">';
            $html .= '<div class="service-left">';
            $html .= '<label class="service-toggle">';
            $html .= '<input type="checkbox" disabled ' . $checked . '>';
            $html .= '<span class="service-slider"></span>';
            $html .= '</label>';
            $html .= '<span class="service-name">' . $uid . '</span>';
            $html .= '</div>';
            $html .= '<a href="' . $configUrl . '" class="btn btn-xs btn-default btn-config" title="Service konfigurieren">';
            $html .= '<i class="rex-icon fa-cog"></i> Config';
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</dd>';
        $html .= '</dl>';
        return $html;
    }

    /**
     * Helper to get cookie PID by UID
     */
    private static function getCookiePidByUid(string $uid, int $clangId): int
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT pid FROM ' . \rex::getTable('consent_manager_cookie') . ' WHERE uid = ? AND clang_id = ?', [$uid, $clangId]);
        return (int) $sql->getValue('pid');
    }

    /**
     * Active service list with real checkboxes (for add/edit mode)
     * @param array<array{uid: string}> $services Array of service records
     */
    public static function getActiveServiceToggleList(string $label, array $services, int $clangId, string $fieldName, rex_form $form): string
    {
        // Get currently selected values
        $selectedValues = [];
        if ($form->isEditMode() && null !== $form->getSql()->getValue($fieldName)) {
            $selectedValues = array_filter(explode('|', (string) $form->getSql()->getValue($fieldName)), static function ($value) {
                return '' !== $value;
            });
        }
        
        $html = '';
        $html .= '<dl class="rex-form-group form-group consent_manager-service-list-active">';
        if ('' !== $label) {
            $html .= '<dt><label class="control-label">' . $label . '</label></dt>';
        }
        $html .= '<dd>';
        
        // Alle auswählen/abwählen Buttons
        $html .= '<div style="margin-bottom: 10px;">';
        $html .= '<button type="button" class="btn btn-xs btn-info select-all-services" style="margin-right: 5px;"><i class="fa fa-check-square-o"></i> Alle auswählen</button>';
        $html .= '<button type="button" class="btn btn-xs btn-default deselect-all-services"><i class="fa fa-square-o"></i> Alle abwählen</button>';
        $html .= '</div>';
        
        // Live-Suchfeld
        $html .= '<div class="input-group" style="margin-bottom: 12px;">';
        $html .= '<span class="input-group-addon"><i class="fa fa-search"></i></span>';
        $html .= '<input type="text" class="form-control service-search" placeholder="Service suchen..." autocomplete="off">';
        $html .= '</div>';
        
        $html .= '<style nonce="' . \rex_response::getNonce() . '">
            .consent_manager-service-list-active .service-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 6px 10px;
                margin-bottom: 6px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                transition: all 0.2s;
            }
            .consent_manager-service-list-active .service-item.hidden {
                display: none;
            }
            .consent_manager-service-list-active .service-item:hover {
                background: #e9ecef;
                border-color: #adb5bd;
            }
            .consent_manager-service-list-active .service-left {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }
            .consent_manager-service-list-active .service-name {
                font-weight: 500;
                font-size: 13px;
                color: #495057;
            }
            .consent_manager-service-list-active .service-uid {
                font-size: 12px;
                color: #6c757d;
                font-weight: 500;
                margin-top: 1px;
            }
            .consent_manager-service-list-active .service-variant {
                font-size: 11px;
                color: #6c757d;
                font-style: italic;
                margin-top: 1px;
            }
            .consent_manager-service-list-active .service-info {
                display: flex;
                flex-direction: column;
            }
            .consent_manager-service-list-active .service-toggle {
                position: relative;
                display: inline-block;
                width: 36px;
                height: 20px;
            }
            .consent_manager-service-list-active .service-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .consent_manager-service-list-active .service-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .3s;
                border-radius: 20px;
            }
            .consent_manager-service-list-active .service-slider:before {
                position: absolute;
                content: "";
                height: 14px;
                width: 14px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
                box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            }
            .consent_manager-service-list-active input:checked + .service-slider {
                background-color: #5cb85c;
            }
            .consent_manager-service-list-active input:checked + .service-slider:before {
                transform: translateX(16px);
            }
            .consent_manager-service-list-active .btn-config {
                padding: 3px 8px;
                font-size: 11px;
                line-height: 1.4;
                border-radius: 3px;
            }
            .consent_manager-service-list-active .service-search {
                font-size: 13px;
            }
            .consent_manager-service-list-active .input-group-addon {
                background-color: #f8f9fa;
            }
            
            /* Dark Mode Support */
            body.rex-theme-dark .consent_manager-service-list-active .service-item {
                background: #2d3748;
                border-color: #4a5568;
            }
            body.rex-theme-dark .consent_manager-service-list-active .service-item:hover {
                background: #374151;
                border-color: #5a6778;
            }
            body.rex-theme-dark .consent_manager-service-list-active .service-name {
                color: #e2e8f0;
            }
            body.rex-theme-dark .consent_manager-service-list-active .service-uid {
                color: #9ca3af;
            }
            body.rex-theme-dark .consent_manager-service-list-active .service-variant {
                color: #9ca3af;
            }
            body.rex-theme-dark .consent_manager-service-list-active .input-group-addon {
                background-color: #374151;
                border-color: #4a5568;
                color: #e2e8f0;
            }
            body.rex-theme-dark .consent_manager-service-list-active .service-search {
                background-color: #1f2937;
                border-color: #4a5568;
                color: #f3f4f6;
            }
            @media (prefers-color-scheme: dark) {
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-item {
                    background: #2d3748;
                    border-color: #4a5568;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-item:hover {
                    background: #374151;
                    border-color: #5a6778;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-name {
                    color: #e2e8f0;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-uid {
                    color: #9ca3af;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-variant {
                    color: #9ca3af;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .input-group-addon {
                    background-color: #374151;
                    border-color: #4a5568;
                    color: #e2e8f0;
                }
                body:not(.rex-theme-light) .consent_manager-service-list-active .service-search {
                    background-color: #1f2937;
                    border-color: #4a5568;
                    color: #f3f4f6;
                }
            }
        </style>';
        
        foreach ($services as $service) {
            $uid = $service['uid'];
            $serviceName = $service['service_name'] ?? $uid;
            $variant = $service['variant'] ?? '';
            $isChecked = in_array($uid, $selectedValues, true);
            $checked = $isChecked ? ' checked' : '';
            $escapedUid = \rex_escape($uid);
            $escapedServiceName = \rex_escape($serviceName);
            $escapedVariant = \rex_escape($variant);
            $configUrl = \rex_url::backendPage('consent_manager/cookie', ['func' => 'edit', 'pid' => self::getCookiePidByUid($uid, $clangId)]);
            
            $html .= '<div class="service-item" data-uid="' . $escapedUid . '" data-name="' . $escapedServiceName . '" data-variant="' . $escapedVariant . '">';
            $html .= '<div class="service-left">';
            $html .= '<label class="service-toggle">';
            $html .= '<input type="checkbox" name="' . $fieldName . '[]" value="' . $escapedUid . '"' . $checked . '>';
            $html .= '<span class="service-slider"></span>';
            $html .= '</label>';
            $html .= '<div class="service-info">';
            $html .= '<span class="service-name">' . $escapedServiceName . '</span>';
            $html .= '<span class="service-uid">' . $escapedUid . '</span>';
            if ('' !== $variant) {
                $html .= '<span class="service-variant">→ ' . $escapedVariant . '</span>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<a href="' . $configUrl . '" class="btn btn-xs btn-default btn-config" title="Service konfigurieren" target="_blank">';
            $html .= '<i class="rex-icon fa-cog"></i> Config';
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '<script nonce="' . \rex_response::getNonce() . '">
        jQuery(function($) {
            // Live-Suche
            $(".consent_manager-service-list-active .service-search").on("keyup", function() {
                var searchTerm = $(this).val().toLowerCase();
                $(".consent_manager-service-list-active .service-item").each(function() {
                    var serviceName = $(this).find(".service-name").text().toLowerCase();
                    var serviceUid = $(this).find(".service-uid").text().toLowerCase();
                    var serviceVariant = $(this).find(".service-variant").text().toLowerCase();
                    if (serviceName.indexOf(searchTerm) !== -1 || serviceUid.indexOf(searchTerm) !== -1 || serviceVariant.indexOf(searchTerm) !== -1) {
                        $(this).removeClass("hidden").show();
                    } else {
                        $(this).addClass("hidden").hide();
                    }
                });
            });
            
            // Alle auswählen
            $(".select-all-services").on("click", function(e) {
                e.preventDefault();
                $(".consent_manager-service-list-active .service-item:visible input[type=checkbox]").prop("checked", true);
            });
            
            // Alle abwählen
            $(".deselect-all-services").on("click", function(e) {
                e.preventDefault();
                $(".consent_manager-service-list-active .service-item:visible input[type=checkbox]").prop("checked", false);
            });
        });
        </script>';
        
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
