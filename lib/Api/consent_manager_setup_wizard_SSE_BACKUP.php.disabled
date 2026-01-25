<?php

/**
 * SSE API: Setup Wizard mit Live-Feedback
 * Führt automatisches Setup durch: Domain, Import, Theme, Auto-Inject
 */

use FriendsOfRedaxo\ConsentManager\JsonSetup;

class rex_api_consent_manager_setup_wizard extends rex_api_function
{
    protected $published = true;

    /**
     * CSRF-Protection deaktiviert für EventSource (SSE)
     * 
     * EventSource kann nur GET-Requests senden und unterstützt keine CSRF-Tokens.
     * Security ist durch Admin-Check in execute() gewährleistet.
     *
     * @return bool
     */
    protected function requiresCsrfToken()
    {
        return false;
    }

    public function execute()
    {
        // Output Buffer SOFORT leeren - wichtig für SSE!
        rex_response::cleanOutputBuffers();
        
        // Berechtigungsprüfung
        if (!rex::getUser() || !rex::getUser()->isAdmin()) {
            // SSE Headers setzen vor Fehler
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            echo "event: error\n";
            echo 'data: ' . json_encode(['message' => 'Keine Berechtigung - nur Admins']) . "\n\n";
            flush();
            exit;
        }

        // Session schließen damit andere Requests nicht blockiert werden
        session_write_close();
        
        // SSE Headers setzen
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Nginx: Buffering deaktivieren
        header('Content-Encoding: none'); // Apache Gzip/mod_deflate deaktivieren
        
        // Apache mod_deflate deaktivieren für SSE
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        
        // PHP Output Buffering komplett deaktivieren
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        
        // Implicit Flush aktivieren
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        @ini_set('implicit_flush', '1');
        @ob_implicit_flush(1);

        // Execution Time auf unbegrenzt setzen (SSE kann lange laufen)
        set_time_limit(0);

        // Parameter auslesen (GET weil EventSource kein POST unterstützt)
        $domain = rex_request::get('domain', 'string', '');
        $setupType = rex_request::get('setup_type', 'string', 'standard'); // standard oder minimal
        $autoInject = rex_request::get('auto_inject', 'int', 0) === 1;
        $includeTemplates = rex_request::get('include_templates', 'string', ''); // Kommagetrennte Template-IDs
        $privacyPolicy = rex_request::get('privacy_policy', 'int', 0);
        $legalNotice = rex_request::get('legal_notice', 'int', 0);
        
        // Theme wird nicht mehr übergeben - immer Default-Theme verwenden
        
        // Prüfen ob bereits Services existieren
        $existingServicesCount = rex_sql::factory();
        $existingServicesCount->setQuery('SELECT COUNT(*) as cnt FROM ' . rex::getTable('consent_manager_cookie'));
        $hasExistingServices = (int) $existingServicesCount->getValue('cnt') > 0;

        // Domain bereinigen (Protokoll entfernen)
        $domain = $this->cleanDomain($domain);

        // Validierung
        if ('' === $domain) {
            $this->sendError('Domain fehlt (Eingabe: "' . rex_request::get('domain', 'string', '') . '")');
            exit;
        }

        // Init Event senden
        $this->sendEvent('init', ['status' => 'connected', 'domain' => $domain]);

        try {
            // Schritt 1: Domain anlegen/aktualisieren
            $this->sendProgress(10, 'Domain-Konfiguration erstellen...');
            $this->sendEvent('debug', ['step' => 'before_domain', 'domain' => $domain, 'auto_inject' => $autoInject]);
            
            $domainId = $this->createOrUpdateDomain($domain, $autoInject, $includeTemplates, $privacyPolicy, $legalNotice);
            
            $this->sendEvent('debug', ['step' => 'after_domain', 'domain_id' => $domainId]);
            $this->sendEvent('domain_created', ['id' => $domainId, 'domain' => $domain]);
            usleep(500000); // 0.5s Pause für UX
            
            // Verbindung prüfen (bricht ab wenn Browser Tab geschlossen wurde)
            if (connection_aborted()) {
                exit;
            }

            // Schritt 2: Standard-Setup importieren (nur wenn noch keine Services)
            if ($hasExistingServices) {
                $this->sendProgress(30, 'Services bereits vorhanden - Import übersprungen');
                $this->sendEvent('import_skipped', ['reason' => 'services_exist']);
                usleep(500000);
            } else {
                if ('standard' === $setupType) {
                    $this->sendProgress(30, 'Standard-Setup importieren (Google Analytics, YouTube, Vimeo, ...)');
                    $this->importStandardSetup();
                    $this->sendEvent('import_complete', ['type' => 'standard']);
                } else {
                    $this->sendProgress(30, 'Minimal-Setup importieren (nur Cookie-Gruppen)');
                    $this->importMinimalSetup();
                    $this->sendEvent('import_complete', ['type' => 'minimal']);
                }
                usleep(500000);
            }
            
            // Verbindung prüfen
            if (connection_aborted()) {
                exit;
            }

            // Schritt 2.5: Cookie-Gruppen der Domain zuordnen
            $this->sendProgress(50, 'Cookie-Gruppen der Domain zuordnen...');
            $this->assignGroupsToDomain($domainId);
            $this->sendEvent('groups_assigned', ['domain_id' => $domainId]);
            usleep(500000);
            
            // Verbindung prüfen
            if (connection_aborted()) {
                exit;
            }

            // Schritt 3: Default-Theme setzen (konfiguriert nicht pro Domain)
            $this->sendProgress(60, 'Default-Theme wird verwendet...');
            $this->sendEvent('theme_assigned', ['theme' => 'Default']);
            usleep(500000);

            // Schritt 4: Cache leeren
            $this->sendProgress(80, 'Cache aufwärmen...');
            $this->clearCache();
            $this->sendEvent('cache_cleared', ['success' => true]);
            usleep(300000);
            
            // Verbindung prüfen
            if (connection_aborted()) {
                exit;
            }

            // Schritt 5: Finale Prüfung
            $this->sendProgress(95, 'Konfiguration validieren...');
            $validation = $this->validateSetup($domain);
            $this->sendEvent('validation', $validation);
            usleep(300000);

            // Abschluss
            $this->sendProgress(100, 'Setup abgeschlossen!');
            $this->sendEvent('complete', [
                'message' => 'Consent Manager erfolgreich eingerichtet!',
                'domain' => $domain,
                'setup_type' => $setupType,
                'auto_inject' => $autoInject,
                'url' => rex_url::backendPage('consent_manager/domain'),
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }

        exit;
    }

    /**
     * Domain bereinigen - entfernt Protokoll und Trailing Slashes, behält Port
     */
    private function cleanDomain(string $domain): string
    {
        // Protokoll entfernen
        $domain = preg_replace('#^https?://#i', '', $domain);
        
        // www. optional entfernen (User entscheidet)
        // $domain = preg_replace('#^www\.#i', '', $domain);
        
        // Trailing Slashes entfernen
        $domain = rtrim($domain, '/');
        
        // Port BEIBEHALTEN (z.B. localhost:8443)
        // $domain = preg_replace('#:\d+$#', '', $domain);
        
        // Pfade entfernen falls vorhanden (aber Port behalten)
        // Nur bis zum ersten Slash, aber nach dem Port
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
            
            $domainExists = $existing->getRows() > 0;
            $this->sendEvent('debug', ['check_domain' => $domain, 'exists' => $domainExists, 'rows' => $existing->getRows()]);
            
            if ($domainExists) {
                // Update existierende Domain
                $existingId = (int) $existing->getValue('id');
                $this->sendEvent('debug', ['action' => 'update', 'id' => $existingId]);
                
                $sql->setWhere(['uid' => $domain]);
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
                
                $this->sendEvent('debug', ['action' => 'updated', 'id' => $existingId]);
                return $existingId;
            }
            
            // Insert neue Domain
            $this->sendEvent('debug', ['action' => 'insert', 'domain' => $domain]);
            
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
            $this->sendEvent('debug', ['action' => 'inserted', 'id' => $domainId, 'last_id' => $sql->getLastId()]);
            
            if (0 === $domainId) {
                throw new \Exception('Domain konnte nicht angelegt werden - keine ID zurückgegeben');
            }
            
            return $domainId;
            
        } catch (\Exception $e) {
            $this->sendEvent('debug', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new \Exception('Fehler beim Anlegen der Domain: ' . $e->getMessage());
        }
    }

    /**
     * Standard-Setup importieren (ruft existierende Setup-Funktion auf)
     */
    private function importStandardSetup(): void
    {
        try {
            $jsonSetupFile = rex_path::addon('consent_manager', 'setup/default_setup.json');
            if (!file_exists($jsonSetupFile)) {
                throw new \Exception('Setup-Datei nicht gefunden: ' . $jsonSetupFile);
            }
            // false = deleteExisting NICHT aktivieren (Domain behalten!)
            JsonSetup::importSetup($jsonSetupFile, false, 'update');
        } catch (\Exception $e) {
            throw new \Exception('Import fehlgeschlagen: ' . $e->getMessage());
        }
    }

    /**
     * Minimal-Setup importieren
     */
    private function importMinimalSetup(): void
    {
        try {
            $jsonSetupFile = rex_path::addon('consent_manager', 'setup/minimal_setup.json');
            if (!file_exists($jsonSetupFile)) {
                throw new \Exception('Setup-Datei nicht gefunden: ' . $jsonSetupFile);
            }
            // false = deleteExisting NICHT aktivieren (Domain behalten!)
            JsonSetup::importSetup($jsonSetupFile, false, 'update');
        } catch (\Exception $e) {
            throw new \Exception('Import fehlgeschlagen: ' . $e->getMessage());
        }
    }

    /**
     * Theme einer Domain zuweisen
     */
    private function assignTheme(string $domain, string $themeUid): bool
    {
        if ('' === $themeUid) {
            // Erstes verfügbares Theme wählen (Standard-Theme)
            $themeFiles = (array) glob(rex_addon::get('consent_manager')->getPath('scss/consent_manager_frontend*.scss'));
            if (count($themeFiles) > 0) {
                natsort($themeFiles);
                $themeUid = basename((string) reset($themeFiles));
            } else {
                return false;
            }
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_domain'));
        $sql->setWhere(['uid' => $domain]);
        $sql->setValue('theme', $themeUid);
        $sql->update();

        return true;
    }

    /**
     * Cache leeren
     */
    private function clearCache(): void
    {
        \FriendsOfRedaxo\ConsentManager\Cache::forceWrite();
    }

    /**
     * Cookie-Gruppen einer Domain zuordnen
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

    /**
     * Progress Event senden
     */
    private function sendProgress(int $percent, string $message): void
    {
        $this->sendEvent('progress', [
            'percent' => $percent,
            'message' => $message,
        ]);
    }

    /**
     * Error Event senden
     */
    private function sendError(string $message): void
    {
        $this->sendEvent('error', ['message' => $message]);
    }

    /**
     * Generisches Event senden
     */
    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        
        // AGGRESSIVE FLUSH für Remote-Server mit mehreren Buffer-Ebenen
        // Manche Server (Apache mod_fcgid, Nginx fastcgi) buffern trotz Headers
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        
        // Zusätzliches Padding für manche Proxies (nginx needs 2kb)
        // Aber nur bei ersten Events, nicht bei jedem
        static $paddingSent = false;
        if (!$paddingSent) {
            echo str_repeat(' ', 2048) . "\n";
            flush();
            $paddingSent = true;
        }
    }
}
