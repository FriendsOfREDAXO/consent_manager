<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class rex_consent_manager_command_log_delete extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Deletes old entries in consent log table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('consent_manager log_delete');

        rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('consent_manager_consent_log') . ' WHERE createdate < DATE_SUB(NOW(), INTERVAL 2 DAYS)');
        $noDeleted = rex_sql::factory()->getRows();

        if ($noDeleted > 0) {
            echo $io->success(rex_i18n::rawMsg('consent_manager_cronjob_deleted', $noDeleted));
            return 0;
        } else {
            echo $io->error(rex_i18n::rawMsg('consent_manager_cronjob_delete_error'));
        }

        return 1;
    }
}
