<?php

/**
 * AJAX API: Setup Wizard mit Live-Feedback via Polling
 * Nutzt Session-basiertes Progress-Tracking statt fehleranfälligem SSE
 */

use FriendsOfRedaxo\ConsentManager\JsonSetup;

class rex_api_consent_manager_setup_wizard extends rex_api_function
{
    protected $published = true;
    
    private const SESSION_KEY = 'consent_manager_setup_progress';

    public function execute()
    {
        // Berechtigungsprüfung
        if (!rex::getUser() || !rex::getUser()->isAdmin()) {
            rex_response::sendJson([
                'status' => 'error',
                'message' => 'Keine Berechtigung - nur Admins'
            ]);
            exit;
        }

        // Action auslesen: start (Setup starten) oder status (Progress abrufen)
        $action = rex_request::request('action', 'string', 'start');
        
        if ($action === 'status') {
            // Progress Status zurückgeben
            $this->getProgress();
            // getProgress() ruft exit auf, wird nie erreicht
        }
        
        // Setup starten
        $this->startSetup();
        // startSetup() ruft exit auf, wird nie erreicht
    }

    /**
     * Progress aus Session holen und zurückgeben
     */
    private function getProgress()
    {
        $progress = rex_session(self::SESSION_KEY, 'array', []);
        
        rex_response::sendJson($progress);
        exit;
    }

    /**
     * Setup starten (läuft asynchron weiter)
     */
    private function startSetup()
    {
        // Parameter auslesen
        $domain = rex_request::request('domain', 'string', '');
        $setupType = rex_request::request('setup_type', 'string', 'standard');
        $autoInject = rex_request::request('auto_inject', 'int', 0) === 1;
        $includeTemplates = rex_request::request('include_templates', 'string', '');
        $privacyPolicy = rex_request::request('privacy_policy', 'int', 0);
        $legalNotice = rex_request::request('legal_notice', 'int', 0);

        // Validierung
        if (empty($domain)) {
            $this->updateProgress(0, 'error', 'Domain darf nicht leer sein');
            rex_response::sendJson([
                'status' => 'error',
                'message' => 'Domain darf nicht leer sein'
            ]);
            exit;
        }

        // Domain bereinigen
        $cleanDomain = $this->cleanDomain($domain);

        // Progress initialisieren
        $this->updateProgress(0, 'running', 'Setup wird gestartet...');
        
        // Sofort Bestätigung zurückgeben
        rex_response::sendJson([
            'status' => 'started',
            'message' => 'Setup wurde gestartet'
        ]);
        
        // Output Buffer schließen damit Response sofort gesendet wird
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();
        
        // Ab hier läuft Setup asynchron weiter
        try {
            // Prüfen ob bereits Services vorhanden sind
            $existingServices = rex_sql::factory();
            $existingServices->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookie'));
            $hasExistingServices = $existingServices->getValue('cnt') > 0;

            // Schritt 1: Domain anlegen/aktualisieren
            $this->updateProgress(10, 'running', 'Domain wird konfiguriert: ' . $cleanDomain);
            $domainId = $this->createOrUpdateDomain($cleanDomain, $autoInject, $includeTemplates, $privacyPolicy, $legalNotice);
            $this->updateProgress(20, 'running', 'Domain erstellt (ID: ' . $domainId . ')');
            usleep(300000); // 0.3s

            // Schritt 2: Standard-Setup importieren (nur wenn noch keine Services)
            if ($hasExistingServices) {
                $this->updateProgress(30, 'running', 'Services bereits vorhanden - Import übersprungen');
            } else {
                if ('standard' === $setupType) {
                    $this->updateProgress(30, 'running', 'Standard-Setup importieren (Google Analytics, YouTube, Vimeo, ...)');
                    $this->importStandardSetup();
                    $this->updateProgress(40, 'running', 'Standard-Services importiert');
                } else {
                    $this->updateProgress(30, 'running', 'Minimal-Setup importieren (nur Cookie-Gruppen)');
                    $this->importMinimalSetup();
                    $this->updateProgress(40, 'running', 'Minimal-Setup importiert');
                }
            }
            usleep(300000);

            // Schritt 3: Cookie-Gruppen der Domain zuordnen
            $this->updateProgress(50, 'running', 'Cookie-Gruppen der Domain zuordnen...');
            $this->assignGroupsToDomain($domainId);
            $this->updateProgress(60, 'running', 'Cookie-Gruppen zugeordnet');
            usleep(300000);

            // Schritt 4: Default-Theme wird verwendet
            $this->updateProgress(70, 'running', 'Default-Theme wird verwendet...');
            usleep(200000);

            // Schritt 5: Cache leeren
            $this->updateProgress(80, 'running', 'Cache aufwärmen...');
            $this->clearCache();
            $this->updateProgress(90, 'running', 'Cache geleert');
            usleep(200000);

            // Schritt 6: Validierung
            $this->updateProgress(95, 'running', 'Konfiguration validieren...');
            $validation = $this->validateSetup($cleanDomain);
            
            // Abschluss
            $this->updateProgress(100, 'complete', 'Setup abgeschlossen!', [
                'domain' => $cleanDomain,
                'domain_id' => $domainId,
                'setup_type' => $setupType,
                'auto_inject' => $autoInject,
                'url' => rex_url::backendPage('consent_manager/domain'),
                'validation' => $validation
            ]);

        } catch (Exception $e) {
            $this->updateProgress(0, 'error', 'Fehler: ' . $e->getMessage());
        }
        
        exit;
    }

    /**
     * Progress in Session speichern
     */
    private function updateProgress(int $percent, string $status, string $message, array $data = [])
    {
        $progress = [
            'percent' => $percent,
            'status' => $status, // running, complete, error
            'message' => $message,
            'timestamp' => time(),
            'data' => $data
        ];
        
        rex_set_session(self::SESSION_KEY, $progress);
    }

    /**
     * Domain bereinigen - entfernt Protokoll und Trailing Slashes, behält Port
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
     * Domain in Datenbank anlegen oder aktualisieren
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
     * Standard-Setup importieren (Google, YouTube, Vimeo, etc.)
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
     * Minimal-Setup importieren (nur Cookie-Gruppen, keine Services)
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
     * Verwendet Pipe-Format: |domainId|domainId|
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
     * Cache leeren
     */
    private function clearCache()
    {
        \FriendsOfRedaxo\ConsentManager\Cache::forceWrite();
    }

    /**
     * Setup validieren
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
