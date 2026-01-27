<?php

/**
 * AJAX API: Setup Wizard mit Live-Feedback via Polling
 * Nutzt Session-basiertes Progress-Tracking statt fehleranfälligem SSE.
 */

use FriendsOfRedaxo\ConsentManager\Cache;
use FriendsOfRedaxo\ConsentManager\JsonSetup;

class rex_api_consent_manager_setup_wizard extends rex_api_function
{
    private const SESSION_KEY = 'consent_manager_setup_progress';
    protected $published = true;

    public function execute()
    {
        rex_response::cleanOutputBuffers();

        // Berechtigungsprüfung
        if (!rex::getUser() || !rex::getUser()->isAdmin()) {
            rex_response::sendJson([
                'status' => 'error',
                'message' => 'Keine Berechtigung - nur Admins',
            ]);
            exit;
        }

        // Parameter auslesen
        $domain = rex_request::request('domain', 'string', '');
        $setupType = rex_request::request('setup_type', 'string', 'standard');
        $autoInject = 1 === rex_request::request('auto_inject', 'int', 0);
        $includeTemplates = rex_request::request('include_templates', 'string', '');
        $privacyPolicy = rex_request::request('privacy_policy', 'int', 0);
        $legalNotice = rex_request::request('legal_notice', 'int', 0);
        $cssFrameworkMode = rex_request::request('css_framework_mode', 'string', '');

        // Framework Modus speichern falls übergeben
        if (in_array($cssFrameworkMode, ['', 'uikit3', 'bootstrap5', 'tailwind'], true)) {
            rex_config::set('consent_manager', 'css_framework_mode', $cssFrameworkMode);
        }

        // Domain bereinigen und validieren
        $cleanDomain = $this->cleanDomain($domain);

        if (empty($cleanDomain)) {
            rex_response::sendJson([
                'status' => 'error',
                'message' => 'Domain ist erforderlich',
            ]);
            exit;
        }

        // Setup ausführen
        $this->runSetup($cleanDomain, $setupType, $autoInject, $includeTemplates, $privacyPolicy, $legalNotice, $cssFrameworkMode);
    }

