<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import { trans } from 'laravel-vue-i18n';
import { HandCoins, Handshake, Users } from 'lucide-vue-next';
import '../../../css/wcm2026.css';
import PageShell from '../../Sections/2026/PageShell.vue';
import SectionHead from '../../Sections/2026/SectionHead.vue';

/*
 * Three ways to support the symposium, each with its own mark: give money,
 * sponsor as a company, or come and help. The bank details sit in the first
 * block rather than in a footnote — they are the point of it.
 */
defineProps({
    base: { type: String, default: '/2026' },
});

const email = 'contact@prinbanat.ro';

// The address sits inside the sentence, where the copy puts it, rather than
// being bolted on at the end.
const withEmail = (key) =>
    trans(key, { email: `<a class="wcm26-support-mail" href="mailto:${email}">${email}</a>` });

// Not translated, and not retyped anywhere else on the site.
const account = {
    beneficiary: 'Asociația Prin Banat',
    ibanRon: 'RO13INGB0000999905728624',
    ibanEur: 'RO25INGB0000999908554891',
    bank: 'ING Bank',
    swift: 'INGBROBUXXX',
};
</script>

<template>
    <Head :title="`${$t('Support us')} · Why Culture Matters 2026`" />

    <PageShell :base="base">
        <section class="wcm26-section">
            <SectionHead n="→" :title="$t('Support us')" />

            <p class="wcm26-lead" style="max-width: 24ch">{{ $t('support.lead') }}</p>

            <div class="wcm26-support">
                <article class="wcm26-cued">
                    <span class="wcm26-cue" aria-hidden="true"><HandCoins :size="50" :stroke-width="1.25" /></span>

                    <div>
                        <h3 class="wcm26-support-title">{{ $t('support.donate.title') }}</h3>
                        <p class="wcm26-support-heading">{{ $t('support.donate.heading') }}</p>

                        <p>{{ $t('support.donate.p1') }}</p>
                        <p>{{ $t('support.donate.p2') }}</p>

                        <!-- The button belongs between the two halves of the
                             offer: donate while registering — here is how — or
                             else, below, straight to the account. -->
                        <p class="wcm26-form-actions">
                            <a :href="`${base}/register`" class="btn btn-secondary btn-flush" style="height: 44px">
                                {{ $t('Register and donate') }}
                            </a>
                        </p>

                        <p>{{ $t('support.donate.p3') }}</p>

                        <dl class="wcm26-account">
                            <dt>{{ $t('Beneficiary') }}</dt>
                            <dd>{{ account.beneficiary }}</dd>

                            <dt>IBAN <span class="wcm26-account-cur">RON</span></dt>
                            <dd class="wcm26-account-iban">{{ account.ibanRon }}</dd>

                            <dt>IBAN <span class="wcm26-account-cur">EUR</span></dt>
                            <dd class="wcm26-account-iban">{{ account.ibanEur }}</dd>

                            <dt>{{ $t('Bank') }}</dt>
                            <dd>{{ account.bank }}</dd>

                            <dt>SWIFT/BIC</dt>
                            <dd class="wcm26-account-iban">{{ account.swift }}</dd>

                            <dt>{{ $t('Payment details') }}</dt>
                            <dd>{{ $t('Donation Why Culture Matters') }}</dd>
                        </dl>

                        <p class="wcm26-support-close">{{ $t('support.donate.close') }}</p>
                    </div>
                </article>

                <article class="wcm26-cued">
                    <span class="wcm26-cue" aria-hidden="true"><Handshake :size="50" :stroke-width="1.25" /></span>

                    <div>
                        <h3 class="wcm26-support-title">{{ $t('support.sponsor.title') }}</h3>
                        <p class="wcm26-support-heading">{{ $t('support.sponsor.heading') }}</p>

                        <p>{{ $t('support.sponsor.p1') }}</p>
                        <p v-html="withEmail('support.sponsor.p2')"></p>

                        <p class="wcm26-support-close">{{ $t('support.sponsor.close') }}</p>
                    </div>
                </article>

                <article class="wcm26-cued">
                    <span class="wcm26-cue" aria-hidden="true"><Users :size="50" :stroke-width="1.25" /></span>

                    <div>
                        <h3 class="wcm26-support-title">{{ $t('support.volunteer.title') }}</h3>
                        <p class="wcm26-support-heading">{{ $t('support.volunteer.heading') }}</p>

                        <p>{{ $t('support.volunteer.p1') }}</p>
                        <p v-html="withEmail('support.volunteer.p2')"></p>
                    </div>
                </article>
            </div>
        </section>
    </PageShell>
</template>
