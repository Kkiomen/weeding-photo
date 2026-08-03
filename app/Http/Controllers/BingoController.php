<?php

namespace App\Http\Controllers;

use App\Models\BingoCard;
use App\Models\BingoField;
use App\Models\BingoMark;
use App\Models\Guest;
use App\Models\Photo;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BingoController extends Controller
{
    private const FREE_CENTER_POSITION = 12; // środek karty 5x5

    private const XP_LINE = 50;

    private const XP_FULL_CARD = 300;

    public function __construct(private PhotoStorage $storage) {}

    public function index(Request $request): Response
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        $card = $this->getOrCreateCard($guest);

        return Inertia::render('Bingo', [
            'card' => $this->transformCard($card),
        ]);
    }

    public function markField(Request $request, BingoField $field): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|max:15360',
        ]);

        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');
        $card = $this->getOrCreateCard($guest);

        $position = array_search($field->id, $card->field_ids, true);
        if ($position === false) {
            return back()->with('flash', ['type' => 'error', 'message' => 'To pole nie należy do Twojej karty.']);
        }

        $existing = BingoMark::where('card_id', $card->id)
            ->where('field_id', $field->id)
            ->first();
        if ($existing) {
            return back()->with('flash', ['type' => 'info', 'message' => 'To pole jest już oznaczone.']);
        }

        $file = $request->file('photo');
        $stored = $this->storage->store($file, 'bingo');

        $photo = Photo::create([
            'guest_id' => $guest->id,
            'task_id' => null,
            'path' => $stored['path'],
            'thumb_path' => $stored['thumb_path'],
            'file_hash' => $stored['hash'],
        ]);

        BingoMark::create([
            'card_id' => $card->id,
            'field_id' => $field->id,
            'photo_id' => $photo->id,
            'marked_at' => now(),
        ]);

        $guest->increment('xp', 10);

        $bonus = $this->checkWins($card);
        if ($bonus > 0) {
            $guest->increment('xp', $bonus);
        }

        $flash = ['type' => 'success', 'message' => '+10 XP'];
        if ($bonus > 0) {
            $flash['message'] .= " · BONUS +{$bonus} XP!";
        }

        return back()->with('flash', $flash);
    }

    private function getOrCreateCard(Guest $guest): BingoCard
    {
        $card = BingoCard::where('guest_id', $guest->id)->first();
        if ($card) {
            return $card;
        }

        $fieldIds = BingoField::inRandomOrder()->limit(24)->pluck('id')->toArray();
        // Wstaw specjalne pole na środek (id=null oznacza freebie)
        // Ale schemat wymaga foreign key na bingo_fields; użyjemy pierwszego pola jako "wolne miejsce"
        // Lepsze rozwiązanie: dodać seed'owe pole "❤️ Wolne pole" i wsadzić w środek.
        $freeField = BingoField::firstOrCreate(
            ['title' => '❤️ Wolne pole'],
            ['icon' => '❤️', 'description' => 'Środek karty – zawsze zaliczone.']
        );

        // Usuń free field z random pool jeśli tam wpadł
        $fieldIds = array_values(array_filter($fieldIds, fn ($id) => $id !== $freeField->id));
        // Uzupełnij jeśli po odfiltrowaniu mamy mniej niż 24
        if (count($fieldIds) < 24) {
            $extra = BingoField::whereNotIn('id', array_merge($fieldIds, [$freeField->id]))
                ->inRandomOrder()
                ->limit(24 - count($fieldIds))
                ->pluck('id')
                ->toArray();
            $fieldIds = array_merge($fieldIds, $extra);
        }
        $fieldIds = array_slice($fieldIds, 0, 24);

        // Wstaw free field na pozycję 12 (środek)
        array_splice($fieldIds, self::FREE_CENTER_POSITION, 0, [$freeField->id]);

        $card = BingoCard::create([
            'guest_id' => $guest->id,
            'field_ids' => $fieldIds,
            'lines_won' => 0,
            'full_card_won' => false,
        ]);

        // Auto-mark środka
        BingoMark::create([
            'card_id' => $card->id,
            'field_id' => $freeField->id,
            'photo_id' => null,
            'marked_at' => now(),
        ]);

        return $card;
    }

    private function transformCard(BingoCard $card): array
    {
        $fields = BingoField::whereIn('id', $card->field_ids)->get()->keyBy('id');
        $marks = BingoMark::with('photo:id,thumb_path')
            ->where('card_id', $card->id)
            ->get()
            ->keyBy('field_id');

        $cells = [];
        foreach ($card->field_ids as $position => $fieldId) {
            $field = $fields[$fieldId] ?? null;
            $mark = $marks[$fieldId] ?? null;

            $cells[] = [
                'position' => $position,
                'field_id' => $fieldId,
                'icon' => $field?->icon,
                'title' => $field?->title,
                'description' => $field?->description,
                'is_center' => $position === self::FREE_CENTER_POSITION,
                'marked' => $mark !== null,
                'thumb' => $mark?->photo ? \Illuminate\Support\Facades\Storage::url($mark->photo->thumb_path) : null,
            ];
        }

        return [
            'cells' => $cells,
            'lines_won' => $card->lines_won,
            'full_card_won' => $card->full_card_won,
        ];
    }

    private function checkWins(BingoCard $card): int
    {
        $card->refresh();
        $markedFieldIds = BingoMark::where('card_id', $card->id)->pluck('field_id')->all();
        $positions = [];
        foreach ($card->field_ids as $pos => $fieldId) {
            if (in_array($fieldId, $markedFieldIds, true)) {
                $positions[] = $pos;
            }
        }
        $marked = array_flip($positions);

        // 5 rzędów + 5 kolumn + 2 przekątne
        $lines = [];
        for ($r = 0; $r < 5; $r++) {
            $lines[] = [$r * 5, $r * 5 + 1, $r * 5 + 2, $r * 5 + 3, $r * 5 + 4];
        }
        for ($c = 0; $c < 5; $c++) {
            $lines[] = [$c, $c + 5, $c + 10, $c + 15, $c + 20];
        }
        $lines[] = [0, 6, 12, 18, 24];
        $lines[] = [4, 8, 12, 16, 20];

        $completedLines = 0;
        foreach ($lines as $line) {
            $allMarked = true;
            foreach ($line as $pos) {
                if (! isset($marked[$pos])) {
                    $allMarked = false;
                    break;
                }
            }
            if ($allMarked) {
                $completedLines++;
            }
        }

        $newLines = max(0, $completedLines - $card->lines_won);
        $bonus = $newLines * self::XP_LINE;

        $fullCard = count($marked) === 25 && ! $card->full_card_won;
        if ($fullCard) {
            $bonus += self::XP_FULL_CARD;
            $card->full_card_won = true;
        }

        if ($newLines > 0 || $fullCard) {
            $card->lines_won = $completedLines;
            $card->save();
        }

        return $bonus;
    }
}
