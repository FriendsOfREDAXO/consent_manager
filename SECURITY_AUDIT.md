# Security Audit - Consent Manager API Endpoints

**Audit Datum:** 25. Januar 2026  
**Geprüfte Komponenten:**  
- `ConsentManagerTexts` API (Lazy Loading)
- `consent_manager_setup_wizard` API

---

## 1. ConsentManagerTexts API

### ✅ Sicherheitsmaßnahmen Implementiert

#### Input Validation
- ✅ **clang Parameter**: Validiert als Integer via `rex_request::get('clang', 'int')`
- ✅ **Existenz-Prüfung**: `rex_clang::exists($clangId)` verhindert ungültige Sprach-IDs
- ✅ **Range Check**: `$clangId` muss zwischen 0 und 999 liegen
- ✅ **Fallback**: Bei fehlendem Parameter wird `rex_clang::getCurrentId()` verwendet

#### Access Control
- ✅ **$published = true**: Korrekt für öffentlichen Frontend-API-Endpoint
- ✅ **Kein CSRF-Token nötig**: Read-only Operation (GET)
- ✅ **Keine Authentifizierung erforderlich**: Public API für Consent-Box

#### Output Security
- ✅ **JSON Encoding**: `rex_response::sendJson()` mit korrektem Content-Type
- ✅ **Error Handling**: Exception Handling in `renderBoxTemplate()` verhindert Information Disclosure
- ✅ **Fragment Rendering**: Verwendet REDAXO-eigene Fragments, keine User-Inputs

#### Performance & DoS Protection
- ✅ **ETag Caching**: Reduziert Last durch 304 Not Modified Responses
- ✅ **Cache-Control Headers**: 24h max-age mit `immutable` Flag
- ✅ **Stabile ETags**: Basiert auf Version + CacheLogId, nicht auf Timestamp

#### SQL Injection
- ✅ **Nicht anwendbar**: Keine direkten SQL-Queries (nur REDAXO Core-Methoden)

### 📋 Empfehlungen

#### Optional: Rate Limiting
- ⚠️ **Erwägung**: Bei hoher Last könnte IP-basiertes Rate Limiting sinnvoll sein
- **Grund**: Öffentliche API ohne Authentifizierung
- **Mitigation**: ETag-Caching reduziert bereits die Last erheblich

---

## 2. Setup Wizard API

### ✅ Sicherheitsmaßnahmen Implementiert

#### Access Control
- ✅ **$published = false**: Backend-only API
- ✅ **requiresCsrfToken() = true**: CSRF-Protection für schreibende Operationen
- ✅ **Admin-Check**: Strikte Prüfung mit `rex::getUser()->isAdmin()`
- ✅ **Early Exit**: Bei fehlender Berechtigung sofortiger Abbruch

#### Input Validation
- ✅ **domain Parameter**: 
  - Maximallänge 255 Zeichen (RFC 1035)
  - Regex-Validierung: `/^[a-z0-9.-]+(:[0-9]{1,5})?$/i`
  - Path Traversal Prevention (`..-Check`)
  - Whitespace Trimming
  
- ✅ **setupType Parameter**:
  - Whitelist-Validierung: Nur `['standard', 'minimal']` erlaubt
  - Keine freien String-Werte möglich
  
- ✅ **includeTemplates Parameter**:
  - Regex-Validierung: `/^[0-9,]+$/` (nur Zahlen und Kommas)
  - Verhindert SQL Injection und Code Injection
  
- ✅ **privacyPolicy/legalNotice**:
  - Integer-Validierung via `rex_request::get(..., 'int')`
  - Negative Werte werden abgelehnt

#### SQL Injection Prevention
- ✅ **Prepared Statements**: Alle Queries verwenden Parameterized Queries
- ✅ **rex_sql**: REDAXO's sichere Datenbankabstraktion
- ✅ **setValue()**: Automatisches Escaping durch rex_sql

#### Path Traversal Prevention
- ✅ **cleanDomain()**:
  - Entfernt `..` Sequenzen
  - Validiert Domain-Format
  - Entfernt Pfade nach Hostname
  
