<?php

namespace MediaWiki\Extensions\ReadingLists\Maintenance;

use CentralIdLookup;
use Maintenance;
use MediaWiki\Extensions\ReadingLists\ReadingListRepository;
use MediaWiki\Extensions\ReadingLists\Utils;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use User;

require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: __DIR__ . '/../../../maintenance/Maintenance.php';

/**
 * Maintenance script for purging unneeded DB rows (deleted lists/entries or orphaned sortkeys).
 * Purging deleted lists/entries limits clients' ability to sync deletes.
 * Purging orphaned sortkeys has no user-visible effect.
 * @ingroup Maintenance
 */
class Purge extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Purge unneeded database rows (deleted lists/entries or orphaned sortkeys).' );
		$this->addOption( 'before', 'Purge deleted lists/entries before this timestamp', true, true );
	}

	/**
	 * @inheritdoc
	 */
	public function execute() {
		$now = wfTimestampNow();
		if ( $this->hasOption( 'before' ) ) {
			$before = wfTimestamp( TS_MW, $this->getOption( 'before' ) );
			if ( !$before || $now <= $before ) {
				// Let's not delete all rows if the user entered an invalid timestamp.
				$this->error( 'Invalid timestamp', 1 );
			}
		} else {
			$before = Utils::getDeletedExpiry();
		}
		$this->output( "...purging deleted rows\n" );
		$this->getReadingListRepository()->purgeOldDeleted( $before );
		$this->output( "...purging orphaned sortkeys\n" );
		$this->getReadingListRepository()->purgeSortkeys();
		$this->output( "done.\n" );
	}

	/**
	 * Initializes the repository.
	 * @return ReadingListRepository
	 */
	private function getReadingListRepository() {
		$services = MediaWikiServices::getInstance();
		$loadBalancerFactory = $services->getDBLoadBalancerFactory();
		$dbw = Utils::getDB( DB_MASTER, $services );
		$dbr = Utils::getDB( DB_REPLICA, $services );
		$user = User::newSystemUser( 'Maintenance script', [ 'steal' => true ] );
		// There isn't really any way for this user to be non-local, but let's be future-proof.
		$centralId = CentralIdLookup::factory()->centralIdFromLocalUser( $user );
		$repository = new ReadingListRepository( $centralId, $dbw, $dbr, $loadBalancerFactory );
		$repository->setLogger( LoggerFactory::getInstance( 'readinglists' ) );
		return $repository;
	}

}

$maintClass = Purge::class;
require_once RUN_MAINTENANCE_IF_MAIN;
