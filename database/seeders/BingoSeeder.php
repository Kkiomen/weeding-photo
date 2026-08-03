<?php

namespace Database\Seeders;

use App\Models\BingoField;
use Illuminate\Database\Seeder;

class BingoSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            ['icon' => '🎩', 'title' => 'Ktoś stracił krawat'],
            ['icon' => '👠', 'title' => 'Panna Młoda zdjęła buty'],
            ['icon' => '🎤', 'title' => 'Karaoke się rozpoczęło'],
            ['icon' => '🍾', 'title' => 'Otwarty szampan sikał'],
            ['icon' => '💃', 'title' => 'Ktoś tańczy na krześle'],
            ['icon' => '📱', 'title' => 'Zdjęcie z Panem Młodym'],
            ['icon' => '👶', 'title' => 'Dziecko biega po parkiecie'],
            ['icon' => '🕺', 'title' => 'Ojciec zatańczył z córką'],
            ['icon' => '🥃', 'title' => 'Toast po polsku'],
            ['icon' => '🎂', 'title' => 'Krojenie tortu'],
            ['icon' => '🎆', 'title' => 'Zimne ognie'],
            ['icon' => '🌙', 'title' => 'Godzina 1:00 minęła'],
            ['icon' => '🍰', 'title' => 'Ktoś ma śmietanę na twarzy'],
            ['icon' => '👴', 'title' => 'Dziadek na parkiecie'],
            ['icon' => '💐', 'title' => 'Rzucanie bukietem'],
            ['icon' => '🦵', 'title' => 'Rzucanie podwiązki'],
            ['icon' => '📸', 'title' => 'Selfie 5+ osób'],
            ['icon' => '💋', 'title' => 'Widoczny ślad szminki'],
            ['icon' => '🧦', 'title' => 'Widać skarpetki Pana Młodego'],
            ['icon' => '🚬', 'title' => 'Ktoś wychodzi na papierosa'],
            ['icon' => '🎈', 'title' => 'Balon w rękach dorosłego'],
            ['icon' => '🍕', 'title' => 'Nocna przekąska pojawiła się'],
            ['icon' => '👗', 'title' => 'Kreacja porwana / zabrudzona'],
            ['icon' => '🎁', 'title' => 'Otwierany prezent'],
            ['icon' => '🕶️', 'title' => 'Ktoś w okularach po zmroku'],
            ['icon' => '🤝', 'title' => 'Poznałeś nową osobę'],
            ['icon' => '👑', 'title' => 'Ktoś z „koroną" (serwetka etc.)'],
            ['icon' => '🎭', 'title' => 'Sceniczna poza w tle'],
            ['icon' => '💧', 'title' => 'Ktoś płakał ze wzruszenia'],
            ['icon' => '💦', 'title' => 'Ktoś się bardzo spocił'],
            ['icon' => '🤳', 'title' => 'Grupowe selfie z Parą'],
            ['icon' => '🎼', 'title' => 'DJ puścił „Sto lat"'],
            ['icon' => '🍹', 'title' => 'Kolorowy drink w kadrze'],
            ['icon' => '👣', 'title' => 'Bosa stopa na parkiecie'],
            ['icon' => '📚', 'title' => 'Wpis do księgi pamiątkowej'],
            ['icon' => '🎪', 'title' => 'Ktoś organizuje wygłup'],
            ['icon' => '💤', 'title' => 'Ktoś zasnął na krześle'],
            ['icon' => '🎯', 'title' => 'Świadkowa robi coś głupiego'],
            ['icon' => '🎨', 'title' => 'Makijaż się rozmazał'],
            ['icon' => '🍫', 'title' => 'Fontanna czekolady w akcji'],
            ['icon' => '🥂', 'title' => 'Przemowa świadka'],
            ['icon' => '🍽️', 'title' => 'Pusty talerz „mistrza żarcia"'],
            ['icon' => '👨‍👩‍👧', 'title' => 'Trzy pokolenia w kadrze'],
            ['icon' => '🎳', 'title' => 'Ktoś przewrócił kieliszek'],
            ['icon' => '🏰', 'title' => 'Zdjęcie w oknie Zamku'],
        ];

        foreach ($fields as $f) {
            BingoField::updateOrCreate(['title' => $f['title']], $f);
        }
    }
}