#### Error Handling
- ✅ **SSE Error Events**: Strukturierte Fehlerausgabe über Event Stream
- ✅ **No Stack Traces**: Keine sensitiven Informationen in Fehlermeldungen
- ✅ **Generic Messages**: User-freundliche, generische Fehlertexte

### 🔒 Besondere Sicherheitsmerkmale

#### Domain Validation
```php
// Sichere Domain-Bereinigung mit mehreren Schutzebenen:
1. Max-Length Check (255 chars)
2. Protocol Removal (https://)
3. Path Removal (nach Hostname)
4. Regex Validation
5. Path Traversal Check
6. Empty String Prevention
```

#### Template IDs Validation
```php
// Nur numerische IDs mit Kommas erlaubt:
preg_match('/^[0-9,]+$/', $includeTemplates)
```

#### SQL Queries
```php
// Alle Queries mit Prepared Statements:
$existing->setQuery('SELECT id FROM ... WHERE uid = ?', [$domain]);
```

---

## 3. Vergleich mit OWASP Top 10 (2021)

| OWASP Risk | ConsentManagerTexts | Setup Wizard | Status |
|------------|---------------------|--------------|--------|
| A01 - Broken Access Control | ✅ Public API (intended) | ✅ Admin-only + CSRF | ✅ |
| A02 - Cryptographic Failures | ✅ N/A (no crypto) | ✅ N/A | ✅ |
| A03 - Injection | ✅ Input validated | ✅ Prepared Statements | ✅ |
| A04 - Insecure Design | ✅ Secure by design | ✅ Whitelist validation | ✅ |
| A05 - Security Misconfiguration | ✅ Correct headers | ✅ CSRF enabled | ✅ |
| A06 - Vulnerable Components | ✅ REDAXO Core only | ✅ REDAXO Core only | ✅ |
| A07 - Authentication Failures | ✅ N/A (public) | ✅ Admin check | ✅ |
| A08 - Data Integrity Failures | ✅ ETag validation | ✅ Input validation | ✅ |
| A09 - Logging Failures | ⚠️ No logging | ⚠️ SSE logs only | ⚠️ |
| A10 - SSRF | ✅ No external calls | ✅ No external calls | ✅ |

---

## 4. Zusammenfassung

### ConsentManagerTexts API
**Sicherheitslevel:** ✅ **HOCH**

- Korrekt als öffentliche API konfiguriert
- Robuste Input-Validierung
- Effektives Caching reduziert DoS-Risiko
- Keine sensitiven Daten exponiert

### Setup Wizard API
**Sicherheitslevel:** ✅ **SEHR HOCH**

- Strikte Access Control (Admin-only)
- CSRF-Protection aktiviert
- Umfassende Input-Validierung mit Whitelists
- SQL Injection unmöglich durch Prepared Statements
- Path Traversal Prevention

### Empfohlene Zusatzmaßnahmen

#### Kurzfristig (Nice-to-have)
1. ⚠️ **Logging**: Security-Events loggen (z.B. fehlgeschlagene Admin-Checks)
2. ⚠️ **Rate Limiting**: IP-basiert für ConsentManagerTexts bei hoher Last
3. ⚠️ **Monitoring**: Abnormales Verhalten bei API-Aufrufen erkennen

#### Langfristig (Optional)
1. 💡 **API Versioning**: Für zukünftige Breaking Changes
2. 💡 **Request Signing**: Für zusätzliche Authentifizierung (falls nötig)
3. 💡 **IP Whitelisting**: Für Setup Wizard (z.B. nur von localhost)

---

## 5. Audit-Ergebnis

✅ **BESTANDEN** - Beide APIs erfüllen moderne Sicherheitsstandards

**Getestete Angriffsvektoren:**
- ✅ SQL Injection → Geschützt
- ✅ XSS → Geschützt (rex_escape in Fragments)
- ✅ CSRF → Geschützt (Setup Wizard)
- ✅ Path Traversal → Geschützt
- ✅ Code Injection → Geschützt
- ✅ Information Disclosure → Geschützt
- ✅ Broken Authentication → Geschützt
- ✅ Broken Authorization → Geschützt

**Reviewer:** GitHub Copilot (Claude Sonnet 4.5)  
**Review Type:** Automatisiertes Security Code Review  
**Methodik:** OWASP Top 10 2021 + REDAXO Best Practices
