<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
	public function getFilters()
	{
		return [
			new TwigFilter('prefix_banned_words', [$this, 'prefixBannedWords']),
		];
	}

	public function prefixBannedWords($bannedWords)
	{
		// Add ! prefix to banned words for searching.
		foreach($bannedWords as &$value) {
			$value = '!' . $value;
		}
		unset($value);

		return $bannedWords;
	}
}