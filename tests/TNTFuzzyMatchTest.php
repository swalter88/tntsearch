<?php

use TeamTNT\TNTSearch\TNTFuzzyMatch;

class TNTFuzzyMatchTest extends PHPUnit\Framework\TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testNorm()
    {
        $vector     = [3, 4];
        $normalized = TNTFuzzyMatch::norm($vector);
        $this->assertEquals(5, $normalized);

        $vector     = [1, 2, 3, 4, 5];
        $normalized = TNTFuzzyMatch::norm($vector);
        $this->assertEquals(7.416198487095663, $normalized);
    }

    public function testDot()
    {
        $vector1 = [1, 2, -5];
        $vector2 = [4, 8, 1];

        $product = TNTFuzzyMatch::dot($vector1, $vector2);

        $this->assertEquals(15, $product);
    }

    public function testWordToVector()
    {
        $word   = "TNT";
        $vector = TNTFuzzyMatch::wordToVector($word);
        $this->assertEquals($vector, [1000055, 1000039, 1000055]);
    }

    public function testAngleBetweenVectors()
    {
        $vector1 = [1, 2, 3];
        $vector2 = [4, 5, 6];

        $angle = TNTFuzzyMatch::angleBetweenVectors($vector1, $vector2);

        $this->assertEquals(0.97463184619707621, $angle);
    }

    public function testHasCommonSubsequence()
    {
        $pattern1 = "tnsarh";
        $pattern2 = "ntnsearch";

        $res1 = TNTFuzzyMatch::hasCommonSubsequence($pattern1, 'tntsearch');
        $res2 = TNTFuzzyMatch::hasCommonSubsequence($pattern2, 'tntsearch');

        $this->assertEquals($res1, true);
        $this->assertEquals($res2, false);
    }

    public function testMakeVectorSameLength()
    {
        $wordVector    = TNTFuzzyMatch::wordToVector("tntsearch");
        $patternVector = TNTFuzzyMatch::wordToVector("tnth");

        $res = TNTFuzzyMatch::makeVectorSameLength($wordVector, $patternVector);
        $this->assertEquals([1000054, 1000038, 1000054, 0, 0, 0, 0, 0, 1000026], $res);
    }

    public function testFuzzyMatchFromFile()
    {
        $res = TNTFuzzyMatch::fuzzyMatchFromFile('search', __DIR__.'/_files/english_wordlist_2k.txt');

        $equal = bccomp($res['search'], 1.2, 2);
        $this->assertEquals(0, $equal);

        $equal = bccomp($res['research'], 1.06, 2);
        $this->assertEquals(0, $equal);
    }

    public function testFuzzyMatch()
    {
        $res = TNTFuzzyMatch::fuzzyMatch('search', ['search', 'research', 'something']);

        $equal = bccomp($res['search'], 1.2, 2);
        $this->assertEquals(0, $equal);

        $equal = bccomp($res['research'], 1.06, 2);
        $this->assertEquals(0, $equal);
    }
}
