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

namespace ICTCRM\Search\UI;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use ICTCRM\Search\Exceptions\SearchEngineNotFoundException;
use ICTCRM\Search\Exceptions\SearchException;
use ICTCRM\Search\Exceptions\SearchInvalidRequestException;
use ICTCRM\Search\Exceptions\SearchUserFriendlyException;
use ICTCRM\Search\SearchQuery;
use ICTCRM\Search\SearchWrapper;
use ICTCRM\Utility\SuiteLogger;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class SearchThrowableHandler handles an Exception or Error thrown during the search process and displays an error
 * screen.
 *
 * If developer mode is enabled, further details will be provided.
 */
class SearchThrowableHandler
{
    /** @var Throwable The Exception or Error that has occurred */
    private $throwable;
    /** @var SearchQuery the current search query */
    private $query;

    /**
     * SearchThrowableHandler constructor.
     *
     * @param Throwable   $throwable
     * @param SearchQuery $query
     */
    public function __construct($throwable, SearchQuery $query)
    {
        $this->throwable = $throwable;
        $this->query = $query;
    }

    /**
     * Shows the proper error message.
     *
     * If developer mode is enabled, a full exception page will be shown.
     */
    public function handle()
    {
        global $sugar_config;

        $logger = new SuiteLogger();

        $prefix = '[SearchThrowableHandler] ';
        $logger->error($prefix . $this->getFriendlyMessage());
        $logger->error($prefix . $this->throwable);

        if ($sugar_config['developerMode'] === true) {
            $this->printStackTrace();
            return;
        }

        $this->printFriendlyMessage();
    }

    /**
     * Returns an error message that is user friendly.
     *
     * @return string
     */
    private function getFriendlyMessage()
    {
        global $mod_strings;

        switch (get_class($this->throwable)) {
            case SearchUserFriendlyException::class:
                return $this->throwable->getMessage();
            case SearchInvalidRequestException::class:
                return $mod_strings['LBL_ELASTIC_SEARCH_SEARCH_INVALID_REQUEST'];
            case SearchEngineNotFoundException::class:
                return $mod_strings['LBL_ELASTIC_SEARCH_SEARCH_ENGINE_NOT_FOUND'];
            case NoNodesAvailableException::class:
                return $mod_strings['LBL_ELASTIC_SEARCH_NO_NODES_AVAILABLE'];
            case SearchException::class:
                return $mod_strings['LBL_ELASTIC_SEARCH_SEARCH'];
            default:
                return $mod_strings['LBL_ELASTIC_SEARCH_DEFAULT'];
        }
    }

    /**
     * Cancels the current output and prints a full screen detailed exception page
     */
    private function printStackTrace()
    {
        $whoops = new Run;
        $handler = new PrettyPageHandler;

        $handler->addDataTable('SearchQuery', $this->query->jsonSerialize());
        $handler->addDataTable('SearchWrapper Status', $this->getSearchWrapperStatus());

        $whoops->pushHandler($handler);
        $whoops->register();

        $whoops->handleException($this->throwable);
    }

    /**
     * Returns an array with the SearchWrapper status to be displayed in the detailed view.
     *
     * @return array
     */
    private function getSearchWrapperStatus()
    {
        try {
            return [
                'Available Engines' => implode(', ', SearchWrapper::getEngines()),
                'Search Controller' => SearchWrapper::getController(),
                'Default Search Engine' => SearchWrapper::getDefaultEngine(),
                'Friendly Error Message' => $this->getFriendlyMessage(),
            ];
        } catch (\Exception $exception) {
            return ['error' => 'failed to get SearchWrapper status'];
        }
    }

    /**
     * Prints the error on the page.
     */
    private function printFriendlyMessage()
    {
        global $mod_strings;

        $message = $this->getFriendlyMessage();
        $endMessage = $mod_strings['LBL_ELASTIC_SEARCH_EXCEPTION_END_MESSAGE'];

        echo '<h1>Error</h1>';
        echo "<p class='text-danger'>$message $endMessage</p>";
    }
}
