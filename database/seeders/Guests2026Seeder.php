<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;

/*
 * The 2026 guests, as the organisers wrote them.
 *
 * This file is the copy of record. The texts are the bios from the guests'
 * own Drive folders — the ones marked ready to use, not a summary of them —
 * transcribed here because the server has no Drive credentials and, by
 * decision, is not getting any: an API key that can read a private folder is
 * not worth leaving on a web server for a list that changes a few times a year.
 *
 * Adding a guest means adding a row here and committing their portrait under
 * public/assets/2026/guests. Run `php artisan guests:sync` to apply it.
 *
 * Editing is still possible in the backoffice; running the sync puts these
 * texts back, which is the point of it.
 */
class Guests2026Seeder extends Seeder
{
    public const GUESTS = [
        [
            'slug' => 'oleksandra-kovalchuk',
            'full_name' => 'Oleksandra Kovalchuk',
            'position' => 1,
            'avatar' => '/assets/2026/guests/kovalchuk.jpg',
            'institution_url' => 'https://www.mfcua.org/en/about',
            'en' => [
                'role' => 'Co-founder, Museum for Change',
                'institution' => 'Museum for Change, Ukraine',
                'description' => 'Oleksandra Kovalchuk is a Ukrainian cultural manager, heritage expert, and co-founder of Museum for Change. Since the beginning of Russia’s full-scale invasion, she has led and supported emergency heritage protection initiatives across Ukraine, working with museums and cultural institutions on evacuation, safeguarding collections, emergency stabilization, documentation, and institutional resilience. She also serves as Deputy Director of the Odesa National Fine Arts Museum and is the incoming Managing Director of the Ukraine Cultural Heritage Fund (UCHF). Her work focuses on practical approaches to protecting cultural heritage during war and strengthening institutions to operate under prolonged crisis.',
            ],
            'ro' => [
                'role' => 'Cofondatoare, Museum for Change',
                'institution' => 'Museum for Change, Ucraina',
                'description' => 'Oleksandra Kovalchuk este manager cultural și expert în patrimoniu din Ucraina și cofondatoare a Museum for Change. De la începutul invaziei pe scară largă a Rusiei, a coordonat și sprijinit inițiative de urgență pentru protejarea patrimoniului în întreaga Ucraină, colaborând cu muzee și instituții culturale în domenii precum evacuarea, protejarea colecțiilor, stabilizarea de urgență, documentarea și consolidarea rezilienței instituționale. Este, de asemenea, director adjunct al Muzeului Național de Arte Frumoase din Odesa și urmează să preia funcția de director executiv al Ukraine Cultural Heritage Fund (UCHF). Activitatea sa se concentrează pe dezvoltarea unor abordări practice pentru protejarea patrimoniului cultural în timpul războiului și pe consolidarea capacității instituțiilor de a funcționa în condițiile unei crize prelungite.',
            ],
        ],
        [
            'slug' => 'andras-mudra',
            'full_name' => 'András Mudra',
            'position' => 2,
            'avatar' => '/assets/2026/guests/mudra.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Architect, KÉK',
                'institution' => 'KÉK — Contemporary Architecture Centre, Budapest',
                'description' => 'András Mudra graduated in architecture from the Budapest University of Technology and Economics (BME) in 2024 and has since been working at DANU architecture studio. As an architect, he considers it essential to create livable, sustainable, and community-oriented spaces both within buildings and in their surrounding environments. He is particularly interested in working at the urban and public-building scale. He joined KÉK in 2022, initially as an intern, where he became a member of the Pecha Kucha Night Budapest and DANUrB+ teams. His main areas of interest include late modern architectural heritage, its public perception and preservation, as well as grassroots urban initiatives supported and shaped by local communities. In recent years, he has served as project lead for Budapest100, the KÉK Mentor Program, ModernTéka, and Pecha Kucha Night Budapest, while also being a regular participant in building camps. Since 2023, he has been part of the Urban Walks team as an organizer, and since 2025, as a guide. He is also the project manager of the Danube Ruralscapes project, which focuses on the regional-scale renewal of rural townscape practices.

KÉK — Contemporary Architecture Centre (Budapest) is an independent organisation founded in 2006, working in architecture, built heritage and participatory urban development. Known internationally for Budapest100, KÉK develops programmes that bring together professionals, communities and public administrations to imagine cities that are more inclusive, more sustainable and better connected to their heritage.',
            ],
            'ro' => [
                'role' => 'Arhitect, KÉK',
                'institution' => 'KÉK — Centrul de Arhitectură Contemporană, Budapesta',
                'description' => 'András Mudra a absolvit arhitectura la Universitatea de Tehnologie și Economie din Budapesta (BME) în 2024 și lucrează de atunci la studioul de arhitectură DANU. Ca arhitect, consideră esențială crearea unor spații locuibile, sustenabile și orientate spre comunitate, atât în interiorul clădirilor, cât și în jurul lor, cu un interes aparte pentru scara urbană și cea a clădirilor publice. S-a alăturat KÉK în 2022, inițial ca stagiar, devenind membru al echipelor Pecha Kucha Night Budapest și DANUrB+. Principalele sale teme de interes sunt patrimoniul arhitectural modern târziu, percepția publică și conservarea acestuia, precum și inițiativele urbane pornite de la firul ierbii, susținute și modelate de comunitățile locale. În ultimii ani a coordonat proiectele Budapest100, KÉK Mentor Program, ModernTéka și Pecha Kucha Night Budapest și a participat constant la tabere de construcție. Din 2023 face parte din echipa Urban Walks ca organizator, iar din 2025 și ca ghid. Este totodată managerul proiectului Danube Ruralscapes, dedicat reînnoirii la scară regională a practicilor de peisaj urban rural.

KÉK – Contemporary Architecture Centre (Budapesta) este o organizație independentă fondată în 2006, activă în domeniul arhitecturii, patrimoniului construit și dezvoltării urbane participative. Cunoscută la nivel internațional pentru proiectul Budapest100, KÉK dezvoltă programe care aduc împreună profesioniști, comunități și administrații pentru a imagina orașe mai incluzive, mai sustenabile și mai conectate la patrimoniul lor.',
            ],
        ],
        [
            'slug' => 'barbara-szij',
            'full_name' => 'Barbara Szij',
            'position' => 3,
            'avatar' => '/assets/2026/guests/szij.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Head of Research, Budapest100',
                'institution' => 'KÉK — Contemporary Architecture Centre, Budapest',
                'description' => 'Barbara Szij graduated in art history from Eötvös Loránd University (ELTE), specializing in architectural and urban history. She began volunteering as a researcher for Budapest100 in 2014, and since 2016 has served as the programme’s Head of Research. In connection with this role, she also contributed to projects such as URBACT Come In! — which supported the international adaptation of the Budapest100 model — as well as DANUrB and the Csepel Works: Open Factory Weekend initiative. Alongside these projects, she worked at the Budapest History Museum in the communications department and later as an exhibition project assistant, before joining KÉK full-time. In 2023 and 2024, she served as project manager of Budapest100, and from 2025 onward, returned to coordinating the programme’s volunteer researchers. Her main area of interest is the relationship between architecture networks and urban development, as well as finding ways to involve broader audiences in the preservation, presentation, and public discourse surrounding built heritage.

KÉK — Contemporary Architecture Centre (Budapest) is an independent organisation founded in 2006, working in architecture, built heritage and participatory urban development. Known internationally for Budapest100, KÉK develops programmes that bring together professionals, communities and public administrations to imagine cities that are more inclusive, more sustainable and better connected to their heritage.',
            ],
            'ro' => [
                'role' => 'Coordonatoare de cercetare, Budapest100',
                'institution' => 'KÉK — Centrul de Arhitectură Contemporană, Budapesta',
                'description' => 'Barbara Szij a absolvit istoria artei la Universitatea Eötvös Loránd (ELTE), cu specializare în istoria arhitecturii și a orașului. A început să lucreze ca cercetătoare voluntară pentru Budapest100 în 2014, iar din 2016 este coordonatoarea de cercetare a programului. În această calitate a contribuit și la proiecte precum URBACT Come In! — care a sprijinit adaptarea internațională a modelului Budapest100 —, DANUrB și inițiativa Csepel Works: Open Factory Weekend. În paralel, a lucrat la Muzeul de Istorie al Budapestei, în departamentul de comunicare și apoi ca asistentă de proiect expozițional, înainte de a se alătura KÉK cu normă întreagă. În 2023 și 2024 a fost managerul de proiect al Budapest100, iar din 2025 a revenit la coordonarea cercetătorilor voluntari ai programului. Principalul său domeniu de interes este relația dintre rețelele de arhitectură și dezvoltarea urbană, precum și modalitățile prin care publicul larg poate fi implicat în conservarea, prezentarea și discuția publică despre patrimoniul construit.

KÉK – Contemporary Architecture Centre (Budapesta) este o organizație independentă fondată în 2006, activă în domeniul arhitecturii, patrimoniului construit și dezvoltării urbane participative. Cunoscută la nivel internațional pentru proiectul Budapest100, KÉK dezvoltă programe care aduc împreună profesioniști, comunități și administrații pentru a imagina orașe mai incluzive, mai sustenabile și mai conectate la patrimoniul lor.',
            ],
        ],
        [
            'slug' => 'raluca-maria-trifa',
            'full_name' => 'Raluca-Maria Trifa',
            'position' => 4,
            'avatar' => '/assets/2026/guests/trifa.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Lecturer',
                'institution' => '“Ion Mincu” University of Architecture and Urbanism, Bucharest',
                'description' => 'Raluca-Maria Trifa is an architect, researcher, and lecturer at the “Ion Mincu” University of Architecture and Urbanism in Bucharest. Her research focuses on industrial architecture and the transformation of post-industrial cities, with a particular interest in the relationship between heritage, memory, and identity.

She has published studies and articles on these topics and is the author of Historic Industrial Architecture: Possibilities for Sustainable Recovery. The Case of Timișoara (ACS Publishing House, 2023), a volume dedicated to Timișoara’s industrial heritage and the possibilities for its recovery and adaptive reuse. In 2024–2025, she was a fellow at New Europe College, where she conducted a comparative analysis of the industrial and urban development of four peripheral cities of the Habsburg Empire: Timișoara, Pécs, Rijeka, and Brno. Her research examined both the similarities and distinctive features of these cities, as well as the processes of knowledge, technology, and architectural model transfer that accompanied their industrial development.',
            ],
            'ro' => [
                'role' => 'Lector universitar',
                'institution' => 'Universitatea de Arhitectură și Urbanism „Ion Mincu”, București',
                'description' => 'Raluca-Maria Trifa este arhitectă, cercetătoare și lector universitar la Universitatea de Arhitectură și Urbanism „Ion Mincu” din București. Activitatea sa de cercetare se concentrează asupra arhitecturii industriale și transformării orașelor postindustriale, cu un interes particular pentru relația dintre patrimoniu, memorie și identitate.

A publicat studii și articole pe aceste teme și este autoarea volumului Arhitectura industrială istorică: posibilități de recuperare sustenabilă. Cazul Timișoara (Editura ACS, 2023), dedicat patrimoniului industrial timișorean și posibilităților de recuperare și reutilizare a acestuia. În perioada 2024–2025 a fost bursieră New Europe College, unde a realizat o analiză comparativă a evoluției industriale și urbane a patru orașe periferice ale Imperiului Habsburgic: Timișoara, Pécs, Rijeka și Brno. Cercetarea a urmărit atât identificarea similitudinilor și particularităților dintre aceste orașe, cât și a proceselor de transfer de cunoștințe, tehnologii și modele arhitecturale care au însoțit dezvoltarea lor industrială.',
            ],
        ],
        [
            'slug' => 'gabriela-robeci',
            'full_name' => 'Gabriela Robeci',
            'position' => 5,
            'avatar' => '/assets/2026/guests/robeci.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Research Assistant',
                'institution' => 'Faculty of Arts and Design, West University of Timișoara',
                'description' => 'Gabriela Robeci is a research assistant with a background in the history and theory of visual arts. Since 2022, she has been teaching seminars and courses at the Faculty of Arts and Design, West University of Timișoara, Romania. Her research has been presented at local and international symposia and conferences, most recently at the AICA symposium Re-imagining the Global South in Johannesburg, South Africa.

Curating exhibitions, coordinating documentary video materials and publishing articles are among the ways in which she shares her academic work. Some of her research interests can be found in journals and publications available through the CEEOL platform, cicasp.uvt.ro, as well as in Parabol, Arta and Caietele de Arte și Design. Her research focuses on visual arts and built cultural heritage from the beginning of the 20th century to the present. She examines the contexts that drive cultural change through case studies of site-specific interventions, immersive spaces, temporary artworks, abstraction, and the translation of philosophical ideas into object-based forms.',
            ],
            'ro' => [
                'role' => 'Asistentă de cercetare',
                'institution' => 'Facultatea de Arte și Design, Universitatea de Vest din Timișoara',
                'description' => 'Gabriela Robeci, asistentă de cercetare, are o formare în istoria și teoria artelor vizuale și predă seminarii și cursuri la Facultatea de Arte și Design a Universității de Vest din Timișoara, România, începând din 2022. Cercetările sale au fost prezentate la simpozioane și conferințe locale și internaționale, cel mai recent fiind simpozionul AICA „Re-imagining the Global South”, desfășurat la Johannesburg, în Africa de Sud.

Curatorierea expozițiilor, coordonarea materialelor video documentare și publicarea de articole se numără printre modalitățile prin care își împărtășește activitatea academică. Unele dintre temele de interes pot fi regăsite în reviste și publicații, inclusiv cele disponibile pe platforma CEEOL, cicasp.uvt.ro și în revistele Parabol, Arta și Caietele de Arte și Design.

Interesul de cercetare se concentrează pe artele vizuale și patrimoniul cultural construit, de la începutul secolului XX până în prezent. Studiază contextele care determină schimbări culturale prin studii de caz asupra intervențiilor cu specific de sit, spațiilor imersive, lucrărilor de artă temporare, abstractizărilor și conceptualizărilor unor idei filosofice în forme obiectuale.',
            ],
        ],
        [
            'slug' => 'nicoleta-musat',
            'full_name' => 'Nicoleta Mușat',
            'position' => 6,
            'avatar' => '/assets/2026/guests/musat.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Educator & field researcher',
                'institution' => 'West University of Timișoara',
                'description' => 'Nicoleta is an educator and field researcher. Trained as a philologist at the Faculty of Letters in Timișoara, she discovered the history of mentalities, ethnological fieldwork and life-story interviews while pursuing her master’s studies more than two decades ago. Her research has focused primarily on the Banat region, where she has taken part in multidisciplinary projects exploring funeral rituals, different forms of heritage and the processes through which heritage is created and negotiated within communities. She has also studied human migration and the stories that accompany it. She is fascinated by life stories, family archives and the objects people collect and pass down from one generation to another. More recently, her attention has turned to the ways Romanian is taught through textbooks, how it is transmitted within families as a heritage language, and how language contributes to shaping identity in Romanian communities abroad.',
            ],
            'ro' => [
                'role' => 'Profesoară și om de teren',
                'institution' => 'Universitatea de Vest din Timișoara',
                'description' => 'Nicoleta este profesoară și om de teren; s-a format, ca filolog, la Literele timișorene, unde, urmând cursuri masterale, în urmă cu peste două decenii, a descoperit istoria mentalităților, terenul etnologic și interviurile de tip povestea vieții. A făcut cercetări, în special, în Banat, în proiecte multidisciplinare în care s-a interesat de ritualizarea înmormântării, de diferitele tipuri de patrimoniu și de manierele de patrimonializare care se produc în comunități. Totodată, s-a preocupat de migrațiile oamenilor și de poveștile care le însoțesc. Este fascinată de poveștile de viață, de arhivele de familie, de obiectele pe care le strâng oamenii și pe care le lasă moștenire. În ultimul timp, și-a îndreptat atenția spre manierele în care se predă limba română prin manuale, spre cum se transmite ea în cadrul familial ca limbă de patrimoniu și cum vine să creeze identitate în comunități din afara țării.',
            ],
        ],
        [
            'slug' => 'iulia-iordan',
            'full_name' => 'Iulia Iordan',
            'position' => 7,
            'avatar' => '/assets/2026/guests/iordan.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Writer, museum educator and curator',
                'institution' => 'Da’DeCe Association',
                'description' => 'Iulia Iordan is a writer, museum educator and curator. She studied philosophy, pedagogy and the history of images, and currently explores different ways of enriching cultural discourse by bringing in the voices of children and young people. She has worked as a museum educator for more than twenty years, first at the National Museum of Art of Romania and later at the Da’DeCe Association, where she develops cultural projects aimed at bringing people closer to heritage, philosophy, literature and contemporary art. Her everyday practice involves countless encounters with people of different ages and professional backgrounds, with whom she creates interactive exhibitions, poetry, mediation workshops, cultural trails and other forms of expression still in the process of being invented. She has been publishing stories since 2012, two of which have received awards in Romanian children’s book competitions and have been featured at international exhibitions and book fairs. She is a co-founder of De Basm, the Romanian Association of Writers for Children and Young Adults, where she is particularly involved in cultural intervention projects in areas with limited access to cultural resources, advocating for every child’s right to contemporary literature. How we can engage with the meanings of culture in ways that make it relevant to each of us is one of the central questions of her professional practice, both in the cultural and publishing projects she develops and in her doctoral research at the Doctoral School of the Centre of Excellence in Image Studies.

Since the end of 2025, she has also been part of the Cultural Projects team of the Romanian Order of Architects, where she coordinates, within a multidisciplinary team, the development of an anniversary publication marking 25 years of the OAR, as well as the project Policy for Architecture.',
            ],
            'ro' => [
                'role' => 'Scriitoare, educator muzeal și curatoare',
                'institution' => 'Asociația Da’DeCe',
                'description' => 'Iulia Iordan este scriitoare, educator muzeal și curatoare. A studiat filosofia, pedagogia și istoria imaginii, iar în prezent experimentează diverse modalități de a îmbogăți discursul cultural cu vocile copiilor și tinerilor. Lucrează de peste douăzeci de ani ca educator muzeal, mai întâi la Muzeul Național de Artă al României, apoi la Asociația Da’DeCe, unde inventează proiecte culturale care au ca scop apropierea oamenilor de patrimoniu, filosofie, literatură sau artă contemporană. Viața sa cotidiană este alcătuită din nenumărate moduri de a se întâlni cu oameni de vârste și profesii diferite, alături de care concepe expoziții interactive, poezie, ateliere de mediere, trasee culturale sau alte forme de expresie în curs de inventare. Publică povești din anul 2012, două dintre acestea fiind premiate la concursurile de carte românească pentru copii și participante la expoziții și târguri internaționale. Este cofondatoare a De Basm. Asociația Scriitorilor pentru Copii și Adolescenți din România, unde se implică în special în proiecte de intervenție culturală în zone defavorizate din punct de vedere cultural, militând pentru dreptul tuturor copiilor la literatură contemporană. Cum să ne apropiem de semnificațiile culturii, în așa fel încât aceasta să capete un sens pentru fiecare dintre noi, este una dintre provocările sale profesionale în proiectele culturale și editoriale pe care le derulează, dar și tema sa de cercetare la Școala Doctorală – Centrul de Excelență în Studiul Imaginii.

Nu în ultimul rând, este parte a echipei de Proiecte culturale a Ordinului Arhitecților din România de la finalul anului 2025, unde coordonează realizarea unui volum aniversar cu ocazia a 25 de ani de OAR, în cadrul unei echipe multidisciplinare, și proiectul „Politica pentru arhitectură”.',
            ],
        ],
        [
            'slug' => 'andela-petrovic',
            'full_name' => 'Anđela Petrović',
            'position' => 8,
            'avatar' => '/assets/2026/guests/petrovic.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Curator & researcher in museology and heritage studies',
                'institution' => 'Youth.Heritage.Europe.',
                'description' => 'Anđela Petrović is a PhD candidate in Art History, specializing in Museology and Heritology at the Faculty of Philosophy, University of Belgrade, where she is also a Teaching Associate. She is currently working as a curator at the Gallery of the Serbian Academy of Sciences and Arts. Previously, she worked at the National Museum of Serbia in the Department for Public Engagement and Education, with a focus on accessibility and inclusive museum practices. She is a member of ICOM and the Balkan Museum Network.

Her academic and professional work focuses on sensory museology, museum communication, and heritage interpretation, with particular interest in multisensory, non-visual, and affective approaches to audience engagement and museum accessibility. She has participated in several national and international projects and conferences. Since 2025, she has been a recipient of the doctoral scholarship awarded by the Ministry of Science, Technological Development, and Innovation of the Republic of Serbia.

Anđela Petrović joins Why Culture Matters 2026 as a representative of Youth.Heritage.Europe., a partner of this year’s symposium.',
            ],
            'ro' => [
                'role' => 'Curatoare și cercetătoare în muzeologie și patrimoniu',
                'institution' => 'Youth.Heritage.Europe.',
                'description' => 'Anđela Petrović este doctorandă în Istoria Artei, cu specializare în Muzeologie și Heritologie, la Facultatea de Filosofie a Universității din Belgrad, unde este și cadru didactic asociat. În prezent, lucrează în calitate de curatoare la Galeria Academiei Sârbe de Științe și Arte. Anterior, a activat în cadrul Muzeului Național al Serbiei, în Departamentul de Relații cu Publicul și Educație, concentrându-se asupra accesibilității și practicilor muzeale incluzive. Este membră ICOM și Balkan Museum Network.

Activitatea sa academică și profesională se concentrează asupra muzeologiei senzoriale, comunicării muzeale și interpretării patrimoniului, cu un interes special pentru abordările multisenzoriale, non-vizuale și afective ale relației cu publicul și pentru accesibilitatea muzeelor. A participat la numeroase proiecte și conferințe naționale și internaționale. Din 2025, beneficiază de o bursă doctorală acordată de Ministerul Științei, Dezvoltării Tehnologice și Inovării din Republica Serbia.

Anđela Petrović se alătură Why Culture Matters 2026 ca reprezentantă a Youth.Heritage.Europe., partener al acestei ediții a simpozionului.',
            ],
        ],
        [
            'slug' => 'andreea-lazea',
            'full_name' => 'Andreea Lazea',
            'position' => 9,
            'avatar' => '/assets/2026/guests/lazea.jpg',
            'institution_url' => null,
            'en' => [
                'role' => 'Associate professor & director of CICASP',
                'institution' => 'Faculty of Arts and Design, West University of Timișoara',
                'description' => 'Andreea Lazea is an Associate Professor at the Faculty of Arts and Design, West University of Timișoara, where she teaches courses in cultural heritage, aesthetics and art history. She is also Director of the Centre for Research in Curatorship, Art History and Criticism, and Heritage Studies (CICASP).

Her recent research focuses on contemporary public art in Romania, as well as on cultural heritage and its capacity to foster creativity and innovation. Her publications include Arta publică în Timișoara după 1989 [Public Art in Timișoara after 1989] (UVT Publishing House, 2025) and Pentru o antropologie a patrimoniului și patrimonializării. Perspective asupra monumentelor istorice în România [Towards an Anthropology of Heritage and Heritagisation. Perspectives on Historic Monuments in Romania] (Lumen Publishing House, 2012).

Alongside her teaching and research, Andreea Lazea is active as a curator and develops and implements projects for broader audiences, including Patrimoniul de aproape [Heritage Up Close] (Timișoara, 2023).',
            ],
            'ro' => [
                'role' => 'Conferențiară universitară și directoare CICASP',
                'institution' => 'Facultatea de Arte și Design, Universitatea de Vest din Timișoara',
                'description' => 'Andreea Lazea este conferențiar universitar la Facultatea de Arte și Design, Universitatea de Vest din Timișoara, unde predă cursuri în domeniul patrimoniului cultural, al esteticii și al istoriei artei, și director al Centrului de Cercetări în Curatoriat, Istoria și Critica de Artă, Studii Patrimoniale (CICASP). Cercetările sale recente vizează arta publică contemporană din România, precum și patrimoniul cultural și capacitatea acestuia de a stimula creativitatea și inovația. Printre publicațiile sale se numără volumele „Arta publică în Timișoara după 1989” (Ed. UVT, 2025) și „Pentru o antropologie a patrimoniului și patrimonializării. Perspective asupra monumentelor istorice în România” (Ed. Lumen, 2012).

Alături de activitatea didactică și de cercetare, Andreea Lazea desfășoară și o activitate curatorială și elaborează și implementează proiecte destinate publicului larg, așa cum este cazul proiectului „Patrimoniul de aproape” (Timișoara, 2023).',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::GUESTS as $guest) {
            $person = Person::on('wcm_2026')->firstOrNew(['slug' => $guest['slug']]);

            $person->setConnection('wcm_2026')->fill([
                'full_name' => $guest['full_name'],
                'position' => $guest['position'],
                'avatar' => $guest['avatar'],
                'institution_url' => $guest['institution_url'],
            ])->save();

            foreach (['en', 'ro'] as $locale) {
                $translation = $person->translateOrNew($locale);
                $translation->role = $guest[$locale]['role'];
                $translation->institution = $guest[$locale]['institution'];
                $translation->description = $guest[$locale]['description'];
            }

            $person->save();
        }
    }
}
