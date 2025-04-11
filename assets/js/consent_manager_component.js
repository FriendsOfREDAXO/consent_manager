class ConsentManagerComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._consents = [];
        this._cookieData = {};
    }

    static get observedAttributes() {
        return ['domain', 'initially-hidden', 'force-reload'];
    }

    connectedCallback() {
        this._initializeComponent();
    }

    _initializeComponent() {
        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                    position: fixed;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 999999;
                }
                .consent-background {
                    position: fixed;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.4);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 1em;
                }
                .consent-wrapper {
                    font-family: Verdana, Geneva, sans-serif;
                    font-size: 14px;
                    line-height: 1.5;
                    background: #fefefe;
                    color: #444;
                    padding: 2em;
                    width: 100%;
                    max-width: 65em;
                    max-height: 95vh;
                    overflow-y: auto;
                    border-radius: 4px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                    animation: fadeIn 0.4s;
                }
                .consent-hidden {
                    display: none;
                }
                .consent-headline {
                    font-size: 1.2em;
                    font-weight: bold;
                    margin: 0 0 1em 0;
                }
                .consent-text {
                    margin: 0 0 1.5em 0;
                }
                .consent-cookiegroups {
                    margin: 0 0 1.5em 0;
                }
                .consent-cookiegroup-checkbox {
                    margin-bottom: 1em;
                }
                .consent-cookiegroup-checkbox label {
                    display: flex;
                    align-items: center;
                    font-weight: bold;
                    cursor: pointer;
                }
                .consent-cookiegroup-checkbox input {
                    margin-right: 0.7em;
                    transform: scale(1.2);
                }
                .consent-detail {
                    margin: 1.5em 0;
                }
                .consent-cookiegroup-title {
                    background: #f5f5f5;
                    padding: 0.5em 1em;
                    font-weight: bold;
                }
                .consent-cookiegroup-description {
                    padding: 0.5em 1em;
                    border-left: 1px solid #f5f5f5;
                }
                .consent-cookie {
                    padding: 0.5em 1em;
                    border-left: 1px solid #f5f5f5;
                    margin-top: 3px;
                }
                .consent-cookie span {
                    display: block;
                    margin-top: 0.5em;
                }
                .consent-buttons {
                    display: flex;
                    justify-content: flex-end;
                    gap: 0.5em;
                    margin-top: 1.5em;
                }
                .consent-buttons button {
                    padding: 0.5em 1em;
                    border: 1px solid #ccc;
                    background: #f5f5f5;
                    cursor: pointer;
                    border-radius: 3px;
                }
                .consent-buttons button:hover {
                    background: #e5e5e5;
                }
                .consent-accept-all {
                    background: #4CAF50 !important;
                    color: white;
                    border-color: #45a049 !important;
                }
                .consent-accept-all:hover {
                    background: #45a049 !important;
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
            </style>
            <div class="consent-background consent-hidden">
                <div class="consent-wrapper">
                    <slot></slot>
                </div>
            </div>`;

        this._setupEventListeners();
        this._initializeCookie();
    }

    _setupEventListeners() {
        // Event Listener für Buttons und Tastaturnavigation
        this.shadowRoot.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hide();
            }
            
            // Tab-Falle implementieren
            if (e.key === 'Tab') {
                const focusableElements = this.shadowRoot.querySelectorAll(
                    'button, [href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                const firstFocusable = focusableElements[0];
                const lastFocusable = focusableElements[focusableElements.length - 1];

                if (e.shiftKey && document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });

        // Fokus beim Öffnen setzen
        this.addEventListener('show', () => {
            const firstCheckbox = this.shadowRoot.querySelector('input[type="checkbox"]:not([disabled])');
            if (firstCheckbox) {
                firstCheckbox.focus();
            }
        });

        // Buttons Event Listener
        this.shadowRoot.addEventListener('click', (e) => {
            if (e.target.matches('.consent-accept-all')) {
                this._saveConsent('all');
                this._announceToScreenReader('Alle Cookies wurden akzeptiert');
            } else if (e.target.matches('.consent-accept-none')) {
                this._saveConsent('none');
                this._announceToScreenReader('Alle optionalen Cookies wurden abgelehnt');
            } else if (e.target.matches('.consent-save-selection')) {
                this._saveConsent('selection');
                this._announceToScreenReader('Ihre Cookie-Auswahl wurde gespeichert');
            }
        });
    }

    _initializeCookie() {
        const cookie = Cookies.get('consent_manager');
        if (cookie) {
            this._cookieData = JSON.parse(cookie);
            this._consents = this._cookieData.consents || [];
        }
    }

    _updateGoogleConsent(consents) {
        // Google Consent Mode v2 Aktualisierung
        const gtag = window.gtag || function(){};
        
        const consentUpdate = {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied',
            functionality_storage: 'granted',
            personalization_storage: 'granted',
            security_storage: 'granted'
        };

        // Überprüfen der Consent-Gruppen
        consents.forEach(consent => {
            if (consent === 'statistics') {
                consentUpdate.analytics_storage = 'granted';
            }
            if (consent === 'marketing') {
                consentUpdate.ad_storage = 'granted';
                consentUpdate.ad_user_data = 'granted';
                consentUpdate.ad_personalization = 'granted';
            }
        });

        gtag('consent', 'update', consentUpdate);
    }

    _saveConsent(type) {
        this._consents = [];
        this._cookieData = {
            consents: [],
            version: this.getAttribute('version'),
            consentid: this.getAttribute('consent-id'),
            cachelogid: this.getAttribute('cache-log-id')
        };

        if (type !== 'none') {
            const checkboxes = this.querySelectorAll('[data-cookie-uids]');
            checkboxes.forEach(checkbox => {
                const cookieUids = JSON.parse(checkbox.dataset.cookieUids);
                if (type === 'all' || checkbox.checked) {
                    this._consents.push(...cookieUids);
                }
            });
        }

        this._cookieData.consents = this._consents;
        
        // Cookie setzen
        Cookies.set('consent_manager', JSON.stringify(this._cookieData), {
            expires: parseInt(this.getAttribute('cookie-lifetime') || '365'),
            path: '/',
            domain: this.getAttribute('domain'),
            sameSite: 'Lax'
        });

        // Google Consent Mode v2 Update
        this._updateGoogleConsent(this._consents);

        // Event dispatchen
        this.dispatchEvent(new CustomEvent('consent-saved', {
            detail: this._consents,
            bubbles: true
        }));

        // Box schließen
        this.hide();

        // Seite neu laden wenn nötig
        if (this.hasAttribute('force-reload')) {
            window.location.reload();
        }
    }

    _announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('class', 'sr-only');
        announcement.textContent = message;
        document.body.appendChild(announcement);
        setTimeout(() => announcement.remove(), 1000);
    }

    show() {
        const background = this.shadowRoot.querySelector('.consent-background');
        background.classList.remove('consent-hidden');
        this.dispatchEvent(new CustomEvent('show'));
    }

    hide() {
        const background = this.shadowRoot.querySelector('.consent-background');
        background.classList.add('consent-hidden');
    }

    hasConsent(cookieUid) {
        return this._consents.includes(cookieUid);
    }
}

customElements.define('consent-manager', ConsentManagerComponent);