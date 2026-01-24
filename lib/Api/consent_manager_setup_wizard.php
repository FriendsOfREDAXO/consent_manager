<?php

/**
 * SSE API: Setup Wizard mit Live-Feedback
 * Führt automatisches Setup durch: Domain, Import, Theme, Auto-Inject
 */
class rex_api_consent_manager_setup_wizard extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        // Output Buffer SOFORT leeren - noch vor allen anderen Operationen!
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
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
        header('X-Accel-Buffering: no');

        // Execution Time erhöhen
        set_time_limit(120);

        // Parameter auslesen (GET weil EventSource kein POST unterstützt)
        $domain = rex_request::get('domain', 'string', '');
        $setupType = rex_request::get('setup_type', 'string', 'standard'); // standard oder minimal
        $themeUid = rex_request::get('theme_uid', 'string', '');
        $autoInject = rex_request::get('auto_inject', 'int', 0) === 1;

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
            $domainId = $this->createOrUpdateDomain($domain, $autoInject);
            $this->sendEvent('domain_created', ['id' => $domainId, 'domain' => $domain]);
            usleep(500000); // 0.5s Pause für UX

            // Schritt 2: Standard-Setup importieren
            if ('standard' === $setupType) {
                $this->sendProgress(30, 'Standard-Setup importieren (Google Analytics, YouTube, Vimeo, ...)');
                $this->importStandardSetup();
                $this->sendEvent('import_complete', ['type' => 'standard']);
            } else {
                $this->sendProgress(30, 'Minimal-Setup importieren (nur Cookie-Gruppen)');
                $this->importMinimalSetup();
                $this->sendEvent('import_complete', ['type' => 'minimal']);
            }
            usleep(800000); // 0.8s Pause

            // Schritt 3: Theme zuweisen
            $this->sendProgress(60, 'Theme konfigurieren...');
            $themeAssigned = $this->assignTheme($domain, $themeUid);
            $this->sendEvent('theme_assigned', ['theme' => $themeUid, 'success' => $themeAssigned]);
            usleep(500000);

            // Schritt 4: Cache leeren
            $this->sendProgress(80, 'Cache aufwärmen...');
            $this->clearCache();
            $this->sendEvent('cache_cleared', ['success' => true]);
            usleep(300000);

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
                'theme' => $themeUid,
                'auto_inject' => $autoInject,
                'url' => rex_url::backendPage('consent_manager/domain'),
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }

        exit;
    }

    /**
     * Domain bereinigen - entfernt Protokoll und Trailing Slashes
     */
    private function cleanDomain(string $domain): string
    {
        // Protokoll entfernen
        $domain = preg_replace('#^https?://#i', '', $domain);
        
        // www. optional entfernen (User entscheidet)
        // $domain = preg_replace('#^www\.#i', '', $domain);
        
        // Trailing Slashes entfernen
        $domain = rtrim($domain, '/');
        
        // Port entfernen falls vorhanden
        $domain = preg_replace('#:\d+$#', '', $domain);
        
        // Pfade entfernen falls vorhanden
        $domain = strtok($domain, '/');
        
        // Zu lowercase
        $domain = strtolower($domain);
        
        return $domain;
    }

    /**
     * Domain in Datenbank anlegen oder aktualisieren
     */
    private function createOrUpdateDomain(string $domain, bool $autoInject): int
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('consent_manager_domain'));
        
        // Prüfen ob Domain bereits existiert
        $existing = rex_sql::factory();
        $existing->setQuery('SELECT id FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);
        
        if ($existing->getRows() > 0) {
            // Update
            $sql->setWhere(['uid' => $domain]);
            $sql->setValue('auto_inject', $autoInject ? 1 : 0);
            $sql->setValue('auto_inject_reload_on_consent', 0);
            $sql->setValue('auto_inject_delay', 0);
            $sql->setValue('auto_inject_focus', 1);
            $sql->update();
            
            return (int) $existing->getValue('id');
        }
        
        // Insert
        $sql->setValue('uid', $domain);
        $sql->setValue('auto_inject', $autoInject ? 1 : 0);
        $sql->setValue('auto_inject_reload_on_consent', 0);
        $sql->setValue('auto_inject_delay', 0);
        $sql->setValue('auto_inject_focus', 1);
        $sql->insert();
        
        return (int) $sql->getLastId();
    }

    /**
     * Standard-Setup importieren (ruft existierende Setup-Funktion auf)
     */
    private function importStandardSetup(): void
    {
        $importer = new \FriendsOfRedaxo\ConsentManager\Importer();
        $importer->importStandard();
    }

    /**
     * Minimal-Setup importieren
     */
    private function importMinimalSetup(): void
    {
        $importer = new \FriendsOfRedaxo\ConsentManager\Importer();
        $importer->importMinimal();
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
        $sql->setValue('theme_uid', $themeUid);
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
        $sql->setQuery('SELECT theme_uid FROM ' . rex::getTable('consent_manager_domain') . ' WHERE uid = ?', [$domain]);
        if ($sql->getRows() > 0) {
            $validation['domain_exists'] = true;
            $validation['theme_assigned'] = '' !== $sql->getValue('theme_uid');
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
        flush();
    }
}
