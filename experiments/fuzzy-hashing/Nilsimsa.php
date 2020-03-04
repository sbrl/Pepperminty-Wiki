<?php

/**
 * PHP Library to calculate and compare Nilsimsa digests.
 *
 * The Nilsimsa hash is a locality senstive hash function. Generally similar
 * documents will have similar Nilsimsa digests. The Hamming distance between
 * the digests can be used to approximate the similarity between documents. For
 * further information consult http://en.wikipedia.org/wiki/Nilsimsa_Hash and
 * the references (particularly Damiani et al.)
 *
 * Implementation details:
 * The Nilsimsa class takes in a data parameter which is the string of the
 * document to digest Calling the methods hexdigest() and digest() give the
 * nilsimsa digests in hex or array format. The helper function compare_digests
 * takes in two digests and computes the Nilsimsa score. You can also use
 * compare_files() and compare_strings() to compare files and strings directly.
 *
 * This code is a port of py-nilsimsa located at
 * https://code.google.com/p/py-nilsimsa/
 */

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Bill Eager
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the 'Software'), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Nilsimsa
{
    /**
     * Tran53 hash constant
     * @var array
     */
    const TRAN = [
        0x02,0xd6,0x9e,0x6f,0xf9,0x1d,0x04,0xab,0xd0,0x22,0x16,0x1f,0xd8,0x73,0xa1,0xac,
        0x3b,0x70,0x62,0x96,0x1e,0x6e,0x8f,0x39,0x9d,0x05,0x14,0x4a,0xa6,0xbe,0xae,0x0e,
        0xcf,0xb9,0x9c,0x9a,0xc7,0x68,0x13,0xe1,0x2d,0xa4,0xeb,0x51,0x8d,0x64,0x6b,0x50,
        0x23,0x80,0x03,0x41,0xec,0xbb,0x71,0xcc,0x7a,0x86,0x7f,0x98,0xf2,0x36,0x5e,0xee,
        0x8e,0xce,0x4f,0xb8,0x32,0xb6,0x5f,0x59,0xdc,0x1b,0x31,0x4c,0x7b,0xf0,0x63,0x01,
        0x6c,0xba,0x07,0xe8,0x12,0x77,0x49,0x3c,0xda,0x46,0xfe,0x2f,0x79,0x1c,0x9b,0x30,
        0xe3,0x00,0x06,0x7e,0x2e,0x0f,0x38,0x33,0x21,0xad,0xa5,0x54,0xca,0xa7,0x29,0xfc,
        0x5a,0x47,0x69,0x7d,0xc5,0x95,0xb5,0xf4,0x0b,0x90,0xa3,0x81,0x6d,0x25,0x55,0x35,
        0xf5,0x75,0x74,0x0a,0x26,0xbf,0x19,0x5c,0x1a,0xc6,0xff,0x99,0x5d,0x84,0xaa,0x66,
        0x3e,0xaf,0x78,0xb3,0x20,0x43,0xc1,0xed,0x24,0xea,0xe6,0x3f,0x18,0xf3,0xa0,0x42,
        0x57,0x08,0x53,0x60,0xc3,0xc0,0x83,0x40,0x82,0xd7,0x09,0xbd,0x44,0x2a,0x67,0xa8,
        0x93,0xe0,0xc2,0x56,0x9f,0xd9,0xdd,0x85,0x15,0xb4,0x8a,0x27,0x28,0x92,0x76,0xde,
        0xef,0xf8,0xb2,0xb7,0xc9,0x3d,0x45,0x94,0x4b,0x11,0x0d,0x65,0xd5,0x34,0x8b,0x91,
        0x0c,0xfa,0x87,0xe9,0x7c,0x5b,0xb1,0x4d,0xe5,0xd4,0xcb,0x10,0xa2,0x17,0x89,0xbc,
        0xdb,0xb0,0xe2,0x97,0x88,0x52,0xf7,0x48,0xd3,0x61,0x2c,0x3a,0x2b,0xd1,0x8c,0xfb,
        0xf1,0xcd,0xe4,0x6a,0xe7,0xa9,0xfd,0xc4,0x37,0xc8,0xd2,0xf6,0xdf,0x58,0x72,0x4e,
    ];

    /**
     * Stores whether the digest is complete
     *
     * @var boolean
     */
    private $digestComputed;

    /**
     * Stores the number of characters in the string digested
     *
     * @var int
     */
    private $numChar;

    /**
     * Stores the accumulator as a 256-bit vector
     *
     * @var array
     */
    private $acc;

    /**
     * Stores the active window used in {process} for hashing
     *
     * @var array
     */
    private $window;

    /**
     * @var mixed
     */
    private $digest;
    
    /**
     * The target length of the hash.
     * @var int
     */
    private $length;

    /**
     * Constructor
     *
     * @param string $data The data to process
     */
    public function __construct($data = null, $length = null)
    {
        if($length !== null) $this->length = $length;
        $this->digestComputed = false;
        $this->numChar        = 0;
        $this->acc            = array_fill(0, $this->length, 0);
        $this->window         = [];

        if ($data) {
            $this->process($data);
        }
    }

    /**
     * Computes the hash of all of the trigrams in the chunk using a window of
     * length 5
     *
     * @param string $chunk The chunk to process
     */
    public function process($chunk)
    {
        foreach (str_split($chunk) as $char) {
            $this->numChar++;
            $c             = ord($char);
            $windowLength = count($this->window);

            if ($windowLength > 1) {
                // seen at least three characters
                $this->acc[$this->tranHash(
                    $c, $this->window[0], $this->window[1], 0
                )]
                    += 1;
            }
            if ($windowLength > 2) {
                // seen at least four characters
                $this->acc[$this->tranHash(
                    $c, $this->window[0], $this->window[2], 1
                )]
                    += 1;
                $this->acc[$this->tranHash(
                    $c, $this->window[1], $this->window[2], 2
                )]
                    += 1;
            }
            if ($windowLength > 3) {
                // have a full window
                $this->acc[$this->tranHash(
                    $c, $this->window[0], $this->window[3], 3
                )]
                    += 1;
                $this->acc[$this->tranHash(
                    $c, $this->window[1], $this->window[3], 4
                )]
                    += 1;
                $this->acc[$this->tranHash(
                    $c, $this->window[2], $this->window[3], 5
                )]
                    += 1;
                // duplicate hashes, used to maintain 8 trigrams per character
                $this->acc[$this->tranHash(
                    $this->window[3], $this->window[0], $c, 6
                )]
                    += 1;
                $this->acc[$this->tranHash(
                    $this->window[3], $this->window[2], $c, 7
                )]
                    += 1;
            }

            // add current character to the window, remove the previous character
            array_unshift($this->window, $c);

            if ($windowLength >= 4) {
                $this->window = array_slice($this->window, 0, 4);
            }
        }
    }

    /**
     * Implementation of the Tran53 hash algorithm
     *
     * @param int $a Input A
     * @param int $b Input B
     * @param int $c Input C
     * @param int $n Input N
     *
     * @return int
     */
    public function tranHash($a, $b, $c, $n)
    {
        return ((
            (self::TRAN[($a + $n) & 255] ^ self::TRAN[$b] * ($n + $n + 1)) +
             self::TRAN[($c) ^ self::TRAN[$n]]
        ) & ($this->length-1)); // Was 255
    }

    /**
     * Returns the digest as a hex string. Computes it if it isn't computed
     * already.
     *
     * @return string The digest
     */
    public function hexDigest()
    {
        if ( ! $this->digestComputed) {
            $this->computeDigest();
        }

        $output = null;

        foreach ($this->digest as $i) {
            $output .= sprintf('%02x', $i);
        }
        return $output;
    }

    

    /**
     * Returns the digest as an array. Computes it if it isn't computed already.
     *
     * @return array The digest
     */
    public function digest()
    {
        if ( ! $this->digestComputed) {
            $this->computeDigest();
        }

        return $this->digest;
    }

    /**
     * Using a threshold (mean of the accumulator), computes the nilsimsa
     * digest after completion. Sets complete flag to true and stores result in
     * $this->digest
     */
    public function computeDigest()
    {
        $numTrigrams = 0;

        if ($this->numChar == 3) {
            // 3 chars -> 1 trigram
            $numTrigrams = 1;
        }
        else if ($this->numChar == 4) {
            // 4 chars -> 4 trigrams
            $numTrigrams = 4;
        }
        else if ($this->numChar > 4) {
            // > 4 chars -> 8 for each CHAR
            $numTrigrams = 8 * $this->numChar - 28;
        }

        // threshhold is the mean of the acc buckets
        $threshold = $numTrigrams / $this->length;

        $digest = array_fill(0, $this->length/8, 0);

        for ($i = 0; $i < ($this->length-2); $i++) {
            if ($this->acc[$i] > $threshold) {
                // equivalent to i/8, 2**(i mod 7)
                $digest[$i >> 3] += 1 << ($i & 7);
            }
        }

        // set flag to true
        $this->digestComputed = true;
        // store result in digest, reversed
        $this->digest = array_reverse($digest);
    }
    
    static function hash($data, $length = 256) {
        $hasher = new self($data, $length);
        return $hasher->hexDigest();
    }
}

