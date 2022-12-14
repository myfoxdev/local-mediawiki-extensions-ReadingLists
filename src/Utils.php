<?php

namespace MediaWiki\Extensions\ReadingLists;

use MediaWiki\MediaWikiServices;
use UnexpectedValueException;
use Wikimedia\Rdbms\DBConnRef;

/**
 * Static utility methods.
 */
class Utils {

	/**
	 * Get a database connection for the reading lists database.
	 * @param int $db Index of the connection to get, e.g. DB_MASTER or DB_REPLICA.
	 * @param MediaWikiServices $services
	 * @return DBConnRef
	 */
	public static function getDB( $db, $services ) {
		$extensionConfig = $services->getConfigFactory()->makeConfig( 'ReadingLists' );
		$cluster = $extensionConfig->get( 'ReadingListsCluster' );
		$database = $extensionConfig->get( 'ReadingListsDatabase' );

		$loadBalancerFactory = $services->getDBLoadBalancerFactory();
		$loadBalancer = $cluster
			? $loadBalancerFactory->getExternalLB( $cluster )
			: $loadBalancerFactory->getMainLB( $database );
		return $loadBalancer->getConnectionRef( $db, [], $database );
	}

	/**
	 * Check if we are on the central wiki. ReadingLists is mostly wiki agnostic but one wiki
	 * must be selected for things that should not be duplicated (such as jobs and schema
	 * updates).
	 * @param MediaWikiServices $services
	 * @return bool
	 */
	public static function isCentralWiki( $services ) {
		$extensionConfig = $services->getConfigFactory()->makeConfig( 'ReadingLists' );
		$centralWiki = $extensionConfig->get( 'ReadingListsCentralWiki' );
		if ( $centralWiki === false ) {
			return true;
		}
		return ( wfWikiID() === $centralWiki );
	}

	/**
	 * Returns the timestamp at which deleted items expire (can be purged).
	 * @return string Timestamp in TS_MW format
	 * @throws UnexpectedValueException When the extension is configured incorrectly.
	 */
	public static function getDeletedExpiry() {
		$services = MediaWikiServices::getInstance();
		$extensionConfig = $services->getConfigFactory()->makeConfig( 'ReadingLists' );
		$days = $extensionConfig->get( 'ReadingListsDeletedRetentionDays' );
		$unixTimestamp = strtotime( '-' . $days . ' days' );
		$timestamp = wfTimestamp( TS_MW, $unixTimestamp );
		if ( !$timestamp || !$unixTimestamp ) {
			// not really an argument but close enough
			throw new UnexpectedValueException( 'Invalid $wgReadingListsDeletedRetentionDays value: '
				. $days );
		}
		return $timestamp;
	}

}
