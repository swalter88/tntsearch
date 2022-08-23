<?php

namespace TeamTNT\TNTSearch;

class TNTFuzzyMatch
{
    public static function norm($vec)
    {
        $norm       = 0;
        $components = count($vec);

        for ($i = 0; $i < $components; $i++) {
            $norm += $vec[$i] * $vec[$i];
        }

        return sqrt($norm);
    }

    public static function dot($vec1, $vec2)
    {
        $prod       = 0;
        $components = count($vec1);

        for ($i = 0; $i < $components; $i++) {
            $prod += ($vec1[$i] * $vec2[$i]);
        }

        return $prod;
    }

    public static  function wordToVector($word)
    {
        $alphabet = "aAbBcCčČćĆdDđĐeEfFgGhHiIjJkKlLmMnNoOpPqQrRsSšŠtTvVuUwWxXyYzZžŽ1234567890'+ /";

        $result = [];
        foreach (str_split($word) as $w) {
            $result[] = strpos($alphabet, $w) + 1000000;
        }
        return $result;
    }

    public static  function angleBetweenVectors($a, $b)
    {
        $denominator = (self::norm($a) * self::norm($b));

        if ($denominator == 0) {
            return 0;
        }

        return self::dot($a, $b) / $denominator;
    }

    public static function hasCommonSubsequence($pattern, $str)
    {
        $pattern = mb_strtolower($pattern);
        $str     = mb_strtolower($str);

        $j             = 0;
        $patternLength = strlen($pattern);
        $strLength     = strlen($str);

        for ($i = 0; $i < $strLength && $j < $patternLength; $i++) {
            if ($pattern[$j] == $str[$i]) {
                $j++;
            }
        }

        return ($j == $patternLength);
    }

    public static function makeVectorSameLength($str, $pattern)
    {
        $j   = 0;
        $max = max(count($pattern), count($str));
        $a   = [];
        $b   = [];

        for ($i = 0; $i < $max && $j < $max; $i++) {
            if (isset($pattern[$j]) && isset($str[$i]) && $pattern[$j] == $str[$i]) {
                $j++;
                $b[] = $str[$i];
            } else {
                $b[] = 0;
            }
        }

        return $b;
    }

    public static function fuzzyMatchFromFile($pattern, $path)
    {
        $res   = [];
        $lines = fopen($path, "r");
        if ($lines) {
            while (!feof($lines)) {
                $line = rtrim(fgets($lines, 4096));
                if (self::hasCommonSubsequence($pattern, $line)) {
                    $res[] = $line;
                }
            }
            fclose($lines);
        }

        $paternVector = self::wordToVector($pattern);

        $sorted = [];
        foreach ($res as $caseSensitiveWord) {
            $word                   = mb_strtolower(trim($caseSensitiveWord));
            $wordVector             = self::wordToVector($word);
            $normalizedPaternVector = self::makeVectorSameLength($wordVector, $paternVector);

            $angle = self::angleBetweenVectors($wordVector, $normalizedPaternVector);

            if (strpos($word, $pattern) !== false) {
                $angle += 0.2;
            }
            $sorted[$caseSensitiveWord] = $angle;
        }

        arsort($sorted);
        return $sorted;
    }

    public static function fuzzyMatch($pattern, $items)
    {
        $res = [];

        foreach ($items as $item) {
            if (self::hasCommonSubsequence($pattern, $item)) {
                $res[] = $item;
            }
        }

        $paternVector = self::wordToVector($pattern);

        $sorted = [];
        foreach ($res as $word) {
            $word                   = trim($word);
            $wordVector             = self::wordToVector($word);
            $normalizedPaternVector = self::makeVectorSameLength($wordVector, $paternVector);

            $angle = self::angleBetweenVectors($wordVector, $normalizedPaternVector);

            if (strpos($word, $pattern) !== false) {
                $angle += 0.2;
            }

            $sorted[$word] = $angle;
        }

        arsort($sorted);

        return $sorted;
    }
}
