<?php

    /**
     * Represents an info update on an account
     * from the txttools system
     *
     * @package datawrappers
     * @author Greg J Preece, <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010062812
     * @since 2010062512
     */
    class moodletxt_ParseAccountDetails {

        /**
         * DB Record ID for the account
         * @var int
         */
        var $id;

        /**
         * Number of credits used on account
         * @var int
         */
        var $creditsused;

        /**
         * Number of credits remaining on account
         * @var int
         */
        var $creditsremaining;

        /**
         * Unix timestamp representing time of update
         * @var int
         */
        var $updatetime;

        /**
         * Constructor - initialises the bean
         * @param int $accountID DB record ID of txttools account
         * @param int $creditsused Number of credits used
         * @param int $creditsremaining Number of credits remaining
         * @version 2010062812
         * @since 2010062512
         */
        function moodletxt_ParseAccountDetails($accountID, $creditsused = 0, $creditsremaining = 0) {

            $this->set_id($accountID);
            $this->set_creditsused($creditsused);
            $this->set_creditsremaining($creditsremaining);
            $this->set_updatetime(time());

        }

        /**
         * Returns the DB record ID for this account
         * @return int DB record ID
         * @version 2010062512
         * @since 2010062512
         */
        function get_id() {
            return $this->id;
        }

        /**
         * Set the DB record ID for this account
         * @param int $id DB record ID
         * @version 2010062512
         * @since 2010062512
         */
        function set_id($id) {
            $this->id = $id;
        }

        /**
         * Returns the number of message credits
         * used via this account
         * @return int Number of credits used
         * @version 2010062512
         * @since 2010062512
         */
        function get_creditsused() {
            return $this->creditsused;
        }

        /**
         * Sets the number of message credits
         * used via this account
         * @param int $creditsused  Number of credits used
         * @version 2010062512
         * @since 2010062512
         */
        function set_creditsused($creditsused) {
            $this->creditsused = $creditsused;
        }

        /**
         * Returns the number of message credits
         * remaining on this account
         * @return int Number of credits remaining
         * @version 2010062512
         * @since 2010062512
         */
        function get_creditsremaining() {
            return $this->creditsremaining;
        }

        /**
         * Sets the number of message credits
         * remaining on this account
         * @param int $creditsremaining Number of credits remaining
         * @version 2010062512
         * @since 2010062512
         */
        function set_creditsremaining($creditsremaining) {
            $this->creditsremaining = $creditsremaining;
        }

        /**
         * Returns the UTC timestamp at which
         * this update was performed
         * @return int Unix timestamp
         * @version 2010062812
         * @since 2010062812
         */
        function get_updatetime() {
            return $this->updatetime;
        }

        /**
         * Sets the UTC timestamp at which
         * this update was performed
         * @param int $updatetime Unix timestamp
         * @version 2010062812
         * @since 2010062812
         */
        function set_updatetime($updatetime) {
            $this->updatetime = $updatetime;
        }

    }

?>
