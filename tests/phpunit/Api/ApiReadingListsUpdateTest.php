<?php

namespace MediaWiki\Extensions\ReadingLists\Tests\Api;

use ApiUsageException;
use MediaWiki\Extensions\ReadingLists\HookHandler;
use MediaWiki\Extensions\ReadingLists\Tests\ReadingListsTestHelperTrait;
use ApiTestCase;

/**
 * @covers \MediaWiki\Extensions\ReadingLists\Api\ApiReadingListsUpdate
 * @covers \MediaWiki\Extensions\ReadingLists\Api\ApiReadingLists
 * @group medium
 * @group API
 * @group Database
 */
class ApiReadingListsUpdateTest extends ApiTestCase {

	use ReadingListsTestHelperTrait;

	private $apiParams = [
		'action'  => 'readinglists',
		'format'  => 'json',
		'command' => 'update',
	];

	private $user;

	protected function setUp() : void {
		parent::setUp();
		$this->tablesUsed = array_merge( $this->tablesUsed, HookHandler::$testTables );
		$this->user = parent::getTestSysop()->getUser();
		$this->readingListsSetup();
	}

	/**
	 * @dataProvider updateProvider
	 */
	public function testUpdate( $apiParams, $expected ) {
		$listIds = $this->addLists( $this->user->mId, [
			[
				'rl_is_default' => 0,
				'rl_name' => 'dogs',
				'rl_date_created' => wfTimestampNow(),
				'rl_date_updated' => wfTimestampNow(),
				'rl_deleted' => 0,
			],
		] );
		$this->apiParams['list'] = $listIds[0];
		$this->apiParams['name'] = $apiParams['name'];
		$this->apiParams['description'] = $apiParams['description'];
		$result = $this->doApiRequestWithToken( $this->apiParams, null, $this->user );
		$this->assertEquals( $expected, $result[0]['update']['result'] );
	}

	public function updateProvider() {
		return [
			[ [ 'name' => 'new dogs', 'description' => 'Woof! Woof!' ], 'Success'
			]
		];
	}

	/**
	 * @dataProvider updateBatchProvider
	 */
	public function testUpdateBatch( $apiParams, $expected ) {
		$listIds = $this->addLists( $this->user->mId, [
			[
				'rl_is_default' => 0,
				'rl_name' => 'dogs',
				'rl_description' => 'Woof!',
				'rl_date_created' => wfTimestampNow(),
				'rl_date_updated' => wfTimestampNow(),
				'rl_deleted' => 0,
			],
			[
				'rl_is_default' => 0,
				'rl_name' => 'cats',
				'rl_description' => 'Meow!',
				'rl_date_created' => wfTimestampNow(),
				'rl_date_updated' => wfTimestampNow(),
				'rl_deleted' => 0,
			],
		] );
		$this->apiParams['batch'] = json_encode( [
			(object)[ "list" => $listIds[0],
				"name" => $apiParams[0]['name'],
				"description" => $apiParams[0]['description']
			],
			(object)[ "list" => $listIds[1],
				"name" => $apiParams[1]['name'],
				"description" => $apiParams[1]['description']
			],
		] );
		$result = $this->doApiRequestWithToken( $this->apiParams, null, $this->user );
		$this->assertEquals( $expected, $result[0]['update']['result'] );
	}

	public function updateBatchProvider() {
		return [
			[ [ [ 'name' => 'more dogs', 'description' => 'Woof! Woof!' ],
					[ 'name' => 'more cats', 'description' => 'Meow! Meow!' ],
				], 'Success'
			]
		];
	}

	/**
	 * @dataProvider updateProvider
	 */
	public function testUpdateDefault() {
		$listIds = $this->addLists( $this->user->mId, [
			[
				'rl_is_default' => 1,
				'rl_name' => 'dogs',
				'rl_description' => 'Woof!',
				'rl_date_created' => wfTimestampNow(),
				'rl_date_updated' => wfTimestampNow(),
				'rl_deleted' => 0,
			],
		] );
		$this->apiParams['list'] = $listIds[0];
		$this->apiParams['name'] = 'new dogs';
		$this->apiParams['description'] = 'Woof! Woof!';

		$this->expectException( ApiUsageException::class );
		$this->expectExceptionMessage( 'The default list cannot be updated.' );
		$result = $this->doApiRequestWithToken( $this->apiParams, null, $this->user );
	}

	protected function tearDown() : void {
		parent::tearDown();
		$this->readingListsTeardown();
	}
}
