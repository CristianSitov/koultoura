<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;

/*
 * The first-announcement guests, in English and Romanian. Idempotent:
 * re-running it updates the existing rows by slug rather than duplicating them.
 *
 * The Romanian copy is a translation of the English, except Andreea Lazea's,
 * which is condensed from the Romanian half of her own bio document.
 */
class Guests2026Seeder extends Seeder
{
    public function run(): void
    {
        $guests = [
            [
                'position' => 1,
                'slug' => 'oleksandra-kovalchuk',
                'full_name' => 'Oleksandra Kovalchuk',
                'avatar' => '/assets/2026/guests/kovalchuk.jpg',
                'role' => 'Speaker & workshop',
                'institution' => 'Museum for Change, Ukraine',
                'description' => 'Ukrainian cultural manager, heritage expert and co-founder of Museum for Change. Since the start of the full-scale invasion she has led emergency heritage protection initiatives across Ukraine — evacuation, safeguarding collections, documentation and institutional resilience. Deputy Director of the Odesa National Fine Arts Museum and incoming Managing Director of the Ukraine Cultural Heritage Fund.',
            ],
            [
                'position' => 2,
                'slug' => 'andras-mudra',
                'full_name' => 'András Mudra',
                'avatar' => '/assets/2026/guests/mudra.jpg',
                'role' => '',
                'institution' => 'KÉK — Contemporary Architecture Centre, Budapest',
                'description' => 'Architect at DANU architecture studio, graduate of the Budapest University of Technology and Economics. At KÉK since 2022, he has led Budapest100, the KÉK Mentor Program, ModernTéka and Pecha Kucha Night Budapest. His interests are late modern architectural heritage, its public perception and preservation, and grassroots urban initiatives.',
            ],
            [
                'position' => 3,
                'slug' => 'barbara-szij',
                'full_name' => 'Barbara Szij',
                'avatar' => '/assets/2026/guests/szij.jpg',
                'role' => '',
                'institution' => 'KÉK — Contemporary Architecture Centre, Budapest',
                'description' => 'Art historian specialising in architectural and urban history. A Budapest100 researcher since 2014 and Head of Research since 2016, she has worked on URBACT Come In!, DANurB and Csepel Works: Open Factory Weekend. Her interest lies in involving broader audiences in the preservation and public discourse of built heritage.',
            ],
            [
                'position' => 4,
                'slug' => 'raluca-maria-trifa',
                'full_name' => 'Raluca-Maria Trifa',
                'avatar' => '/assets/2026/guests/trifa.jpg',
                'role' => '',
                'institution' => '“Ion Mincu” University of Architecture and Urbanism, Bucharest',
                'description' => 'Architect, researcher and lecturer. Her work focuses on industrial architecture and the transformation of post-industrial cities, and the relationship between heritage, memory and identity. Author of Historic Industrial Architecture: Possibilities for Sustainable Recovery. The Case of Timișoara (2023); New Europe College fellow 2024–2025.',
            ],
            [
                'position' => 5,
                'slug' => 'gabriela-robeci',
                'full_name' => 'Gabriela Robeci',
                'avatar' => '/assets/2026/guests/robeci.jpg',
                'role' => 'Moderator',
                'institution' => 'Faculty of Arts and Design, West University of Timișoara',
                'description' => 'Research assistant with a background in the history and theory of visual arts, teaching at the Faculty of Arts and Design since 2022. Her research covers visual arts and built cultural heritage from the early 20th century to the present, examining site-specific interventions, immersive spaces and temporary artworks.',
            ],
            [
                'position' => 6,
                'slug' => 'nicoleta-musat',
                'full_name' => 'Nicoleta Mușat',
                'avatar' => '/assets/2026/guests/musat.jpg',
                'role' => '',
                'institution' => 'West University of Timișoara',
                'description' => 'Educator and field researcher, trained as a philologist in Timișoara. Her fieldwork in the Banat region covers funeral rituals, forms of heritage and how heritage is created within communities, alongside migration and the stories that accompany it. Recently she studies Romanian as a heritage language in communities abroad.',
            ],
            [
                'position' => 7,
                'slug' => 'iulia-iordan',
                'full_name' => 'Iulia Iordan',
                'avatar' => '/assets/2026/guests/iordan.jpg',
                'role' => 'Speaker & workshop',
                'institution' => 'Da’DeCe Association',
                'description' => 'Writer, museum educator and curator with over twenty years in museum education, first at the National Museum of Art of Romania and now at Da’DeCe. She creates interactive exhibitions, mediation workshops and cultural trails, and co-founded De Basm, the Romanian Association of Writers for Children and Young Adults.',
            ],
            [
                'position' => 8,
                'slug' => 'andela-petrovic',
                'full_name' => 'Anđela Petrović',
                'avatar' => '/assets/2026/guests/petrovic.jpg',
                'role' => '',
                'institution' => 'Youth.Heritage.Europe.',
                'description' => 'Curator at the Gallery of the Serbian Academy of Sciences and Arts and PhD candidate in Museology and Heritology at the University of Belgrade. Her work focuses on sensory museology, museum communication and heritage interpretation, with an interest in multisensory and affective approaches to accessibility. Joins as a representative of Youth.Heritage.Europe., a partner of this year’s symposium.',
            ],
            [
                'position' => 9,
                'slug' => 'andreea-lazea',
                'full_name' => 'Andreea Lazea',
                'avatar' => '/assets/2026/guests/lazea.jpg',
                'role' => '',
                'institution' => 'Faculty of Arts and Design, West University of Timișoara',
                'description' => 'Associate Professor at the Faculty of Arts and Design, West University of Timișoara, where she teaches cultural heritage, aesthetics and art history, and Director of CICASP, the centre for research in curatorship, art history and criticism, and heritage studies. Her recent research covers contemporary public art in Romania and the capacity of heritage to foster creativity. Author of Public Art in Timișoara after 1989 (2025); also active as a curator.',
            ],
        ];

        $romanian = [
            'oleksandra-kovalchuk' => [
                'role' => 'Vorbitoare & atelier',
                'institution' => 'Museum for Change, Ucraina',
                'description' => 'Manager cultural, expertă în patrimoniu și cofondatoare a Museum for Change. De la începutul invaziei pe scară largă a coordonat inițiative de protecție de urgență a patrimoniului în toată Ucraina — evacuare, punerea în siguranță a colecțiilor, documentare și reziliență instituțională. Director adjunct al Muzeului Național de Arte Frumoase din Odesa și viitoare director general al Fondului pentru Patrimoniul Cultural al Ucrainei.',
            ],
            'andras-mudra' => [
                'role' => '',
                'institution' => 'KÉK — Centrul de Arhitectură Contemporană, Budapesta',
                'description' => 'Arhitect la studioul DANU architecture, absolvent al Universității de Tehnologie și Economie din Budapesta. La KÉK din 2022, a coordonat Budapest100, Programul de mentorat KÉK, ModernTéka și Pecha Kucha Night Budapesta. Îl interesează patrimoniul arhitectural modern târziu, percepția publică și conservarea acestuia, precum și inițiativele urbane de tip grassroots.',
            ],
            'barbara-szij' => [
                'role' => '',
                'institution' => 'KÉK — Centrul de Arhitectură Contemporană, Budapesta',
                'description' => 'Istoric de artă specializată în istoria arhitecturii și a orașului. Cercetătoare în Budapest100 din 2014 și coordonatoare a cercetării din 2016, a lucrat la URBACT Come In!, DANurB și Csepel Works: Open Factory Weekend. O interesează implicarea unui public larg în conservarea și în discuția publică despre patrimoniul construit.',
            ],
            'raluca-maria-trifa' => [
                'role' => '',
                'institution' => 'Universitatea de Arhitectură și Urbanism „Ion Mincu”, București',
                'description' => 'Arhitectă, cercetătoare și cadru didactic. Activitatea sa se concentrează pe arhitectura industrială și pe transformarea orașelor post-industriale, precum și pe relația dintre patrimoniu, memorie și identitate. Autoare a volumului Arhitectura industrială istorică: posibilități de recuperare sustenabilă. Cazul Timișoarei (2023); bursieră New Europe College 2024–2025.',
            ],
            'gabriela-robeci' => [
                'role' => 'Moderatoare',
                'institution' => 'Facultatea de Arte și Design, Universitatea de Vest din Timișoara',
                'description' => 'Asistentă de cercetare cu formare în istoria și teoria artelor vizuale, predă la Facultatea de Arte și Design din 2022. Cercetarea sa acoperă artele vizuale și patrimoniul cultural construit, de la începutul secolului XX până în prezent, cu accent pe intervenții site-specific, spații imersive și lucrări temporare.',
            ],
            'nicoleta-musat' => [
                'role' => '',
                'institution' => 'Universitatea de Vest din Timișoara',
                'description' => 'Educatoare și cercetătoare de teren, formată ca filolog la Timișoara. Cercetarea sa de teren în Banat acoperă ritualurile funerare, formele patrimoniului și modul în care patrimoniul se creează în interiorul comunităților, alături de migrație și de poveștile care o însoțesc. În ultimii ani studiază limba română ca limbă de patrimoniu în comunitățile din străinătate.',
            ],
            'iulia-iordan' => [
                'role' => 'Vorbitoare & atelier',
                'institution' => 'Asociația Da’DeCe',
                'description' => 'Scriitoare, educatoare de muzeu și curatoare, cu peste douăzeci de ani de experiență în educația muzeală, mai întâi la Muzeul Național de Artă al României, iar acum la Da’DeCe. Creează expoziții interactive, ateliere de mediere și trasee culturale și este cofondatoare a De Basm, Asociația Română a Scriitorilor pentru Copii și Tineret.',
            ],
            'andela-petrovic' => [
                'role' => '',
                'institution' => 'Youth.Heritage.Europe.',
                'description' => 'Curatoare la Galeria Academiei Sârbe de Științe și Arte și doctorandă în muzeologie și heritologie la Universitatea din Belgrad. Activitatea sa se concentrează pe muzeologia senzorială, comunicarea muzeală și interpretarea patrimoniului, cu interes pentru abordările multisenzoriale și afective ale accesibilității. Participă ca reprezentantă a Youth.Heritage.Europe., partener al ediției din acest an.',
            ],
            'andreea-lazea' => [
                'role' => '',
                'institution' => 'Facultatea de Arte și Design, Universitatea de Vest din Timișoara',
                'description' => 'Conferențiară universitară la Facultatea de Arte și Design, Universitatea de Vest din Timișoara, unde predă cursuri de patrimoniu cultural, estetică și istoria artei, și directoare a Centrului de Cercetări în Curatoriat, Istoria și Critica de Artă, Studii Patrimoniale (CICASP). Cercetările sale recente vizează arta publică contemporană din România și capacitatea patrimoniului de a stimula creativitatea. Autoare a volumului „Arta publică în Timișoara după 1989” (2025); desfășoară și activitate curatorială.',
            ],
        ];

        foreach ($guests as $guest) {
            // Pinned to the 2026 connection: without it a seed run from the CLI
            // would land in whatever DB_CONNECTION happens to be the default.
            $person = Person::on('wcm_2026')->firstOrNew(['slug' => $guest['slug']]);
            $person->fill([
                'full_name' => $guest['full_name'],
                'avatar' => $guest['avatar'],
                'position' => $guest['position'],
            ])->save();

            $text = ['en' => $guest] + ['ro' => $romanian[$guest['slug']] ?? null];

            foreach (array_filter($text) as $locale => $copy) {
                $translation = $person->translateOrNew($locale);
                $translation->role = $copy['role'];
                $translation->institution = $copy['institution'];
                $translation->description = $copy['description'];
            }

            $person->save();
        }
    }
}