    /**
     * Setup ausführen - komplett synchron.
     */
    private function runSetup(string $domain, string $setupType, bool $autoInject, string $includeTemplates, int $privacyPolicy, int $legalNotice, string $cssFrameworkMode = '')
    {
        try {
            // Parameter auslesen
            $domain = rex_request::request('domain', 'string', '');
            $setupType = rex_request::request('setup_type', 'string', 'standard');
            $autoInject = 1 === rex_request::request('auto_inject', 'int', 0);
            $includeTemplates = rex_request::request('include_templates', 'string', '');
            $privacyPolicy = rex_request::request('privacy_policy', 'int', 0);
            $legalNotice = rex_request::request('legal_notice', 'int', 0);
            $cssFrameworkMode = rex_request::request('css_framework_mode', 'string', '');

            // Validierung
            if (empty($domain)) {
                $this->updateProgress(0, 'error', 'Domain darf nicht leer sein');
                rex_response::sendJson([
                    'status' => 'error',
                    'message' => 'Domain darf nicht leer sein',
                ]);
                exit;
            }

            // Prüfen ob bereits Services vorhanden sind
            $existingServices = rex_sql::factory();
            $existingServices->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookie'));
            $hasExistingServices = $existingServices->getValue('cnt') > 0;

            // Domain anlegen/aktualisieren
            $domainId = $this->createOrUpdateDomain($domain, $autoInject, $includeTemplates, $privacyPolicy, $legalNotice);

            // Standard-Setup importieren (wenn noch keine Services vorhanden)
            if (!$hasExistingServices) {
                if ('standard' === $setupType) {
                    $this->importStandardSetup();
                } else {
                    $this->importMinimalSetup();
                }
            }

            // Cookie-Gruppen zuordnen
            $this->assignGroupsToDomain($domainId);

            // Cache leeren
            $this->clearCache();

            // Validierung
            $validation = $this->validateSetup($domain);

            // Erfolgreiche Response
            rex_response::sendJson([
                'status' => 'success',
                'message' => 'Setup erfolgreich abgeschlossen!',
                'data' => [
                    'domain' => $domain,
                    'domain_id' => $domainId,
                    'setup_type' => $setupType,
                    'auto_inject' => $autoInject,
                    'url' => rex_url::backendPage('consent_manager/domain'),
                    'validation' => $validation,
                ],
            ]);
        } catch (Exception $e) {
            rex_response::sendJson([
                'status' => 'error',
                'message' => 'Fehler: ' . $e->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * Progress in Session speichern.
     */
    private function updateProgress(int $percent, string $status, string $message, array $data = [])
    {
        $progress = [
            'percent' => $percent,
            'status' => $status, // running, complete, error
            'message' => $message,
            'timestamp' => time(),
            'data' => $data,
        ];

        rex_set_session(self::SESSION_KEY, $progress);
    }

    /**
     * Domain bereinigen - entfernt Protokoll und Trailing Slashes, behält Port.
     */
    private function cleanDomain(string $domain): string
    {
        // Protokoll entfernen
        $domain = preg_replace('#^https?://#i', '', $domain);

        // Trailing Slashes entfernen
        $domain = rtrim($domain, '/');

        // Pfade entfernen falls vorhanden (aber Port behalten)
        if (false !== ($pos = strpos($domain, '/'))) {
            $domain = substr($domain, 0, $pos);
        }

        // Zu lowercase
        $domain = strtolower($domain);

        return $domain;
    }

    /**
     * Domain in Datenbank anlegen oder aktualisieren.
     */
    private function createOrUpdateDomain(string $domain, bool $autoInject, string $includeTemplates = '', int $privacyPolicy = 0, int $legalNotice = 0): int
    {
        try {
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('consent_manager_domain'));

            // Prüfen ob Domain bereits existiert
            $existing = rex_sql::factory();
            $existing->setQuery('SELECT id FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);

            if ($existing->getRows() > 0) {
                // Domain existiert - UPDATE
                $domainId = (int) $existing->getValue('id');

                $sql->setWhere(['id' => $domainId]);
                $sql->setValue('uid', $domain);
                $sql->setValue('auto_inject', $autoInject ? 1 : 0);
                $sql->setValue('auto_inject_reload_on_consent', 0);
                $sql->setValue('auto_inject_delay', 0);
                $sql->setValue('auto_inject_focus', 1);
                $sql->setValue('auto_inject_include_templates', $includeTemplates);

                if ($privacyPolicy > 0) {
                    $sql->setValue('privacy_policy', $privacyPolicy);
                }
                if ($legalNotice > 0) {
                    $sql->setValue('legal_notice', $legalNotice);
                }

                $sql->update();
            } else {
                // Domain neu anlegen - INSERT
                $sql->setValue('uid', $domain);
                $sql->setValue('auto_inject', $autoInject ? 1 : 0);
                $sql->setValue('auto_inject_reload_on_consent', 0);
                $sql->setValue('auto_inject_delay', 0);
                $sql->setValue('auto_inject_focus', 1);
                $sql->setValue('auto_inject_include_templates', $includeTemplates);

                if ($privacyPolicy > 0) {
                    $sql->setValue('privacy_policy', $privacyPolicy);
                }
                if ($legalNotice > 0) {
                    $sql->setValue('legal_notice', $legalNotice);
                }

                $sql->insert();
                $domainId = (int) $sql->getLastId();
            }

            return $domainId;
        } catch (rex_sql_exception $e) {
            throw new Exception('Domain konnte nicht gespeichert werden: ' . $e->getMessage());
        }
    }

    /**
     * Standard-Setup importieren (Google, YouTube, Vimeo, etc.).
     */
    private function importStandardSetup()
    {
        try {
            $jsonSetupFile = rex_path::addon('consent_manager', 'setup/default_setup.json');
            if (!file_exists($jsonSetupFile)) {
                throw new Exception('Setup-Datei nicht gefunden: ' . $jsonSetupFile);
            }
            // false = deleteExisting NICHT aktivieren (Domain behalten!)
            JsonSetup::importSetup($jsonSetupFile, false, 'update');
        } catch (Exception $e) {
            throw new Exception('Standard-Setup konnte nicht importiert werden: ' . $e->getMessage());
        }
    }

    /**
     * Minimal-Setup importieren (nur Cookie-Gruppen, keine Services).
     */
    private function importMinimalSetup()
    {
        try {
            $jsonSetupFile = rex_path::addon('consent_manager', 'setup/minimal_setup.json');
            if (!file_exists($jsonSetupFile)) {
                throw new Exception('Setup-Datei nicht gefunden: ' . $jsonSetupFile);
            }
            // false = deleteExisting NICHT aktivieren (Domain behalten!)
            JsonSetup::importSetup($jsonSetupFile, false, 'update');
        } catch (Exception $e) {
            throw new Exception('Minimal-Setup konnte nicht importiert werden: ' . $e->getMessage());
        }
    }

    /**
     * Cookie-Gruppen einer Domain zuordnen
     * Verwendet Pipe-Format: |domainId|domainId|.
     */
    private function assignGroupsToDomain(int $domainId): void
    {
        $sql = rex_sql::factory();

        // Nur die "required" Gruppe finden (uid = 'required')
        // Zuordnung erfolgt über das domain-Feld mit Pipe-Format |domainId|
        $sql->setQuery('SELECT id, domain FROM ' . rex::getTable('consent_manager_cookiegroup') . ' WHERE uid = ?', ['required']);

        if ($sql->getRows() > 0) {
            $groupId = $sql->getValue('id');
            $currentDomain = $sql->getValue('domain');

            // Domain-String im Format |17|18| aufbauen
            $domainString = trim((string) $currentDomain, '|');
            $domainIds = array_filter(explode('|', $domainString));

            // Nur hinzufügen wenn noch nicht vorhanden
            if (!in_array((string) $domainId, $domainIds, true)) {
                $domainIds[] = $domainId;
                $newDomainString = '|' . implode('|', $domainIds) . '|';

                // Required-Gruppe der Domain zuordnen
                $update = rex_sql::factory();
                $update->setTable(rex::getTable('consent_manager_cookiegroup'));
                $update->setWhere(['id' => $groupId]);
                $update->setValue('domain', $newDomainString);
                $update->update();
            }
        }
    }

    /**
     * Cache leeren.
     */
    private function clearCache()
    {
        Cache::forceWrite();
    }

    /**
     * Setup validieren.
     */
    private function validateSetup(string $domain): array
    {
        $validation = [
            'domain_exists' => false,
            'theme_assigned' => false,
            'cookies_count' => 0,
            'groups_count' => 0,
        ];

        // Domain prüfen
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT theme FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);
        if ($sql->getRows() > 0) {
            $validation['domain_exists'] = true;
            $validation['theme_assigned'] = '' !== $sql->getValue('theme');
        }

        // Cookies zählen
        $cookieCount = rex_sql::factory();
        $cookieCount->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookie'));
        $validation['cookies_count'] = (int) $cookieCount->getValue('cnt');

        // Cookie Groups zählen
        $groupCount = rex_sql::factory();
        $groupCount->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookiegroup'));
        $validation['groups_count'] = (int) $groupCount->getValue('cnt');

        return $validation;
    }
}
