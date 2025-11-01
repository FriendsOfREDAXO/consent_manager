<?php

namespace FriendsOfRedaxo\ConsentManager\Command;

use rex;
use rex_console_command;
use rex_i18n;
use rex_sql;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogDelete extends rex_console_command
{
    protected function configure()
    {
        /* TODO: Text nach *.lang verlagern (rex_18n) */
        $this->setDescription('Deletes old entries in consent log table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('consent_manager log_delete');

        /**
         * alt und von Rexstan moniert (SQL-Syntax-Error near "'DAYS)' at line 1" ):
         *  rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('consent_manager_consent_log') . ' WHERE createdate < DATE_SUB(NOW(), INTERVAL 2 DAYS)');
         *  $noDeleted = rex_sql::factory()->getRows();
         */
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_consent_log'));
        $sql->setWhere('createdate < DATE_SUB(NOW(), INTERVAL 2 DAYS)');
        $sql->delete();
        $noDeleted = $sql->getRows();

        if ($noDeleted > 0) {
            $io->success(rex_i18n::rawMsg('consent_manager_cronjob_deleted', $noDeleted));
            return 0;
        }
        $io->error(rex_i18n::rawMsg('consent_manager_cronjob_delete_error'));

        return 1;
    }
}
