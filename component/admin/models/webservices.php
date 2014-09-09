<?php
/**
 * @package     Redcore.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Webservices Model
 *
 * @package     Redcore.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedcoreModelWebservices extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_webservices';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'webservices_limit';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'published', 'w.published'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery

	protected function getListQuery()
	{
		$table = RedcoreHelpersTranslation::getTranslationTable();
		$db	= $this->getDbo();
		$query = $db->getQuery(true);

		if (empty($table))
		{
			$query->select('*')
				->from('#__extensions')
				->where('1=2');

			return $query;
		}

		$query->select('o.*')
			->from($db->qn($table->table, 'o'));

		$columns = (array) $table->columns;

		foreach ($columns as $column)
		{
			$query->select($db->qn('t.' . $column, 't_' . $column));
		}

		$query->select(
			array(
				$db->qn('t.rctranslations_id'),
				$db->qn('t.rctranslations_language'),
				$db->qn('t.rctranslations_originals'),
				$db->qn('t.rctranslations_modified'),
				$db->qn('t.rctranslations_state')
			)
		);

		$leftJoinOn = array();

		foreach ($table->primaryKeys as $primaryKey)
		{
			$leftJoinOn[] = 'o.' . $primaryKey . ' = t.' . $primaryKey;
		}

		if ($language = $this->getState('filter.language'))
		{
			$leftJoinOn[] = 't.rctranslations_language = ' . $db->q($language);
		}
		else
		{
			// We will return empty query
			$leftJoinOn[] = '1 = 2';
		}

		$leftJoinOn = implode(' AND ', $leftJoinOn);

		$query->leftJoin(
			$db->qn(RTranslationTable::getTranslationsTableName($table->table, ''), 't') . (!empty($leftJoinOn) ? ' ON ' . $leftJoinOn . '' : '')
		);

		// Filter search
		$search = $this->getState('filter.search_translations');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$searchColumns = array();

			foreach ($columns as $column)
			{
				$searchColumns[] = '(o.' . $column . ' LIKE ' . $search . ')';
				$searchColumns[] = '(t.' . $column . ' LIKE ' . $search . ')';
			}

			if (!empty($searchColumns))
			{
				$query->where('(' . implode(' OR ', $searchColumns) . ')');
			}
		}

		// Content Element filter
		$contentElement = RTranslationHelper::getContentElement($table->option, $table->xml);
		$filters = $contentElement->getTranslateFilter();

		if (!empty($filters))
		{
			foreach ($filters as $filter)
			{
				$query->where((string) $filter);
			}
		}

		// Ordering
		$orderList = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order = !empty($orderList) ? $orderList : 't.rctranslations_language';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}*/

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$items = RApiHalHelper::getWebservices();

		return $items;
	}

	/**
	 * Loading of related XML files
	 *
	 * @param   string  $extensionName    Extension name
	 * @param   array   $contentElements  Content elements
	 *
	 * @return  array  List of objects
	 */
	public function loadMissingContentElements($extensionName, $contentElements = array())
	{
		$translationTables = RTranslationHelper::getInstalledTranslationTables();
		$missingTables = array();

		foreach ($translationTables as $translationTableKey => $translationTable)
		{
			$translationTable->table = str_replace('#__', '', $translationTable->table);

			if ($translationTable->option == $extensionName)
			{
				$foundTable = false;

				foreach ($contentElements as $contentElement)
				{
					if (!empty($contentElement->table) && $contentElement->table == $translationTable->table)
					{
						$foundTable = true;
						break;
					}
				}

				if (!$foundTable)
				{
					$missingTables[$translationTableKey] = $translationTable;
				}
			}
		}

		return $missingTables;
	}
}
