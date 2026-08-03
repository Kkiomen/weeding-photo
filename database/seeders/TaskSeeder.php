<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Reset: usuń zadania, do których nie przypisano jeszcze zdjęć.
        DB::table('tasks')
            ->whereNotIn('id', DB::table('photos')->whereNotNull('task_id')->select('task_id'))
            ->delete();

        $tasks = [
            // KLASYKI ODWRÓCONE
            ['icon' => '🤳', 'title' => 'Selfie z Parą Młodą… ale z podwójnym podbródkiem', 'description' => 'Klasyk odwrócony – wszyscy troje muszą zrobić najgorszą minę na jaką was stać.', 'xp_reward' => 40],
            ['icon' => '👯', 'title' => 'Sobowtór gościa', 'description' => 'Znajdź osobę ubraną najbardziej podobnie do siebie i zrób selfie „bliźniaki weselne".', 'xp_reward' => 35],
            ['icon' => '🕺', 'title' => 'Najgorszy taniec wieczoru', 'description' => 'Uchwyć kogoś w trakcie ruchu, który przejdzie do historii tego wesela. Blur mile widziany.', 'xp_reward' => 30],
            ['icon' => '👞', 'title' => 'Zdjęcie tylko butów', 'description' => 'Cała reszta ucięta – tylko obuwie gości pod stołem. Im dziwniej, tym lepiej.', 'xp_reward' => 25],
            ['icon' => '🤳', 'title' => 'Nieświadome selfie', 'description' => 'Zrób selfie, na którym w tle jest ktoś, kto właśnie robi coś kompromitującego. Nie musi wiedzieć.', 'xp_reward' => 45],
            ['icon' => '💋', 'title' => 'Ślad szminki na policzku', 'description' => 'Ktoś dostał całusa i zostawił ślad. Uwiecznij dowód zbrodni.', 'xp_reward' => 30],
            ['icon' => '🍾', 'title' => 'Kieliszek w powietrzu', 'description' => 'Toast złapany dokładnie w momencie zderzenia kieliszków. Bonus jeśli coś się rozlało.', 'xp_reward' => 35],
            ['icon' => '🧦', 'title' => 'Skarpetki Pana Młodego', 'description' => 'Podkradnij się i uwiecznij wzór skarpetek. Musi być zaskakujący.', 'xp_reward' => 25],
            ['icon' => '💃', 'title' => 'Grupa 5+ na parkiecie w jednej pozie', 'description' => 'Namów pięć osób do jednej absurdalnej choreografii i zrób zdjęcie.', 'xp_reward' => 40],
            ['icon' => '🎤', 'title' => 'Ktoś śpiewa jakby był na Eurowizji', 'description' => 'Karaoke, przyśpiewki, cokolwiek – emocje muszą być NA MAXA.', 'xp_reward' => 30],
            ['icon' => '🍰', 'title' => 'Tort w twarzy (albo prawie)', 'description' => 'Klasyk – ktoś je tort z bliska, po pysku, w locie. Wybór należy do Ciebie.', 'xp_reward' => 40],
            ['icon' => '👴', 'title' => 'Najstarszy gość szaleje na parkiecie', 'description' => 'Uchwyć dziadka/babcię w momencie, w którym pokazują młodym jak się to robi.', 'xp_reward' => 45],
            ['icon' => '👶', 'title' => 'Najmłodszy gość imituje dorosłego', 'description' => 'Kieliszek soczku, mina jak koneser, patrzy w dal. Portret weselnego dziecka.', 'xp_reward' => 40],
            ['icon' => '😴', 'title' => 'Ktoś zasnął przy stole', 'description' => 'Godzina 2:00, ktoś nie dał rady. Uwiecznij ofiarę zabawy. (delikatnie!)', 'xp_reward' => 50],
            ['icon' => '🤝', 'title' => 'Dwie osoby, które się dopiero poznały', 'description' => 'Znajdź parę gości, którzy nie znali się przed weselem i już są kumplami. Uścisk dłoni lub przytulasek.', 'xp_reward' => 25],
            ['icon' => '🍸', 'title' => 'Barmański popis', 'description' => 'Zdjęcie barmana w akcji – lanie, kręcenie, cokolwiek zabawnego.', 'xp_reward' => 20],
            ['icon' => '👗', 'title' => 'Kreacja stylizowana na królową balu', 'description' => 'Portret gościa, który dziś rządzi salą. Musi mieć pozę.', 'xp_reward' => 30],
            ['icon' => '🎭', 'title' => 'Emocje ekstremalne', 'description' => 'Uchwyć czyjąś minę w momencie największego zaskoczenia, śmiechu albo wzruszenia. Zoom!', 'xp_reward' => 35],
            ['icon' => '🚬', 'title' => 'Konspiracja na palarni', 'description' => 'Grupka gości na papierosku knuje. O czym rozmawiają? Zrób kadr jakby był z filmu mafijnego.', 'xp_reward' => 30],
            ['icon' => '🤡', 'title' => 'Muszka/krawat użyty niezgodnie z przeznaczeniem', 'description' => 'Krawat na czole? Muszka na uchu? Ktoś w gorsecie z serwetki? Tego szukamy.', 'xp_reward' => 35],
            ['icon' => '🕶️', 'title' => 'Ktoś w okularach po 22:00', 'description' => 'Wesele = klimat DJ-ski. Znajdź gościa w ciemnych okularach w środku nocy.', 'xp_reward' => 25],
            ['icon' => '🎯', 'title' => 'Perfect timing', 'description' => 'Zdjęcie, na którym coś wygląda jakby wyszło przypadkiem, ale idealnie. Latający kieliszek + reakcja tła itp.', 'xp_reward' => 60],
            ['icon' => '📸', 'title' => 'Fotograf sfotografowany', 'description' => 'Zrób zdjęcie oficjalnemu fotografowi z wesela. Bonus jeśli robi śmieszną minę.', 'xp_reward' => 30],
            ['icon' => '🔥', 'title' => 'Zimne ognie – ale kadr sceniczny', 'description' => 'Nie samo sparklery, tylko ktoś pozujący z nimi jak na okładce albumu rockowego.', 'xp_reward' => 40],
            ['icon' => '🍔', 'title' => 'Bufet o 3:00 nad ranem', 'description' => 'Ktoś je bigos rękami / trzyma trzy tortille naraz / poluje na ostatnią śledzia. Zrób paparazzi.', 'xp_reward' => 35],

            // ABSURD I PROVOKACJE
            ['icon' => '🦵', 'title' => 'Podwiązka w akcji', 'description' => 'Moment zdejmowania/rzucania podwiązki – uchwyć minę osoby, która ją złapała.', 'xp_reward' => 40],
            ['icon' => '💐', 'title' => 'Walka o bukiet', 'description' => 'Rzucanie bukietu = akcja jak w rugby. Zrób kadr w środku bitwy.', 'xp_reward' => 45],
            ['icon' => '🍽️', 'title' => 'Puste talerze bohaterów', 'description' => 'Zdjęcie stołu, na którym ktoś zmiótł WSZYSTKO. Dowód, że apetyt dopisał.', 'xp_reward' => 20],
            ['icon' => '🫗', 'title' => 'Rozlany drink', 'description' => 'Uwiecznij katastrofę – w momencie rozlania albo tuż po. Bez oceniania!', 'xp_reward' => 30],
            ['icon' => '🎈', 'title' => 'Dekoracja użyta niezgodnie z przeznaczeniem', 'description' => 'Balon na głowie, kwiaty za uchem, serwetka jako peleryna. Znajdź improwizację.', 'xp_reward' => 35],
            ['icon' => '🕴️', 'title' => 'Solowy taniec z powietrzem', 'description' => 'Ktoś tańczy sam ze sobą, ale wygląda jakby był w transie. Portret weselnego mistyka.', 'xp_reward' => 35],
            ['icon' => '🤸', 'title' => 'Nietypowa poza pod ścianą', 'description' => 'Ktoś siedzi/leży/klęczy w miejscu, gdzie nikt nie powinien. Uwiecznij bez pytania.', 'xp_reward' => 30],
            ['icon' => '👑', 'title' => 'Improwizowana korona', 'description' => 'Ktoś z serwetką/talerzem/kwiatem na głowie jak król/królowa parkietu.', 'xp_reward' => 30],
            ['icon' => '📞', 'title' => 'Telefon o 2:00', 'description' => 'Ktoś dzwoni do kogoś w środku nocy, z pełnym zaangażowaniem. O co chodziło – nie wiadomo.', 'xp_reward' => 30],
            ['icon' => '🎢', 'title' => 'Karuzela z gości', 'description' => 'Ktoś podniesiony w powietrze, na krzesełku, na plecach – akcja grupowa.', 'xp_reward' => 45],

            // DUETY I GRUPY
            ['icon' => '👨‍👦', 'title' => 'Ojciec i syn / matka i córka w identycznej pozie', 'description' => 'Genetyka nie kłamie. Znajdź pokrewieństwo i uwiecznij.', 'xp_reward' => 35],
            ['icon' => '🫂', 'title' => 'Wzruszający uścisk', 'description' => 'Ktoś się rozczulił, ktoś go przytula. Ciepły kadr wśród szaleństwa.', 'xp_reward' => 30],
            ['icon' => '👵', 'title' => 'Babcia z drinkiem', 'description' => 'Babcia/ciocia z kolorowym drinkiem w ręku, mrugająca do obiektywu.', 'xp_reward' => 40],
            ['icon' => '🤳', 'title' => 'Selfie 8+ osób', 'description' => 'Wciśnijcie się w jeden kadr. Im więcej głów, tym lepiej. Ktoś musi być ucięty.', 'xp_reward' => 40],
            ['icon' => '🐕', 'title' => 'Zwierzę na weselu', 'description' => 'Pies, kot, cokolwiek co nie powinno tam być. Jeśli nie ma – twórcze podejście: pluszak, obrus w łaty.', 'xp_reward' => 50],

            // MISTRZOSTWO KADRU
            ['icon' => '🌙', 'title' => 'Widok z zewnątrz', 'description' => 'Wyjdź z sali, zrób zdjęcie okien Zamku Topacz od zewnątrz – jak z filmu.', 'xp_reward' => 35],
            ['icon' => '🏰', 'title' => 'Zamek w tle', 'description' => 'Zdjęcie kogoś/czegoś, ale zamek MUSI być wyraźnie w kadrze.', 'xp_reward' => 25],
            ['icon' => '💡', 'title' => 'Zdjęcie tylko światłem', 'description' => 'Odbicie w kieliszku, żyrandolu, lampce. Bez ludzi, tylko światło i klimat.', 'xp_reward' => 30],
            ['icon' => '🪞', 'title' => 'Selfie w lustrze', 'description' => 'Klasyk 2010, ale dziś. W łazience, w oknie, cokolwiek co odbija.', 'xp_reward' => 25],
            ['icon' => '🎨', 'title' => 'Zdjęcie makro', 'description' => 'Zbliżenie na szczegół – oczy, obrączkę, kropelkę szampana. Bardzo z bliska.', 'xp_reward' => 30],
            ['icon' => '📚', 'title' => 'Kadr jak z klasycznego malarstwa', 'description' => 'Grupa gości ustawiona jak na obrazie renesansowym. Powaga, kompozycja, dostojność.', 'xp_reward' => 50],

            // MISJE EKSTREMALNE (dużo XP)
            ['icon' => '🎬', 'title' => 'Reenactment sceny z filmu', 'description' => 'Odtwórz z gośćmi scenę z filmu (Titanic, Matrix, Ojciec Chrzestny…). Musi być rozpoznawalna.', 'xp_reward' => 70],
            ['icon' => '🏆', 'title' => 'Trofeum wesela', 'description' => 'Znajdź obiekt, który powinien być muzealną pamiątką tego wesela (np. czyjś zgubiony but).', 'xp_reward' => 45],
            ['icon' => '🎁', 'title' => 'Prezent otwierany przez Parę Młodą', 'description' => 'Uchwyć minę Młodych w momencie otwierania czegoś zaskakującego.', 'xp_reward' => 40],
            ['icon' => '🧙', 'title' => 'Ktoś w zaskakującej charakteryzacji', 'description' => 'Malunek na twarzy, przyklejony wąs, doklejone brwi. Improwizacja weselna.', 'xp_reward' => 40],
            ['icon' => '🎪', 'title' => 'Cały stolik w jednej absurdalnej akcji', 'description' => 'Cały stolik jednocześnie robi jedną głupią rzecz. Koordynacja jak w reklamie.', 'xp_reward' => 55],
            ['icon' => '🚗', 'title' => 'Ktoś śpi w samochodzie', 'description' => 'Wesele wygrywa – ktoś nie doszedł do domu. Ostrożnie, z szacunkiem, ale uwiecznij.', 'xp_reward' => 55],

            // POJEDYNKI I WYZWANIA
            ['icon' => '🥊', 'title' => 'Walka na kciuki', 'description' => 'Namów dwie osoby na turniej kciuków. Uchwyć moment zwycięstwa.', 'xp_reward' => 30],
            ['icon' => '🧊', 'title' => 'Konkurs na najgłupszą minę', 'description' => 'Trzy osoby, jedno zdjęcie, każdy robi najgorszą minę. Sędziuje internet.', 'xp_reward' => 35],
            ['icon' => '🍋', 'title' => 'Ktoś próbuje cytryny (albo shota)', 'description' => 'Uchwyć reakcję – ta chwila skrzywienia twarzy jest bezcenna.', 'xp_reward' => 40],
            ['icon' => '🤾', 'title' => 'Rzut podwiązką/bukietem – z powietrza', 'description' => 'Obiekt w locie, złapany w powietrzu. Bez blura!', 'xp_reward' => 50],
            ['icon' => '🎳', 'title' => 'Pojedynek na przyśpiewki', 'description' => 'Dwie grupy śpiewają na wyścigi. Uchwyć momentu maksymalnego zaangażowania.', 'xp_reward' => 35],

            // GRUPKI I KLIKI
            ['icon' => '👫', 'title' => 'Świadkowie w akcji', 'description' => 'Świadkowa i świadek w środku akcji – toast, taniec, przemowa. Nie mogą się nudzić.', 'xp_reward' => 40],
            ['icon' => '🎓', 'title' => 'Znajomi ze studiów', 'description' => 'Zbierz ekipę znajomych Młodych ze studiów. Muszą się razem uśmiechać.', 'xp_reward' => 30],
            ['icon' => '💼', 'title' => 'Koledzy z pracy w krzywym zwierciadle', 'description' => 'Koledzy z pracy robią coś, czego szef nigdy by nie zobaczył. Krawaty na czołach mile widziane.', 'xp_reward' => 45],
            ['icon' => '🧓', 'title' => 'Trzy pokolenia w jednym kadrze', 'description' => 'Dziadkowie, rodzice, dzieci – wszyscy razem, wszyscy się śmieją.', 'xp_reward' => 45],
            ['icon' => '👨‍❤️‍👨', 'title' => 'Najstarsza para na weselu', 'description' => 'Znajdź parę z najdłuższym stażem. Portret miłości, która przetrwała.', 'xp_reward' => 40],

            // WNĘTRZE I DETALE
            ['icon' => '🪑', 'title' => 'Puste krzesło o dziwnej porze', 'description' => 'Nikt nie siedzi tam, gdzie powinien. Uwiecznij pustkę w środku imprezy.', 'xp_reward' => 25],
            ['icon' => '🥄', 'title' => 'Ktoś je łyżką coś, co powinno być jedzone widelcem', 'description' => 'Improwizacja gastronomiczna. Bezczelność weselna.', 'xp_reward' => 30],
            ['icon' => '🧻', 'title' => 'Kreatywne użycie serwetki', 'description' => 'Serwetka jako czapka, śliniak, obrus na głowie, cokolwiek. Musi być pomysłowe.', 'xp_reward' => 30],
            ['icon' => '🕰️', 'title' => 'Zegarek/telefon o dziwnej godzinie', 'description' => 'Zbliżenie na tarczę zegarka lub ekran telefonu pokazujący absurdalną porę.', 'xp_reward' => 20],
            ['icon' => '🧴', 'title' => 'Ktoś poprawia makijaż/fryzurę', 'description' => 'Klasyczny moment przy lustrze/w toalecie. Uchwyć proces regeneracji.', 'xp_reward' => 25],

            // NA PARKIECIE
            ['icon' => '💫', 'title' => 'Ktoś w idealnym obrocie', 'description' => 'Suknia w rozwianiu, marynarka w powietrzu. Moment jak z teledysku.', 'xp_reward' => 45],
            ['icon' => '🕴️', 'title' => 'Pojedynek Michael Jacksona', 'description' => 'Ktoś próbuje moonwalka albo pozy MJ. Wybór trudny, potencjał ogromny.', 'xp_reward' => 40],
            ['icon' => '💦', 'title' => 'Spocone czoło', 'description' => 'Portret gościa, który daje z siebie WSZYSTKO na parkiecie. Krople gwarantują XP.', 'xp_reward' => 30],
            ['icon' => '🤹', 'title' => 'Żonglerka czymkolwiek', 'description' => 'Trzy jabłka? Kieliszki? Pomarańcze? Ktoś musi coś rzucać w powietrze.', 'xp_reward' => 45],
            ['icon' => '🏋️', 'title' => 'Ktoś podnosi kogoś', 'description' => 'Klasyk – Panna Młoda na rękach Pana Młodego, ale każda inna kombinacja też się liczy.', 'xp_reward' => 40],

            // MISJE OBSERWACYJNE
            ['icon' => '🔍', 'title' => 'Znajdź osobę z dwoma drinkami', 'description' => 'Chcieć więcej to normalne. Uwiecznij optymistę bufetu.', 'xp_reward' => 25],
            ['icon' => '🕵️', 'title' => 'Ktoś podkrada jedzenie ze stołu obok', 'description' => 'Weselna dywersja – bądź świadkiem. Bez zdrady tożsamości.', 'xp_reward' => 40],
            ['icon' => '📱', 'title' => 'Trzy telefony w jednym kadrze', 'description' => 'Wszyscy patrzą w ekrany. Nikt nie patrzy na siebie. Portret czasów.', 'xp_reward' => 30],
            ['icon' => '👀', 'title' => 'Ktoś patrzy jakby coś kombinował', 'description' => 'Ta specyficzna mina, gdy plan się rodzi. Uchwyć spisek w powstawaniu.', 'xp_reward' => 35],
            ['icon' => '💌', 'title' => 'Karta z życzeniami z czymś dziwnym', 'description' => 'Zdjęcie najbardziej niesamowitej karty/wpisu w księdze pamiątkowej.', 'xp_reward' => 30],

            // WESELE Z TWISTEM
            ['icon' => '🎂', 'title' => 'Krojenie tortu – ale ktoś nie umie', 'description' => 'Uchwyć moment, gdy nóż idzie źle. Musi być śmiesznie.', 'xp_reward' => 40],
            ['icon' => '🌹', 'title' => 'Kwiat w niecodziennym miejscu', 'description' => 'Za uchem, w kieliszku, na czole. Znajdź kwiat, który nie powinien tam być.', 'xp_reward' => 30],
            ['icon' => '🎀', 'title' => 'Suknia w akcji', 'description' => 'Panna Młoda robi coś nieelegancko w sukni (biega, wchodzi na krzesło, tańczy hardcore). GOLD.', 'xp_reward' => 55],
            ['icon' => '👔', 'title' => 'Pan Młody rozluźnia krawat', 'description' => 'Ten moment, kiedy formalności się kończą i zaczyna prawdziwe wesele.', 'xp_reward' => 35],
            ['icon' => '🍫', 'title' => 'Fontanna czekolady – katastrofa', 'description' => 'Ktoś maczał zbyt agresywnie / rozlał / zjadł wszystko. Uwiecznij chaos.', 'xp_reward' => 40],

            // NA GRANICY MOŻLIWOŚCI
            ['icon' => '🌅', 'title' => 'Wschód słońca', 'description' => 'Dotrwałeś do świtu. Zrób zdjęcie nieba i tego, co jeszcze zostało z imprezy.', 'xp_reward' => 80],
            ['icon' => '👣', 'title' => 'Bosa Panna Młoda', 'description' => 'Szpilki poddały się przed nią. Uchwyć wolność bosych stóp.', 'xp_reward' => 40],
            ['icon' => '🎩', 'title' => 'Ktoś w kapeluszu, który nie jest jego', 'description' => 'Cudzy kapelusz, czapka, wieniec – improwizacja jest kluczem.', 'xp_reward' => 30],
            ['icon' => '💤', 'title' => 'Śpiący na kanapie', 'description' => 'Kanapa w hotelu, siedzenie w holu, ktokolwiek gdziekolwiek. (Z szacunkiem!)', 'xp_reward' => 45],
            ['icon' => '🎉', 'title' => 'Ostatni taniec', 'description' => 'Uchwyć finał – ostatnie osoby na parkiecie, ostatnia piosenka wesela.', 'xp_reward' => 60],
        ];

        foreach ($tasks as $i => $task) {
            Task::updateOrCreate(
                ['title' => $task['title']],
                array_merge($task, ['sort_order' => $i])
            );
        }
    }
}
