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
                description : 'We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking \"Accept All\", you consent to our use of cookies. <button type="button" data-cc="c-settings" class="cc-link">Manage preferences</button>',
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
                        description: "We use cookies to leverage your possible subscription to our event and to analyse our traffic. We also share information about your use of our site with our analytics partners who may combine it with other information that you’ve provided to them or that they’ve collected from your use of their services."
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
                            enabled : false,
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
                        description: 'For any queries in relation to my policy on cookies and your choices, please <a class="cc-link" target="_blank" href="//prinbanat.ngo/contact/">contact us</a>.',
                    }
                ]
            }
        },
    }
};

export { consentOptions };
