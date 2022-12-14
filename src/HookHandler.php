<?php

namespace MediaWiki\Extensions\ReadingLists;

use DatabaseUpdater;
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IMaintainableDatabase;

/**
 * Static entry points for hooks.
 */
class HookHandler {

	/** @var array Tables which need to be set up / torn down for tests */
	public static $testTables = [
		'reading_list',
		'reading_list_entry',
		'reading_list_sortkey',
		'reading_list_entry_sortkey',
	];

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		if ( Utils::isCentralWiki( MediaWikiServices::getInstance() ) ) {
			$baseDir = dirname( __DIR__ );
			$updater->addExtensionTable( 'reading_list', "$baseDir/sql/readinglists.sql" );
		}
		return true;
	}

	/**
	 * Setup the centralauth tables in the current DB, so we don't have
	 * to worry about rights on another database. The first time it's called
	 * we have to set the DB prefix ourselves, and reset it back to the original
	 * so that CloneDatabase will work. On subsequent runs, the prefix is already
	 * set up for us.
	 *
	 * @param IMaintainableDatabase $db
	 * @param string $prefix
	 */
	public static function onUnitTestsAfterDatabaseSetup( $db, $prefix ) {
		global $wgReadingListsCluster, $wgReadingListsDatabase;
		$wgReadingListsCluster = false;
		$wgReadingListsDatabase = false;

		$originalPrefix = $db->tablePrefix();
		$db->tablePrefix( $prefix );
		if ( !$db->tableExists( 'reading_list' ) ) {
			$baseDir = dirname( __DIR__ );
			$db->sourceFile( "$baseDir/sql/readinglists.sql" );
		}
		$db->tablePrefix( $originalPrefix );
	}

	/**
	 * Cleans up tables created by onUnitTestsAfterDatabaseSetup() above
	 */
	public static function onUnitTestsBeforeDatabaseTeardown() {
		$db = wfGetDB( DB_MASTER );
		foreach ( self::$testTables as $table ) {
			$db->dropTable( $table );
		}
	}

}
