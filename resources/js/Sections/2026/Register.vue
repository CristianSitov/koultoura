<script setup>
import { ref } from 'vue';

/*
 * Interest capture. Registration is not open yet and there is no endpoint for
 * it, so the form confirms client-side — point `submit` at the newsletter or
 * registration backend once it exists, and link the header's Register button
 * straight at the real form.
 */
const email = ref('');
const submitted = ref(false);

function submit() {
    if (email.value) {
        submitted.value = true;
    }
}
</script>

<template>
    <section id="register" class="wcm26-register">
        <div class="wcm26-register-inner">
            <h2>
                <span>Free entry.</span>
                <span>Registration required.</span>
            </h2>

            <div>
                <p class="wcm26-register-title">Registration opens soon.</p>
                <p class="wcm26-register-note">Leave your email and we will let you know the moment it does.</p>

                <form v-if="!submitted" @submit.prevent="submit">
                    <input
                        v-model="email"
                        class="input"
                        type="email"
                        name="email"
                        required
                        placeholder="you@example.org"
                        aria-label="Email address"
                    />
                    <button type="submit" class="btn btn-ghost btn-flush">Notify me</button>
                </form>

                <p v-else class="wcm26-register-done">
                    Noted. We will write to {{ email }} when registration opens.
                </p>
            </div>
        </div>
    </section>
</template>
