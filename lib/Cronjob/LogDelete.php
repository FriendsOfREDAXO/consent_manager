<?php

namespace FriendsOfRedaxo\ConsentManager\Cronjob;

use Exception;
use rex;
use rex_addon;
use rex_cronjob;
use rex_i18n;
use rex_sql;

class LogDelete extends rex_cronjob
{
    public function execute()
    {
        if (rex_addon::get('consent_manager')->isAvailable()) {

            try {
                /** TODO: Umstellen auf nur rex_sql-Funktionen */
                $sql = rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('consent_manager_consent_log') . ' WHERE createdate < DATE_SUB(NOW(), INTERVAL ' . (int) trim($this->getParam('days')) .' DAY)');
                $noDeleted = $sql->getRows();

                if ($noDeleted > 0) {
                    $this->setMessage(rex_i18n::rawMsg('consent_manager_cronjob_deleted', $noDeleted));
                } else {
                    $this->setMessage(rex_i18n::rawMsg('consent_manager_cronjob_none_deleted'));
                }
                return true;
            } catch (Exception $e) {
                $this->setMessage($e->getMessage());
                return false;
            }
        }
        $this->setMessage(rex_i18n::rawMsg('consent_manager_cronjob_not_available'));
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('consent_manager_cronjob_delete');
    }

    public function getParamFields()
    {
        $fields = [
            [
                'label' => rex_i18n::msg('consent_manager_cronjob_delete_days'),
                'name' => 'days',
                'type' => 'text',
                'default' => '365',
                'notice' => rex_i18n::msg('consent_manager_cronjob_delete_days_notice'),
            ],
        ];
        return $fields;
    }
}
