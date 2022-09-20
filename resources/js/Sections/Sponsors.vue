<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div id="partners" class="w-full pt-28">
            <section class="bg-white dark:bg-gray-900">
                <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-xl">
                    <div class="mt-3 space-y-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4 xl:gap-8 md:space-y-0 md:mb-16 md:mt-4">
                        <div class="col">
                            <h2 class="mb-4 text-3xl font-extrabold tracking-tight md:leading-4 text-gray-900 md:text-4xl">{{ $t("Organizers") }}</h2>
                            <a href="//prinbanat.ngo"
                               target="_blank"
                               class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                                <img src="assets/images/logos/asociatia_prin_banat.png" alt=""/>
                            </a>
                        </div>
                        <div class="col-span-3 md:gap-5">
                            <div class="row">
                                <h3 class="mb-4 text-lg font-bold tracking-tight md:leading-4 text-gray-900 md:text-lg">{{ $t('organization explained') }}</h3>
                            </div>
                            <div class="grid grid-cols-3 md:gap-5">
                                <a href="//centriphery.eu"
                                   target="_blank"
                                   class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                                    <img src="assets/images/logos/centriphery.png" alt=""/>
                                </a>
                                <a href="//camineinmiscare.ro"
                                   target="_blank"
                                   class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                                    <img src="assets/images/logos/camine_in_miscare.png" alt="Cămine în Mișcare"/>
                                </a>
                                <a href="//www.facebook.com/ambulantapentrumonumenteprinbanat"
                                   target="_blank"
                                   class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                                    <img src="assets/images/logos/ambulanta_pentru_monumente_banat.png" alt=""/>
                                </a>
                            </div>
                        </div>
                        <div class="col-span-4">
                            <p v-html="$t('joint effort')"
                                class="text-gray-500"/>
                        </div>
                    </div>
                    <h2 class="mb-2 text-3xl font-extrabold tracking-tight leading-tight text-gray-900 md:text-4xl">{{ $t('Strategic Partners') }}</h2>
                    <div class="mt-3 space-y-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4 xl:gap-8 md:space-y-0 md:mb-16 md:mt-4">
                        <a href="//europanostra.org"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/europa_nostra.png" alt=""/>
                        </a>
                        <a href="//uvt.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/universitate_de_vest_timisoara.png" alt=""/>
                        </a>
                        <a href="//asociatiamonumentum.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/asociatia_monumentum.png" alt=""/>
                        </a>
                    </div>
                    <h2 class="mb-2 text-3xl font-extrabold tracking-tight leading-tight text-gray-900 md:text-4xl">{{ $t('Financer') }}</h2>
                    <div class="mt-3 space-y-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4 xl:gap-8 md:space-y-0 md:mb-16 md:mt-4">
                        <a href="#"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/administratia_fondului_cultural_national.png" alt=""/>
                        </a>
                        <a href="#"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/centrul_de_proiecte_timisoara.png" alt=""/>
                        </a>
                        <a href="#"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/consiliul_judetean_timis.png" alt=""/>
                        </a>
                        <a href="#"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img :src="'/why-culture-matters/assets/images/logos/europa_creativa_' + $page.props.locale + '.png'" alt=""/>
                        </a>
                    </div>
                    <h2 class="mb-2 text-3xl font-extrabold tracking-tight leading-tight text-gray-900 md:text-4xl">{{ $t('Local Partners') }}</h2>
                    <div class="mt-3 space-y-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4 xl:gap-8 md:space-y-0 md:mb-16 md:mt-4">
                        <a href="//patrimoniu.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/institutul_national_al_patrimoniului.png" alt=""/>
                        </a>
                        <a href="//oartimis.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/ordinul_arhitectilor_din_romania_filiala_timis.png" alt=""/>
                        </a>
                        <a href="//www.facebook.com/asociatiadela4/"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/asociatia_de_la_4.png" alt=""/>
                        </a>
                        <a href="//fitt.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/fundația_judeteana_pentru_tineret_timis.png" alt=""/>
                        </a>
                        <a href="//garana-jazz.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/garana_jazz_festival.png" alt="Gărâna Jazz"/>
                        </a>
                        <a href="//turismtimis.ro"
                           target="_blank"
                           class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/discover_timis.png" alt="Discover Timiș"/>
                        </a>
                    </div>
                    <h2 class="mb-2 text-3xl font-extrabold tracking-tight leading-tight text-gray-900 md:text-4xl">{{ $t('International Partners') }}</h2>
                    <div class="mt-3 space-y-4 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-4 xl:gap-8 md:space-y-0 md:mb-16 md:mt-4">
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/festival_der_regionen_austria.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/new_culture_foundation_bulgaria.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/dansehallerne_denmark.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/espoo_city_theatre_finland.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/la_manufacture_collectif_contemporain_france.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/rijeka_2020_european_capital_of_culture.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/cultura_nova_festival_netherlands.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/walk_and_talk_azores.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/romanian_community_from_serbia.png" alt=""/>
                        </a>
                        <a href="#" class="flex justify-center items-center p-8 text-center bg-gray-50 rounded hover:bg-gray-100">
                            <img src="assets/images/logos/fablab_zagreb.png" alt=""/>
                        </a>
                    </div>
                </div>
            </section>
            <p class="w-1/2 mx-auto text-center text-xs text-gray-400 leading-tight">{{ $t('AFCN disclaimer') }}</p>
        </div>
    </div>
</template>

