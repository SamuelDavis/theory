<?php

class Render
{
    public static function symbolsFor(array $notes)
    {
        return array_map(function (int $note) {
            return static::symbolFor($note);
        }, $notes);
    }

    public static function symbolFor(int $note)
    {
        return Scale::CHROMATIC[$note % count(Scale::CHROMATIC)];
    }
}

class Guitar
{
    const STANDARD_TUNING = ['e', 'B', 'G', 'D', 'A', 'E'];

    public static function tabulate(array $notes, array $tuning = self::STANDARD_TUNING)
    {
        $frets = array_reduce($tuning, function (array $acc, string $string) use ($notes) {
            $root = array_search(strtoupper($string), Scale::CHROMATIC);
            foreach ($notes as $note) {
                $acc[$string] = $acc[$string] ?? [];
                $acc[$string][] = static::findFretFor($note, $root);
            }

            return $acc;
        }, []);

        return implode("\n", array_map(function (array $frets, string $string) {
            $frets = array_map(function (string $fret) {
                return strlen($fret) < 2 ? "-{$fret}" : $fret;
            }, $frets);

            return "{$string}|" . str_replace(' ', '-', implode('-', $frets));
        }, $frets, array_keys($frets)));
    }

    public static function findFretFor(int $note, int $root)
    {
        return (($note + 12) - $root) % count(Scale::CHROMATIC);
    }
}

class Scale
{
    const CHROMATIC = ['A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#'];
    const CHROMATIC_INTERVALS = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1];
    const MAJOR_INTERVALS = [0, 2, 2, 2, 1, 2, 2];
    const MINOR_INTERVALS = [0, 2, 1, 2, 2, 1, 2];
    const KEYS = [
        'Major' => self::MAJOR_INTERVALS,
        'Minor' => self::MINOR_INTERVALS,
    ];

    public static function of(int $root, array $intervals = self::MAJOR_INTERVALS)
    {
        $chromaticLength = count(static::CHROMATIC);

        return array_map(function (int $interval) use (&$root, $chromaticLength) {
            return ($root += $interval) % $chromaticLength;
        }, $intervals);
    }
}

class Chord
{
    const INTERVALS = [0, 2, 4];

    public static function withRoot(int $root, array $intervals = Scale::MAJOR_INTERVALS)
    {
        $scale = Scale::of($root, $intervals);

        return array_map(function (int $interval) use ($scale) {
            return $scale[$interval];
        }, static::INTERVALS);
    }
}

$note = $_POST['note'] ?? array_search('C', Scale::CHROMATIC);
$key = $_POST['key'] ?? array_keys(Scale::KEYS)[0];
$interval = Scale::KEYS[$key] ?? Scale::MAJOR_INTERVALS;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        button.selected {
            font-weight: bold;
            color: #1A88FE;
        }
    </style>
</head>
<body>
<form method="post" id="form">
    <select name="key" id="key">
        <?php foreach (array_keys(Scale::KEYS) as $value): ?>
            <option value="<?= $value ?>" <?= $key === $value ? 'selected' : '' ?>><?= $value ?></option>
        <?php endforeach; ?>
    </select>
    <?php foreach (Scale::CHROMATIC as $chromatic => $symbol): ?>
        <button name="note" type="submit" class="<?= $note == $chromatic ? 'selected' : '' ?>" value="<?= $chromatic ?>"><?= $symbol ?></button>
    <?php endforeach; ?>
</form>

<section>
    <h3>Scale of <?= Render::symbolFor($note) ?> <?= $key ?></h3>
    <pre><?= implode(', ', Render::symbolsFor(Scale::of($note, $interval))) ?></pre>
    <pre><?= print_r(Guitar::tabulate(Scale::of($note, $interval)), true) ?></pre>
</section>
<section>
    <h3><?= Render::symbolFor($note) ?> Chord</h3>
    <pre><?= implode(', ', Render::symbolsFor(Chord::withRoot($note, $interval))) ?></pre>
    <pre><?= print_r(Guitar::tabulate(Chord::withRoot($note, $interval)), true) ?></pre>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => document
        .getElementById('key')
        .addEventListener('change', () => document
            .getElementById('form')
            .submit()));
</script>
</body>
</html>
