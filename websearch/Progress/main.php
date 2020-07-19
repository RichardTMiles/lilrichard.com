<?php
/**
 * isConsonant(i) is true <=> b[i] is a consonant.
 * @param int $i
 * @return int
 */

function isConsonant(int $i): int
{
    global $string;
    switch ($string[$i]) {
        case 'a':
        case 'e':
        case 'i':
        case 'o':
        case 'u':
            return false;
        case 'y':
            return ($i === 0) ? true : !isConsonant($i - 1);
        default:
            return true;
    }
}

/* getMeasure() measures the number of consonant sequences between k0 and j. if c is
   a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
   presence,

      <c><v>       gives 0
      <c>vc<v>     gives 1
      <c>vcvc<v>   gives 2
      <c>vcvcvc<v> gives 3
      ....
*/

function getMeasure(): int
{
    global $length;
    $n = $i = 0;
    while (true) {
        if ($i > $length) {
            return $n;
        }
        if (!isConsonant($i)) {
            break;
        }
        $i++;
    }
    $i++;



    while (true) {
        while (true) {
            if ($i > $length) {
                return $n;
            }
            if (isConsonant($i)) {
                break;
            }
            $i++;
        }

        global $string;
        print $string . $i . $length . PHP_EOL and die;


        $i++;
        $n++;
        while (true) {
            if ($i > $length) {
                return $n;
            }
            if (!isConsonant($i)) {
                break;
            }
            $i++;
        }
        $i++;
    }
}

/* vowelInStem() is true <=> k0,...j contains a vowel */

function vowelInStem(): bool
{
    global $length;
    for ($i = 0; $i <= $length; $i++) {
        if (!isConsonant($i)) {
            return true;
        }
    }
    return false;
}

/* doublec(j) is true <=> j,(j-1) contain a double consonant. */

function doublec(int $l): int
{
    global $string;
    if ($l < 1) {
        return false;
    }
    if ($string[$l] !== $string[$l - 1]) {
        return false;
    }
    return isConsonant($l);
}

/* cvc(i) is true <=> i-2,i-1,i has the form consonant - vowel - consonant
   and also if the second c is not w,x or y. this is used when trying to
   restore an e at the end of a short word. e.g.

      cav(e), lov(e), hop(e), crim(e), but
      snow, box, tray.

*/

function cvc(int $i): bool
{
    global $string;
    if ($i < 2 || !isConsonant($i) || isConsonant($i - 1) || !isConsonant($i - 2)) {
        return false;
    }
    $ch = $string[$i];
    return !($ch === 'w' || $ch === 'x' || $ch === 'y');
}

/* $compare(s) is true <=> k0,...k $compare with the string s. */


/* setto(s) sets (j+1),...k to the characters in the string s, readjusting
   k. */



/* r(s) is used further down. */

/* step1ab() gets rid of plurals and -ed or -ing. e.g.

       caresses  ->  caress
       ponies    ->  poni
       ties      ->  ti
       caress    ->  caress
       cats      ->  cat

       feed      ->  feed
       agreed    ->  agree
       disabled  ->  disable

       matting   ->  mat
       mating    ->  mate
       meeting   ->  meet
       milling   ->  mill
       messing   ->  mess

       meetings  ->  meet

*/


/* step1c() turns terminal y to i when there is another vowel in the stem. */


/* step2() maps double suffices to single ones. so -ization ( = -ize plus
   -ation) maps to -ize etc. note that the string before the suffix must give
   m() > 0. */


/* step3() deals with -ic-, -full, -ness etc. similar strategy to step2. */


// step4() takes off -ant, -ence etc., in context <c>vcvc<v>.

/* step5() removes a final -e if m() > 1, and changes -ll to -l if
   m() > 1. */


