<script setup>
import SectionHead from './SectionHead.vue';

defineProps({
    expanded: { type: Boolean, default: false },
});

defineEmits(['toggle']);
</script>

<template>
    <section id="themes" class="wcm26-section">
        <div class="wcm26-head-split">
            <SectionHead n="03" title="2026 Themes" />
            <button type="button" class="btn btn-ghost btn-flush" style="height: 40px" @click="$emit('toggle')">
                {{ expanded ? 'Show less' : 'Read the full themes' }}
            </button>
        </div>

        <div class="wcm26-themes">
            <article class="wcm26-theme">
                <p class="wcm26-theme-n"><span class="wcm26-square"></span>I</p>
                <h3>Heritage Under Threat</h3>
                <p class="wcm26-theme-q">How do we protect heritage in times of crisis and accelerated change?</p>
                <p class="wcm26-theme-p wcm26-theme-p-grow">
                    The section explores the impact of war, political instability, climate and environmental risks,
                    abandonment, demographic change and other pressures on cultural heritage, with a focus on
                    protection, documentation, preparedness, recovery and community resilience.
                </p>
            </article>

            <article class="wcm26-theme">
                <p class="wcm26-theme-n"><span class="wcm26-square"></span>II</p>
                <h3>Heritage Literacy &amp; Education</h3>
                <p class="wcm26-theme-q">How do we learn to recognise, understand and care for heritage?</p>
                <p class="wcm26-theme-p">
                    From formal and non-formal education to participatory practices and learning through place, this
                    section explores how heritage knowledge can become accessible, relevant and actionable across
                    generations.
                </p>
                <p v-if="expanded" class="wcm26-theme-p">
                    The 2026 edition will also introduce the Heritage School, a new educational initiative that will be
                    piloted during the symposium and further developed in 2027.
                </p>
            </article>

            <article class="wcm26-theme">
                <p class="wcm26-theme-n"><span class="wcm26-square"></span>III</p>
                <h3>Heritage Narratives</h3>
                <p class="wcm26-theme-q">Who tells the stories of heritage — and whose stories do we remember?</p>
                <p class="wcm26-theme-p wcm26-theme-p-grow">
                    This section examines how narratives about places, buildings and communities are constructed,
                    transmitted and challenged, with particular attention to difficult, contested or overlooked
                    histories, cultural mediation and community participation.
                </p>
            </article>

            <article class="wcm26-theme wcm26-theme-new">
                <p class="wcm26-theme-n">
                    <span class="wcm26-square"></span>IV
                    <span class="tag tag-accent" style="margin-left: auto">New in 2026</span>
                </p>
                <h3>Heritage School</h3>
                <p class="wcm26-theme-q">Eight pilot workshops for children, young people and adults.</p>
                <p class="wcm26-theme-p">
                    The 2026 edition of Why Culture Matters will mark the conceptual and programmatic launch of the
                    Heritage School, a new educational initiative dedicated to developing meaningful and accessible ways
                    of learning about cultural heritage.
                </p>
                <div v-if="expanded" class="wcm26-theme-more">
                    <p>
                        Rather than treating heritage education as the simple transmission of historical information,
                        the Heritage School will explore participatory, place-based and intergenerational approaches
                        that encourage people to observe, question, understand and actively engage with the cultural
                        environment around them.
                    </p>
                    <p>
                        During Why Culture Matters 2026, the Heritage School will be activated through a pilot programme
                        of eight practical workshops for children, young people and adults. At the same time, invited
                        professionals and educators will contribute their experience and methodologies to shaping the
                        concept and future direction of the programme.
                    </p>
                    <p>
                        The symposium will therefore function as both a testing ground and a collaborative development
                        space for the Heritage School, ahead of its broader implementation in 2027.
                    </p>
                </div>
                <button type="button" class="btn btn-ghost btn-flush" @click="$emit('toggle')">
                    {{ expanded ? 'Show less' : 'Read the full themes' }}
                </button>
            </article>
        </div>
    </section>
</template>
