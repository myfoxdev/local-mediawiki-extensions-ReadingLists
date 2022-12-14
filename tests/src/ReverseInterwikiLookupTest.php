<?php

namespace MediaWiki\Extensions\ReadingLists;

use MediaWiki\Interwiki\InterwikiLookup;

class ReverseInterwikiLookupTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideLookup
	 * @param string $expectedPrefix Expected IW prefix.
	 * @param string $domain Domain (project) of reading list entry.
	 * @param array[] $iwTable Interwiki table data returned by InterwikiLookup::getAllPrefixes().
	 */
	public function testLookup( $expectedPrefix, $domain, $iwTable ) {
		$iwLookup = $this->getMockForAbstractClass( InterwikiLookup::class );
		$iwLookup->expects( $this->any() )->method( 'getAllPrefixes' )->willReturn( $iwTable );
		$lookup = new ReverseInterwikiLookup( $iwLookup, 'en.wikipedia.org' );
		$actualPrefix = $lookup->lookup( $domain );
		$this->assertSame( $expectedPrefix, $actualPrefix );
	}

	/**
	 * @return array [ expected iw prefix, hostname, interwiki table ]
	 */
	public function provideLookup() {
		$iwTable = [
			[ 'iw_prefix' => 'de', 'iw_url' => 'https://de.wikipedia.org/wiki/$1',
				'iw_wikiid' => 'dewiki', 'iw_local' => true ],
			[ 'iw_prefix' => 'b', 'iw_url' => 'https://en.wikibooks.org/wiki/$1',
			  'iw_wikiid' => 'enwikibooks', 'iw_local' => true ],
			[ 'iw_url' => 'invalid host' ],
		];
		return [
			'no match' => [ null, 'foo.bar.baz', $iwTable ],
			'local' => [ '', 'en.wikipedia.org', $iwTable ],
			'exact match' => [ 'de', 'de.wikipedia.org', $iwTable ],
			'exact match 2' => [ 'b', 'en.wikibooks.org', $iwTable ],
			'cross-project + cross-lang' => [ [ 'b', 'de', ], 'de.wikibooks.org', $iwTable ],
			'invalid language code' => [ null, 'nosuchlang.wikipedia.org', $iwTable ],
		];
	}

}