function lines_count($handle) {
    fseek($handle, 0);
    $count = 0;
    while(fgets($handle) !== false) $count++;
    return $count;
}

$mode = $argv[1] ?? "help";

switch($mode) {
    case "typos":
        $handle = fopen("typos.csv", "r");
        $line_count = lines_count($handle);
        echo("$line_count lines total\n");
        
        $sizes = [ 256, 128, 64, 32, 16, 8 ];
        foreach($sizes as $size) {
            fseek($handle, 0);fgets($handle); // Skipt he first line since it's the header
            
            $count = 0; $count_same = 0; $skipped = 0;
            $same = []; $not_same = [];
            while(($line = fgets($handle)) !== false) {
                $parts = explode(",", trim($line), 2);
                if(strlen($parts[1]) < 3) {
                    $skipped++;
                    continue;
                }
                $hash_a = Nilsimsa::hash($parts[0], $size);
                $hash_b = Nilsimsa::hash($parts[1], $size);
                
                $count++;
                if($hash_a == $hash_b) {
                    $count_same++;
                    $same[] = $parts;
                }
                else $not_same[] = $parts;
                echo("$count_same / $count ($skipped skipped)\r");
            }
            
            file_put_contents("$size-same.csv", implode("\n", array_map(function ($el) {
                return implode(",", $el);
            }, $same)));
            file_put_contents("$size-not-same.csv", implode("\n", array_map(function ($el) {
                return implode(",", $el);
            }, $not_same)));
            
            echo(str_pad($size, 10)."→ $count_same / $count (".round(($count_same/$count)*100, 2)."%), $skipped skipped\n");
        }
        
        break;
    
    case "helloworld":
        foreach([ 256, 128, 64, 32, 16, 8 ] as $size) {
            echo(str_pad($size, 10).Nilsimsa::hash("hello, world!", $size));
            // echo(str_pad($size, 10).Nilsimsa::hash("pinnapple", $size));
            echo("\n");
        }
        break;
    
    case "help":
    default:
        echo("Mode $mode not recognised. Available modes:\n");
        echo("    helloworld    Show different hash sizes\n");
        echo("    typos         Compare typos in typos.csv and calculate statistics\n");
        break;
}

/*
 * TODO: Explore BK-Trees. SymSpell might be orders of magnitudes faster, but that's compared to a regular BK-Tree - and it's *much* more complicated.
 * If we instead use Nilsimsa + the hamming distance comparison funnction (which we removed & will need to reinstate), is it faster than doing lots of `levenshtein()` calls?
 * Experimentation is needed.
 * See also gmp_hamdist() (which requires the gmp PHP extension).
 */
