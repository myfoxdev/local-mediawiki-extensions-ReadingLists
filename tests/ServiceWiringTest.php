<?php

namespace MediaWiki\Extensions\ReadingLists;

use MediaWiki\MediaWikiServices;

class ServiceWiringTest extends \PHPUnit_Framework_TestCase {

	public function testService() {
		$service = MediaWikiServices::getInstance()->getService( 'ReverseInterwikiLookup' );
		$this->assertInstanceOf( ReverseInterwikiLookupInterface::class, $service );
	}

}
