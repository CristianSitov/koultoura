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
                title : "Apreciem confiden??ialitatea dumneavoastr??",
                description : 'Folosim cookie-uri pentru a v?? ??mbun??t????i experien??a de navigare ??i pentru a analiza traficul dumneavoastr??. F??c??nd clic pe ???Accept toate???, sunte??i de acord cu utilizarea cookie-urilor. <button type="button" data-cc="c-settings" class="cc-link">Gestiona??i preferin??ele</button>',
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
                title : '<div>Set??ri cookie</div>',
                save_settings_btn : "Salveaz?? set??ri",
                accept_all_btn : "Accept toate",
                reject_all_btn : "Refuz toate",
                close_btn_label: "??nchide",
                cookie_table_headers : [
                    {col1: "Nume" },
                    {col2: "Domeniu" },
                    {col3: "Descriere" }
                ],
                blocks : [
                    {
                        title : "Acest site web folose??te cookie-uri",
                        description: "Folosim cookie-uri pentru a facilita ??nscrierea la evenimentul nostru."
                    },{
                        title : "Cookie-uri strict necesare",
                        description: 'Aceste cookie-uri sunt esen??iale pentru buna func??ionare a site-ului meu. F??r?? aceste cookie-uri, site-ul web nu ar func??iona corect.',
                        toggle : {
                            value : 'necessary',
                            enabled : true,
                            readonly: true
                        },
                        cookie_table: [
                            {
                                col1: 'why_culture_matters_session',
                                col2: 'prinbanat.ngo',
                                col3: 'Urm??rirea procesului de ??nregistrare.',
                                is_regex: false,
                            },
                            {
                                col1: 'XSRF-TOKEN',
                                col2: 'prinbanat.ngo',
                                col3: 'Asigurarea securit????ii c??mpurilor trimise.',
                                is_regex: false,
                            }
                        ]
                    },{
                        title : "Cookie-uri de performan???? ??i analiz??",
                        description: 'Aceste cookie-uri colecteaz?? informa??ii despre modul ??n care utiliza??i site-ul web, ce pagini a??i vizitat ??i pe ce link-uri a??i f??cut clic. Toate datele sunt anonimizate ??i nu pot fi folosite pentru a v?? identifica.',
                        toggle : {
                            value : 'analytics',
                            enabled : true,
                            readonly: false
                        },
                        cookie_table: [
                            {
                                col1: '^_ga',
                                col2: 'prinbanat.ngo',
                                col3: '??nregistreaz?? un ID unic care este utilizat pentru a genera date statistice despre modul ??n care vizitatorul folose??te site-ul web.',
                                path: window.location.pathname,
                                is_regex: true
                            },
                        ]
                    },{
                        title : "Mai multe informa??ii",
                        description: 'Pentru orice ??ntreb??ri legate de politica noastr?? privind cookie-urile ??i op??iunile dumneavoastr??, v?? rug??m <a class="cc-link" target="_blank" href="//prinbanat.ngo/contact/">s?? ne contacta??i</a>.',
                    }
                ]
            }
        }
    }
};

export { consentOptions };
