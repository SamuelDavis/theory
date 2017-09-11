<?php

const STANDARD_TUNING = ['e', 'B', 'G', 'D', 'A', 'E'];
const CHROMATIC = ['A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#'];
const CHROMATIC_SCALE_INTERVALS = [0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1];
const MAJOR_SCALE_INTERVALS = [0, 2, 2, 2, 1, 2, 2, 1];
const MINOR_SCALE_INTERVALS = [0, 2, 1, 2, 2, 1, 2, 2];
const MAJOR_CHORD_INTERVALS = [0, 2, 4];

function calculateNeck(array $tuning, $tab = false, $note = null)
{
    $notes = is_array($note) ? $note : [$note];
    $result = [];

    foreach ($tuning as $string => $rootNote) {
        foreach (calculateScale(CHROMATIC_SCALE_INTERVALS, $rootNote) as $fret => $guitarNote) {
            if (!$note || in_array($guitarNote, $notes)) {
                $value = $tab ? $fret : $guitarNote;
                $value = strlen($value) < 2 ? "-{$value}" : $value;
                $result[$rootNote][$fret] = $value;
            } else {
                $result[$rootNote][$fret] = '--';
            }
        }
    }

    return $result;
}

function calculateScale(array $intervals, $root = 'C')
{
    $result = [];
    $i = array_search(strtoupper($root), CHROMATIC);
    $chromaticLength = count(CHROMATIC);

    foreach ($intervals as $interval) {
        $i += $interval;
        $i %= $chromaticLength;
        $result[] = CHROMATIC[$i];
    }

    return $result;
}

function calculateChord(array $scale = MAJOR_SCALE_INTERVALS, array $chord = MAJOR_CHORD_INTERVALS, $root = 'C')
{
    $notes = calculateScale($scale, $root);

    return array_reduce($chord, function (array $acc, $i) use ($notes) {
        $acc[] = $notes[$i];

        return $acc;
    }, []);
}

$drawing = "\n";
$chord = calculateChord(MAJOR_SCALE_INTERVALS, MAJOR_CHORD_INTERVALS, 'G');
foreach (calculateNeck(STANDARD_TUNING, true, $chord) as $string => $notes) {
    $drawing .= $string . '|' . implode('|', $notes) . "\n";
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h1>Gmaj Chord: <?= implode(', ', $chord) ?></h1>
<pre><?= $drawing ?></pre>
<h1>Guitar</h1>
<table>
    <tbody>
    <?php foreach (STANDARD_TUNING as $rootNote): ?>
        <tr>
            <?php foreach (calculateScale(CHROMATIC_SCALE_INTERVALS, $rootNote) as $guitarNote): ?>
                <td><?= $guitarNote ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<h1>Major Scales</h1>
<table>
    <tbody>
    <?php foreach (CHROMATIC as $rootNote): ?>
        <tr>
            <?php foreach (calculateScale(MAJOR_SCALE_INTERVALS, $rootNote) as $majorNote): ?>
                <td><?= $majorNote ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<h1>Minor Scales</h1>
<table>
    <tbody>
    <?php foreach (CHROMATIC as $rootNote): ?>
        <tr>
            <?php foreach (calculateScale(MINOR_SCALE_INTERVALS, $rootNote) as $majorNote): ?>
                <td><?= $majorNote ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
