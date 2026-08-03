<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            // Częste (weight 15-20)
            ['icon' => '🎁', 'title' => '+10 XP', 'description' => 'Trafił się drobiazg. Doceniamy!', 'xp_bonus' => 10, 'weight' => 20],
            ['icon' => '⭐', 'title' => '+25 XP', 'description' => 'Nieźle! Punkty do rankingu.', 'xp_bonus' => 25, 'weight' => 18],
            ['icon' => '🍀', 'title' => '+50 XP', 'description' => 'Klasa! Twój ranking rośnie w oczach.', 'xp_bonus' => 50, 'weight' => 12],

            // Średnie (weight 8-12) - wyzwania
            ['icon' => '💃', 'title' => 'Zatańcz z pierwszą osobą, którą zobaczysz', 'description' => 'Musisz. Bez wykręcania.', 'xp_bonus' => 30, 'weight' => 10],
            ['icon' => '🥃', 'title' => 'Toast z Panem Młodym', 'description' => 'Znajdź go i wypijcie razem coś. On się nie odmówi.', 'xp_bonus' => 40, 'weight' => 8],
            ['icon' => '🎤', 'title' => 'Zaśpiewaj karaoke', 'description' => 'Głośno, publicznie. Fałsz mile widziany.', 'xp_bonus' => 50, 'weight' => 6],
            ['icon' => '👯', 'title' => 'Zrób głupie zdjęcie ze świadkiem/świadkową', 'description' => 'Musi być śmiesznie. Nagraj też ich reakcję.', 'xp_bonus' => 35, 'weight' => 10],
            ['icon' => '🕺', 'title' => 'Rzuć wyzwanie na parkiet', 'description' => 'Zaproś kogoś do pojedynku tanecznego. Dowód: zdjęcie.', 'xp_bonus' => 40, 'weight' => 8],

            // Rzadkie (weight 3-5)
            ['icon' => '💯', 'title' => 'JACKPOT: +100 XP!', 'description' => 'Trafiłeś w dziesiątkę!', 'xp_bonus' => 100, 'weight' => 4],
            ['icon' => '👑', 'title' => 'Zostajesz Królem Wesela na 30 minut', 'description' => 'Powiedz każdemu, kogo spotkasz. To Twoje 30 minut chwały.', 'xp_bonus' => 60, 'weight' => 3],
            ['icon' => '🎬', 'title' => 'Zorganizuj mini-scenę filmową', 'description' => 'Musisz zaangażować minimum 3 osoby. Uwiecznij zdjęciem.', 'xp_bonus' => 75, 'weight' => 3],

            // Śmieszne / bez XP
            ['icon' => '🎭', 'title' => 'Pusto…', 'description' => 'Zdrapka pusta. Życie takie jest. Spróbuj za 30 minut.', 'xp_bonus' => 0, 'weight' => 15],
            ['icon' => '🤷', 'title' => 'Prawie wygrałeś', 'description' => 'Prawie. Ale nie. Jeszcze zdrapkę mamy dla Ciebie za pół godziny.', 'xp_bonus' => 0, 'weight' => 12],
            ['icon' => '🍕', 'title' => 'Idź zjeść coś z bufetu', 'description' => 'To nie prezent, to porada. Ale weź +5 XP za posłuszeństwo.', 'xp_bonus' => 5, 'weight' => 10],
            ['icon' => '😴', 'title' => 'Przypomnij komuś, że jutro może boleć', 'description' => 'Rozejrzyj się. Ktoś potrzebuje tej informacji.', 'xp_bonus' => 15, 'weight' => 10],
            ['icon' => '💧', 'title' => 'Wypij szklankę wody', 'description' => 'Serio. Ciało dziękuje. +20 XP za dbanie o siebie.', 'xp_bonus' => 20, 'weight' => 12],

            // Interakcje społeczne
            ['icon' => '🤝', 'title' => 'Poznaj kogoś nowego', 'description' => 'Znajdź gościa, którego nie znasz. Pogadaj minimum 5 minut. Zaufamy Ci.', 'xp_bonus' => 30, 'weight' => 10],
            ['icon' => '📸', 'title' => 'Zrób grupowe selfie ze stolikiem obok', 'description' => 'Nie swoim. Musi być cała ekipa z sąsiedniego stolika.', 'xp_bonus' => 45, 'weight' => 6],
            ['icon' => '💐', 'title' => 'Podaruj coś Pannie Młodej', 'description' => 'Cokolwiek – kwiatek z dekoracji, komplement, uśmiech. Ona zdecyduje czy się liczy.', 'xp_bonus' => 25, 'weight' => 8],
            ['icon' => '🕵️', 'title' => 'Zdobądź czyjś sekret weselny', 'description' => 'Coś, czego nikt nie wie. Nie musisz zdradzać. Wystarczy że sam wiesz.', 'xp_bonus' => 40, 'weight' => 5],
        ];

        foreach ($rewards as $r) {
            Reward::updateOrCreate(['title' => $r['title']], $r);
        }
    }
}
