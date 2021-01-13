<?php
/**
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * ICTCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by ICTCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by ICTCRM".
 */

namespace ICTCRM\Robo\Plugin\Commands;

use BeanFactory;
use Robo\Task\Base\loadTasks;
use ICTCRM\Robo\Traits\CliRunnerTrait;
use ICTCRM\Robo\Traits\RoboTrait;
use ICTCRM\Search\ElasticSearch\ElasticSearchIndexer;
use ICTCRM\Search\Index\Documentify\JsonSerializerDocumentifier;
use ICTCRM\Search\Index\Documentify\SearchDefsDocumentifier;
use ICTCRM\Search\SearchQuery;
use ICTCRM\Search\SearchResults;
use ICTCRM\Search\SearchWrapper;
use ICTCRM\Utility\BeanJsonSerializer;

/**
 * Class ElasticSearchCommands
 *
 * @package ICTCRM\Robo\Plugin\Commands
 */
class ElasticSearchCommands extends \Robo\Tasks
{
    use loadTasks;
    use RoboTrait;
    use CliRunnerTrait;

    /**
     * Performs a search using the given parameters.
     *
     * The full Elasticsearch query string syntax is available. See link below.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.5/query-dsl-query-string-query.html#query-string-syntax
     *
     * @param string $query    The search query. See elasticsearch syntax.
     * @param int    $size     How many results to show
     * @param bool   $showJson if set to `1` shows a JSON for each results.
     */
    public function elasticSearch($query, $size = 20, $showJson = false)
    {
        $this->bootstrap();

        $engine = new SearchWrapper();

        $result = $engine->search('ElasticSearchEngine', SearchQuery::fromString($query, $size));
        $hits = $result->getHits();

        if (empty($hits)) {
            $this->io()->note('No results matching your query. Try broadening your criteria.');
            $this->showSearchTime($result);
            return;
        }

        $this->io()->title("Results");
        foreach ($hits as $key => $module) {
            $this->printModuleResults($showJson, $key, $module);
        }

        $this->showSearchTime($result);
    }

    /**
     * Indexes the sql database in the Elasticsearch engine.
     *
     * Differential indexing will only update (and fetch) beans that have been created/modified/removed since the last
     * run. This should be much faster than a full index.
     *
     * The two documentifiers differ slightly on the type of structure they output.
     * See the relative classes to know more.
     *
     * NOTE: from CLI passing `true` or `false` won't work. Use `0` and `1`, instead as parameters.
     *
     * @param int $differential 0 = full index | 1 = differential index
     * @param int $searchdefs   0 = BeanJsonSerializer | 1 = SearchDefsDocumentifier
     *
     * @see ElasticSearchIndexer::index()
     * @see SearchDefsDocumentifier
     * @see JsonSerializerDocumentifier
     */
    public function elasticIndex($differential = 1, $searchdefs = 0)
    {
        $this->bootstrap();

        ElasticSearchIndexer::repairElasticsearchIndex($differential, $searchdefs);
    }

    /**
     * Deletes the Elasticsearch index.
     */
    public function elasticRmIndex()
    {
        $this->bootstrap();

        $indexer = new ElasticSearchIndexer();
        $indexer->removeIndex();
    }

    /**
     * Print the results for each module.
     *
     * @param bool   $showJson
     * @param string $module
     * @param array  $ids
     */
    private function printModuleResults($showJson, $module, array $ids)
    {
        $this->io()->section($module);

        $results = [];

        $json = BeanJsonSerializer::make();

        foreach ($ids as $id) {
            $bean = BeanFactory::getBean($module, $id);

            $results[] = $showJson
                ? $json->serialize($bean, true, true)
                : mb_convert_encoding($bean->name, 'UTF-8', 'HTML-ENTITIES');
        }

        $this->io()->listing($results);
    }

    /**
     * Pretty prints the search time.
     *
     * @param SearchResults $result
     */
    private function showSearchTime(SearchResults $result)
    {
        $time = round($result->getSearchTime() * 1000);
        $this->io()->success("Search performed in $time ms");
    }
}
