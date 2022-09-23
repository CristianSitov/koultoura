// See: https://github.com/orestbida/cookieconsent#all-configuration-options
import emitter from "./emitter";

const consentOptions = {
    current_lang: document.documentElement.getAttribute('lang'),
    auto_language: 'document',
    autoclear_cookies: true,
    cookie_name: 'why_culture_matters_consent',
    cookie_expiration: 365,
    force_consent: false,
    page_scripts: true,
    onAccept: function (cookie) {
        emitter.emit("consentAccepted", { consent: cookie.level });
    },
    gui_options: {
        consent_modal: {
            layout: 'cloud',
            position: 'bottom center',
            transition: 'slide',
            swap_buttons: true
        },
        settings_modal: {
            layout: 'box',
            position: 'left',
            transition: 'slide',
            swap_buttons: true
        }
    },
    languages: {
        'en' : {
            consent_modal : {
                title : "We value your privacy",
                description : 'We use cookies to enhance your browsing experience and analyze our traffic. By clicking \"Accept All\", you consent to our use of cookies. <button type="button" data-cc="c-settings" class="cc-link">Manage preferences</button>',
                primary_btn: {
                    text: 'Accept',
                    role: 'accept_all'
                },
                secondary_btn: {
                    text : 'Reject',
                    role : 'accept_necessary'
                }
            },
            settings_modal : {
                title : '<div>Cookie settings</div>',
                save_settings_btn : "Save settings",
                accept_all_btn : "Accept all",
                reject_all_btn : "Reject all",
                close_btn_label: "Close",
                cookie_table_headers : [
                    {col1: "Name" },
                    {col2: "Domain" },
                    {col3: "Description" }
                ],
                blocks : [
                    {
                        title : "This website uses cookies",
                        description: "We use cookies to guide you through our event subscription process and to secure your experience on our website."
                    },{
                        title : "Strictly necessary cookies",
                        description: 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
                        toggle : {
                            value : 'necessary',
                            enabled : true,
                            readonly: true
                        },
                        cookie_table: [
                            {
                                col1: 'why_culture_matters_session',
                                col2: 'prinbanat.ngo',
                                col3: 'Tracking your registration funnel.',
                                is_regex: false,
                            },
                            {
                                col1: 'XSRF-TOKEN',
                                col2: 'prinbanat.ngo',
                                col3: 'Ensuring security on submitted fields.',
                                is_regex: false,
                            }
                        ]
                    },{
                        title : "Performance and analytics cookies",
                        description: 'These cookies collect information about how you use the website, which pages you visited and which links you clicked on. All of the data is anonymized and cannot be used to identify you',
                        toggle : {
                            value : 'analytics',
                            enabled : true,
                            readonly: false
                        },
                        cookie_table: [
                            {
                                col1: '^_ga',
                                col2: 'prinbanat.ngo',
                                col3: 'Registers a unique ID that is used to generate statistical data on how the visitor uses the website.',
                                path: window.location.pathname,
                                is_regex: true
                            },
                        ]
                    },{
                        title : "More information",
                        description: 'For any queries in relation to our policy on cookies and your choices, please <a class="cc-link" target="_blank" href="//prinbanat.ngo/contact/">contact us</a>.',
                    }
                ]
            }
        },
        'ro': {
            consent_modal : {
                title : "Apreciem confidențialitatea dumneavoastră",
                description : 'Folosim cookie-uri pentru a vă îmbunătăți experiența de navigare și pentru a analiza traficul dumneavoastră. Făcând clic pe „Accept toate”, sunteți de acord cu utilizarea cookie-urilor. <button type="button" data-cc="c-settings" class="cc-link">Gestionați preferințele</button>',
                primary_btn: {
                    text: 'Accept',
                    role: 'accept_all'
                },
                secondary_btn: {
                    text : 'Refuz',
                    role : 'accept_necessary'
                }
            },
            settings_modal : {
                title : '<div>Setări cookie</div>',
                save_settings_btn : "Salvează setări",
                accept_all_btn : "Accept toate",
                reject_all_btn : "Refuz toate",
                close_btn_label: "Închide",
                cookie_table_headers : [
                    {col1: "Nume" },
                    {col2: "Domeniu" },
                    {col3: "Descriere" }
                ],
                blocks : [
                    {
                        title : "Acest site web folosește cookie-uri",
                        description: "Folosim cookie-uri pentru a facilita înscrierea la evenimentul nostru."
                    },{
                        title : "Cookie-uri strict necesare",
                        description: 'Aceste cookie-uri sunt esențiale pentru buna funcționare a site-ului meu. Fără aceste cookie-uri, site-ul web nu ar funcționa corect.',
                        toggle : {
                            value : 'necessary',
                            enabled : true,
                            readonly: true
                        },
                        cookie_table: [
                            {
                                col1: 'why_culture_matters_session',
                                col2: 'prinbanat.ngo',
                                col3: 'Urmărirea procesului de înregistrare.',
                                is_regex: false,
                            },
                            {
                                col1: 'XSRF-TOKEN',
                                col2: 'prinbanat.ngo',
                                col3: 'Asigurarea securității câmpurilor trimise.',
                                is_regex: false,
                            }
                        ]
                    },{
                        title : "Cookie-uri de performanță și analiză",
                        description: 'Aceste cookie-uri colectează informații despre modul în care utilizați site-ul web, ce pagini ați vizitat și pe ce link-uri ați făcut clic. Toate datele sunt anonimizate și nu pot fi folosite pentru a vă identifica.',
                        toggle : {
                            value : 'analytics',
                            enabled : true,
                            readonly: false
                        },
                        cookie_table: [
                            {
                                col1: '^_ga',
                                col2: 'prinbanat.ngo',
                                col3: 'Înregistrează un ID unic care este utilizat pentru a genera date statistice despre modul în care vizitatorul folosește site-ul web.',
                                path: window.location.pathname,
                                is_regex: true
                            },
                        ]
                    },{
                        title : "Mai multe informații",
                        description: 'Pentru orice întrebări legate de politica noastră privind cookie-urile și opțiunile dumneavoastră, vă rugăm <a class="cc-link" target="_blank" href="//prinbanat.ngo/contact/">să ne contactați</a>.',
                    }
                ]
            }
        }
    }
};

export { consentOptions };