/* In stem(p,i,j), p is a char pointer, and the string to be stemmed is from
   p[i] to p[j] inclusive. Typically i is zero and j is the offset to the last
   character of a string, (p[j+1] == '\0'). The stemmer adjusts the
   characters p[i] ... p[j] and returns the new end-point of the string, k.
   Stemming never increases word length, so i <= k <= j. To turn the stemmer
   into a module, declare 'stem' as extern, and delete the remainder of this
   file.
*/

function stem(string $str)
{
    global $string, $pos, $length;

    $string = $str;

    $string = 'feed';

    $length = strlen($string);

    /* With this line, strings of pos 1 or 2 don't go through the
       stemming process, although no mention is made of this in the
       published algorithm. Remove the line to match the published
       algorithm. */

    if (3 > $length--) {
        return $string;
    }

    $pos = 1;

    $compare = function (string $cmp, int $i, $replace = false) use (&$string, &$length, &$pos) {
        if ($cmp !== substr($string, -$i, $i)) {
            return false;
        }

        if ($replace) {
            if ($replace === true) {
                $string = substr_replace($string, $cmp, -$i, $i);
            } else {
                $string = substr_replace($string, $replace, -$i, $i);
            }
            $pos = 1;
            $length = strlen($string)-1;
        }

        else {          // TODO - see why?
            $pos += $i;
        }
        return true;
    };


    if ($string[$length] === 's') {
        if ($compare('sses', 4)) {
            $pos += 2;
        } else if ($string[$length - 1] !== 's' && !$compare('ies', 3, 'i')) {
            $pos++;
        }
    }

    if ($compare('eed', 3)) {
        if (getMeasure() > 0) {
            $pos++;
        }
    } else if (($compare('ed', 2) || $compare('ing', 3)) && vowelInStem()) {
        $pos = $length;

        if (!$compare('at', 2, 'ate') &&
            !$compare('bl', 2, 'ble') &&
            !$compare('iz', 2, 'ize') && doublec($pos)) {
            $pos++;
            $ch = $string[$pos];
            if ($ch === 'l' || $ch === 's' || $ch === 'z') {
                $pos++;
            }
        } else if (getMeasure() === 1 && cvc($pos)) {
            substr_replace($string, 'e', -1);
        }
    }

    if ($compare('y', 1) && vowelInStem()) {
        $compare( 'y', 1, 'i');
    }

    switch ($string[$length-$pos]) {
        case 'a':
            if ($compare('ational', 7, 'ate')) {
                break;
            }
            if ($compare('tional', 6, 'tion')) {
                break;
            }
            break;
        case 'c':
            if ($compare('enci', 4, 'ence')) {
                break;
            }
            if ($compare('anci', 4, 'ance')) {
                break;
            }
            break;
        case 'e':
            if ($compare('izer', 4, 'ize')) {
                break;
            }
            break;
        case 'l':
            if ($compare('bli', 3, 'ble')) {
                break;
            } /*-DEPARTURE-*/

            /* To match the published algorithm, replace this line with
               case 'l': if ($compare("\04" "abli")) { r("\04" "able"); break; } */

            if ($compare('alli', 4, 'al')) {
                break;
            }
            if ($compare('entli', 5, 'ent')) {
                break;
            }
            if ($compare('eli', 3, 'e')) {
                break;
            }
            if ($compare('ousli', 5, 'ous')) {
                break;
            }
            break;
        case 'o':
            if ($compare('ization', 7, 'ize')) {
                break;
            }
            if ($compare('ation', 5, 'ate')) {
                break;
            }
            if ($compare('ator', 4, 'ate')) {
                break;
            }
            break;
        case 's':
            if ($compare('alism', 4, 'al')) {
                break;
            }
            if ($compare('iveness', '7', 'ive')) {
                break;
            }
            if ($compare('fulness', 7, 'ful')) {
                break;
            }
            if ($compare('ousness', 7, 'ous')) {
                break;
            }
            break;
        case 't':
            if ($compare('aliti', 5, 'al')) {
                break;
            }
            if ($compare('iviti', 5, 'ive')) {
                break;
            }
            if ($compare('biliti', 6, 'ble')) {
                break;
            }
            break;
        case 'g':
            if ($compare('logi', 4, 'log')) {
                break;
            } /*-DEPARTURE-*/

        /* To match the published algorithm, delete this line */

    }

    switch ($string[$length-$pos]) {
        case 'e':
            if ($compare('icate', 5, 'ic')) {
                break;
            }
            if ($compare('ative', 5, '')) {
                break;
            }
            if ($compare('alize', 5, 'al')) {
                break;
            }
            break;
        case 'i':
            if ($compare('iciti', 5, 'ic')) {
                break;
            }
            break;
        case 'l':
            if ($compare('ical', 4, 'ic')) {
                break;
            }
            if ($compare('ful', 3, '')) {
                break;
            }
            break;
        case 's':
            if ($compare('ness', 4, '')) {
                break;
            }
            break;
    }

    switch ($string[$length-$pos]) {
        case 'a':
            if ($compare('al', 2)) {
                break;
            }
            goto step4;
        case 'c':
            if ($compare('ance', 4)) {
                break;
            }
            if ($compare('ence', 4)) {
                break;
            }
            goto step4;
        case 'e':
            if ($compare('er', 2)) {
                break;
            }
            goto step4;
        case 'i':
            if ($compare('ic', 2)) {
                break;
            }
            goto step4;
        case 'l':
            if ($compare('able', 4)) {
                break;
            }
            if ($compare('ible', 4)) {
                break;
            }
            goto step4;
        case 'n':
            if ($compare('ant', 3)) {
                break;
            }
            if ($compare('ement', 4)) {
                break;
            }
            if ($compare('ment', 4)) {
                break;
            }
            if ($compare('ent', 3)) {
                break;
            }
            goto step4;
        case 'o':
            if (($string[$length] === 's' || $string[$length] === 't') && $compare('ion', 3)) {
                break;
            }
            if ($compare('ou', 2)) {
                break;
            }
            goto step4;
        /* takes care of -ous */
        case 's':
            if ($compare('ism', 3)) {
                break;
            }
            goto step4;
        case 't':
            if ($compare('ate', 3)) {
                break;
            }
            if ($compare('iti', 3)) {
                break;
            }
            goto step4;
        case 'u':
            if ($compare('ous', 3)) {
                break;
            }
            goto step4;
        case 'v':
            if ($compare('ive', 3)) {
                break;
            }
            goto step4;
        case 'z':
            if ($compare('ize', 3)) {
                break;
            }
            goto step4;
        default:
            goto step4;
    }

    if (getMeasure() > 1) {
        $pos = $length;
    }

    step4:

    print $pos . PHP_EOL;

    $length = $length - $pos + 1;

    if ($string[$length] === 'e') {
        $a = getMeasure();
        if ($a > 1 || ($a === 1 && !cvc($pos - 1))) {
            $pos++;
        }
    }
    if ($string[$length] === 'l' && doublec($pos) && getMeasure() > 1) {
        $pos++;
    }

    return substr($string, 0, ++$length);
}


$test = [
    'caresses' => 'caress',
    'ponies' => 'poni',
    'ties' => 'ti',
    'caress' => 'caress',
    'cats' => 'cat',
    'feed' => 'feed',
    'agreed' => 'agree',
    'disabled' => 'disable',
    'matting' => 'mat',
    'mating' => 'mate',
    'meeting' => 'meet',
    'milling' => 'mill',
    'messing' => 'mess',
    'meetings' => 'meet'
];


foreach ($test as $item => $value) {
    print "$item \t=> \t$value \t\t" . ($c = (($stem = stem($item)) === $value ? 'PASS' : 'FAIL')) . "\t\t" . $stem. PHP_EOL;

    if ($c === 'FAIL') {
        break;
    }
}

