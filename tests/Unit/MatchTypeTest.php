<?php

namespace Tests\Unit;

use App\Enums\MatchType;
use PHPUnit\Framework\TestCase;

class MatchTypeTest extends TestCase
{
    public function test_contains_ignores_case(): void
    {
        $this->assertTrue(MatchType::Contains->matches('BEA Albert Heijn 1234 AMSTERDAM', 'albert heijn'));
        $this->assertFalse(MatchType::Contains->matches('BEA Jumbo 1234', 'albert heijn'));
    }

    public function test_equals_needs_the_whole_value_but_ignores_case(): void
    {
        $this->assertTrue(MatchType::Equals->matches('Netflix', 'netflix'));
        $this->assertFalse(MatchType::Equals->matches('Netflix International', 'netflix'));
    }

    public function test_regex_matches(): void
    {
        $this->assertTrue(MatchType::Regex->matches('Betaling Jumbo 55', 'albert heijn|jumbo'));
        $this->assertFalse(MatchType::Regex->matches('Betaling Lidl', 'albert heijn|jumbo'));
    }

    public function test_a_slash_in_a_regex_pattern_is_safe(): void
    {
        $this->assertTrue(MatchType::Regex->matches('ref 12/34', '12/34'));
    }

    public function test_an_invalid_regex_counts_as_no_match_instead_of_crashing(): void
    {
        $this->assertFalse(MatchType::Regex->matches('anything', '(unclosed'));
    }

    public function test_special_characters_are_matched_literally_by_contains(): void
    {
        $this->assertTrue(MatchType::Contains->matches('Betaling (Spotify) 9,99', '(spotify)'));
    }
}
