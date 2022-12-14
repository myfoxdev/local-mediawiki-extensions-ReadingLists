<?php
/**
 * @file
 * Documentation hack for plain objects returned by DB queries.
 * For the benefit of IDEs only, won't be used outside phpdoc.
 */

namespace MediaWiki\Extensions\ReadingLists\Doc;

/**
 * Database row for reading_list.
 * Represents a list of pages (potentially from multiple wikis) plus some display-oriented metadata.
 */
trait ReadingListRow {

	/** @var string Primary key. */
	public $rl_id;

	/** @var string Central ID of user. */
	public $rl_user_id;

	/**
	 * Flag to tell apart the initial list from the rest, for UX purposes and to forbid deleting it.
	 * Users with more than zero lists always have exactly one default list.
	 * @var string
	 */
	public $rl_is_default;

	/** @var string Human-readable non-unique name of the list. */
	public $rl_name;

	/** @var string Description of the list. */
	public $rl_description;

	/** @var string List color as 3x2 hex digits. */
	public $rl_color;

	/** @var string List image as file name to pass to wfFindFile() or the like. */
	public $rl_image;

	/** @var string List icon. */
	public $rl_icon;

	/** @var string Creation timestamp. */
	public $rl_date_created;

	/**
	 * Last modification timestamp.
	 * This includes modifications to the reading_list record, and modifications to sort order
	 * of the child entries, but not modifications/additions/deletions of child entries themselves.
	 * @var string
	 */
	public $rl_date_updated;

	/**
	 * Deleted flag.
	 * Lists will be hard-deleted eventually but kept around for a while for sync.
	 * @var string
	 */
	public $rl_deleted;

}
