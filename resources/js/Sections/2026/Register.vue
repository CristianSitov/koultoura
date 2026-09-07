<script setup>
/*
 * Registration is open, so this band is a way in rather than a waiting list.
 * It used to hold an email box that confirmed client-side and stored nothing —
 * which was honest enough while there was no form to send anyone to, and
 * misleading the moment there was.
 */
defineProps({
    base: { type: String, default: '/2026' },
});
</script>

<template>
    <section id="register" class="wcm26-register">
        <div class="wcm26-register-inner">
            <h2>
                <span>{{ $t('Pay what you can.') }}</span>
                <span>{{ $t('Registration required.') }}</span>
            </h2>

            <div>
                <p class="wcm26-register-title">{{ $t('register.band.title') }}</p>
                <p class="wcm26-register-note">{{ $t('register.band.note') }}</p>

                <p class="wcm26-register-actions">
                    <a :href="`${base}/register`" class="btn btn-primary btn-flush" style="height: 44px">
                        {{ $t('register.submit') }}
                    </a>
                </p>
            </div>
        </div>
    </section>
</template>
